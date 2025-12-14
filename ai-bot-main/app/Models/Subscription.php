<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'payment_method',
        'service_id',
        'is_trial',
        'is_auto_renew',
        'expiring_email_sent',
        'next_payment_at',
        'order_id',
        'payment_attempts',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_CANCELED = 'canceled';
    const STATUS_ENDED = 'ended';

    /**
     * Boot метод для автоматической очистки связей при изменении статуса подписки
     */
    protected static function boot()
    {
        parent::boot();

        // Очистка связей при изменении статуса на canceled или ended
        static::updating(function ($subscription) {
            // Если статус меняется на canceled или ended
            if ($subscription->isDirty('status')) {
                $oldStatus = $subscription->getOriginal('status');
                $newStatus = $subscription->status;
                
                // Если подписка была активной и стала неактивной
                if ($oldStatus === self::STATUS_ACTIVE && 
                    in_array($newStatus, [self::STATUS_CANCELED, self::STATUS_ENDED])) {
                    
                    // Удаляем пользователя из всех аккаунтов этого сервиса
                    $user = $subscription->user;
                    $serviceId = $subscription->service_id;
                    
                    if ($user && $serviceId) {
                        $user->serviceAccounts()
                            ->where('service_accounts.service_id', $serviceId)
                            ->detach();
                    }
                }
            }
        });

        // Очистка связей при удалении активной подписки
        static::deleting(function ($subscription) {
            // Если удаляется активная подписка, удаляем пользователя из аккаунтов
            if ($subscription->status === self::STATUS_ACTIVE) {
                $user = $subscription->user;
                $serviceId = $subscription->service_id;
                
                if ($user && $serviceId) {
                    $user->serviceAccounts()
                        ->where('service_accounts.service_id', $serviceId)
                        ->detach();
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'credit_card' => 'Credit Card',
            'crypto' => 'Crypto',
            default => '-',
        };
    }
}
