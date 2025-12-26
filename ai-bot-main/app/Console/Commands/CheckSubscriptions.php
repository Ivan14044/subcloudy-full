<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\Option;
use App\Services\EmailService;
use App\Services\NotificationTemplateService;
use Carbon\Carbon;
use App\Services\MonoPaymentService;

class CheckSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-payments';
    protected $description = 'Check subscriptions and process next payments if due';

    /**
     * Обрабатывает все активные подписки, у которых наступила дата следующего платежа.
     */
    public function handle(): int
    {
        $now = Carbon::now();

        $subscriptions = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->where('is_auto_renew', 1)
            ->where('next_payment_at', '<=', $now)
            ->get();

        foreach ($subscriptions as $subscription) {
            match ($subscription->payment_method) {
                'credit_card' => $this->processCreditCardPayment($subscription),
                default => $this->endSubscription($subscription),
            };
        }

        $this->info('Subscription check complete.');
        return Command::SUCCESS;
    }

    /**
     * Завершает подписку: меняет статус на ended и сохраняет.
     * Изменение статуса автоматически удалит пользователя из аккаунтов сервиса
     * через событие updating в модели Subscription.
     *
     * @param Subscription $subscription
     */
    private function endSubscription(Subscription $subscription): void
    {
        $subscription->status = Subscription::STATUS_ENDED;
        $subscription->save();

        $this->info("Subscription #{$subscription->id} ended (method: {$subscription->payment_method}).");
    }

    /**
     * Обрабатывает продление подписки через сохранённую карту.
     *
     * @param Subscription $subscription
     */
    private function processCreditCardPayment(Subscription $subscription): void
    {
        $this->info("Processing credit card payment for subscription #{$subscription->id}.");

        // Получаем список сохранённых карт
        $walletCards = MonoPaymentService::getWalletCard('wallet_user_' . $subscription->user_id);
        
        if (empty($walletCards)) {
            $this->error("No saved card found for subscription #{$subscription->id}. Subscription will be ended.");
            $this->endSubscription($subscription);
            return;
        }

        // Используем первую доступную карту
        $card = $walletCards[0];
        $cardToken = $card['cardToken'] ?? null;

        if (!$cardToken) {
            $this->error("No card token found for subscription #{$subscription->id}. Subscription will be ended.");
            $this->endSubscription($subscription);
            return;
        }

        // Пытаемся списать средства с карты
        $paymentSuccess = $this->attemptCharge($subscription, $cardToken);

        if (!$paymentSuccess) {
            // увеличиваем количество попыток
            $subscription->payment_attempts = ($subscription->payment_attempts ?? 0) + 1;

            if ($subscription->payment_attempts >= 6) {
                $this->error("Final (6th) payment attempt failed for subscription #{$subscription->id}.");
                $this->endSubscription($subscription);
            } else {
                $subscription->save();
                $this->warn("Attempt #{$subscription->payment_attempts} failed for subscription #{$subscription->id}. Will retry later.");
            }

            return;
        }

        // Успешная оплата — сбрасываем счётчик и продлеваем подписку
        $subscription->payment_attempts = 0;
        $subscription->expiring_email_sent = 0;
        $subscription->next_payment_at = Carbon::now()->addMonth();
        $subscription->save();

        // Отправка уведомлений об успешном продлении
        $user = $subscription->user;
        $service = $subscription->service;
        $currency = strtoupper(Option::get('currency', 'USD'));
        $amount = $subscription->is_trial ? ($service->trial_amount ?? 0) : $service->amount;

        EmailService::send('payment_confirmation', $subscription->user_id, [
            'amount' => number_format($amount, 2, '.', '') . ' ' . $currency,
        ]);

        $serviceName = $service ? ($service->getTranslation('name', $user->lang ?? 'en') ?? $service->name) : 'Service';
        EmailService::send('subscription_activated', $subscription->user_id, [
            'service_name' => $serviceName,
        ]);

        app(NotificationTemplateService::class)->sendToUser($user, 'purchase', [
            'service' => $service->code ?? 'Service',
            'date' => $subscription->next_payment_at ? $subscription->next_payment_at->format('d.m.Y') : '-',
        ]);

        $this->info("Subscription #{$subscription->id} successfully renewed. Next payment at {$subscription->next_payment_at->toDateTimeString()}.");
    }

    /**
     * Пытается списать средства с карты пользователя.
     *
     * @param Subscription $subscription
     * @param string $cardToken
     * @return bool
     */
    private function attemptCharge(Subscription $subscription, string $cardToken): bool
    {
        $service = $subscription->service;
        if (!$service) {
            $this->error("Service not found for subscription #{$subscription->id}.");
            return false;
        }

        $amount = $subscription->is_trial ? ($service->trial_amount ?? 0) : $service->amount;

        if ($amount <= 0) {
            $this->warn("Amount is 0 for subscription #{$subscription->id}. Skipping payment.");
            return true; // Считаем успешным, если сумма 0
        }

        $this->info("Attempting to charge {$amount} for subscription #{$subscription->id}.");

        // Добавляем webhook для получения уведомлений о статусе платежа
        $webhookUrl = config('app.url') . '/api/mono/webhook?subscription_id=' . $subscription->id . '&user_id=' . $subscription->user_id;
        
        $result = MonoPaymentService::chargeWithToken($cardToken, $amount, null, $webhookUrl);

        if ($result === false) {
            $this->error("Payment failed for subscription #{$subscription->id}.");
            return false;
        }

        // Проверяем статус платежа
        $status = isset($result['status']) ? $result['status'] : null;
        if ($status === 'success') {
            $invoiceId = isset($result['invoiceId']) ? $result['invoiceId'] : 'N/A';
            $this->info("Payment successful for subscription #{$subscription->id}. Invoice ID: {$invoiceId}");
            
            // Создаём транзакцию
            \App\Models\Transaction::create([
                'user_id' => $subscription->user_id,
                'amount' => $amount,
                'currency' => strtoupper(\App\Models\Option::get('currency', 'USD')),
                'payment_method' => 'credit_card',
                'subscription_id' => $subscription->id,
                'status' => \App\Models\Transaction::STATUS_COMPLETED,
            ]);

            return true;
        }

        $failureReason = $result['failureReason'] ?? 'Unknown error';
        $this->error("Payment failed for subscription #{$subscription->id}. Reason: {$failureReason}");
        return false;
    }
}
