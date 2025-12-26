<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateTranslation;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'code' => 'subscription_activated',
                'name' => 'Активация подписки',
                'translations' => [
                    'ru' => [
                        'title' => 'Подписка активирована - {{service_name}}',
                        'message' => '<div class="badge badge-success">✓ Подписка активирована</div>

<p>Поздравляем! Ваша подписка на <strong>{{service_name}}</strong> успешно активирована.</p>

<div class="info-box">
    <h3>Детали подписки</h3>
    <div class="info-row">
        <span class="info-label">Сервис:</span>
        <span class="info-value">{{service_name}}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Статус:</span>
        <span class="info-value">Активна</span>
    </div>
</div>

<p>Теперь вы можете использовать все возможности сервиса. Если у вас возникнут вопросы, наша служба поддержки всегда готова помочь.</p>

<p>Спасибо за выбор SubCloudy!</p>',
                    ],
                    'en' => [
                        'title' => 'Subscription Activated - {{service_name}}',
                        'message' => '<div class="badge badge-success">✓ Subscription Activated</div>

<p>Congratulations! Your subscription to <strong>{{service_name}}</strong> has been successfully activated.</p>

<div class="info-box">
    <h3>Subscription Details</h3>
    <div class="info-row">
        <span class="info-label">Service:</span>
        <span class="info-value">{{service_name}}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Status:</span>
        <span class="info-value">Active</span>
    </div>
</div>

<p>You can now use all the features of the service. If you have any questions, our support team is always ready to help.</p>

<p>Thank you for choosing SubCloudy!</p>',
                    ],
                ],
            ],
            [
                'code' => 'payment_confirmation',
                'name' => 'Подтверждение оплаты',
                'translations' => [
                    'ru' => [
                        'title' => 'Оплата подтверждена',
                        'message' => '<div class="badge badge-success">✓ Оплата подтверждена</div>

<p>Спасибо за ваш платеж! Мы успешно получили оплату за вашу подписку.</p>

<div class="info-box">
    <h3>Детали платежа</h3>
    <div class="info-row">
        <span class="info-label">Сумма:</span>
        <span class="info-value">{{amount}}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Статус:</span>
        <span class="info-value">Оплачено</span>
    </div>
</div>

<p>Ваша подписка продлена и активна. Вы можете продолжать пользоваться всеми услугами SubCloudy.</p>

<p>Если у вас есть вопросы по платежу, пожалуйста, свяжитесь с нашей службой поддержки.</p>',
                    ],
                    'en' => [
                        'title' => 'Payment Confirmed',
                        'message' => '<div class="badge badge-success">✓ Payment Confirmed</div>

<p>Thank you for your payment! We have successfully received payment for your subscription.</p>

<div class="info-box">
    <h3>Payment Details</h3>
    <div class="info-row">
        <span class="info-label">Amount:</span>
        <span class="info-value">{{amount}}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Status:</span>
        <span class="info-value">Paid</span>
    </div>
</div>

<p>Your subscription has been renewed and is active. You can continue to use all SubCloudy services.</p>

<p>If you have any questions about the payment, please contact our support team.</p>',
                    ],
                ],
            ],
            [
                'code' => 'subscription_expiring',
                'name' => 'Истечение подписки',
                'translations' => [
                    'ru' => [
                        'title' => 'Подписка скоро истечет - {{service_name}}',
                        'message' => '<div class="badge badge-warning">⚠ Подписка скоро истечет</div>

<p>Уважаемый пользователь!</p>

<p>Ваша подписка на <strong>{{service_name}}</strong> истечет через 3 дня.</p>

<div class="info-box">
    <h3>Информация о подписке</h3>
    <div class="info-row">
        <span class="info-label">Сервис:</span>
        <span class="info-value">{{service_name}}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Дата истечения:</span>
        <span class="info-value">Через 3 дня</span>
    </div>
</div>

<p>Чтобы продолжить пользоваться услугами, пожалуйста, продлите подписку до истечения срока действия.</p>

<p>Если у вас включено автоматическое продление, подписка будет продлена автоматически при наличии средств на вашем счете.</p>

<p>Спасибо за использование SubCloudy!</p>',
                    ],
                    'en' => [
                        'title' => 'Subscription Expiring Soon - {{service_name}}',
                        'message' => '<div class="badge badge-warning">⚠ Subscription Expiring Soon</div>

<p>Dear user!</p>

<p>Your subscription to <strong>{{service_name}}</strong> will expire in 3 days.</p>

<div class="info-box">
    <h3>Subscription Information</h3>
    <div class="info-row">
        <span class="info-label">Service:</span>
        <span class="info-value">{{service_name}}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Expiration Date:</span>
        <span class="info-value">In 3 days</span>
    </div>
</div>

<p>To continue using the services, please renew your subscription before it expires.</p>

<p>If you have automatic renewal enabled, your subscription will be automatically renewed if you have sufficient funds in your account.</p>

<p>Thank you for using SubCloudy!</p>',
                    ],
                ],
            ],
            [
                'code' => 'reset_password',
                'name' => 'Сброс пароля',
                'translations' => [
                    'ru' => [
                        'title' => 'Сброс пароля для вашего аккаунта',
                        'message' => '<div class="badge badge-info">🔐 Сброс пароля</div>

<p>Вы запросили сброс пароля для вашего аккаунта SubCloudy.</p>

<p>Для создания нового пароля нажмите на кнопку ниже:</p>

{{button}}

<p style="color: #999; font-size: 14px; margin-top: 20px;">
    Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо. Ваш пароль останется без изменений.
</p>

<p style="color: #999; font-size: 12px; margin-top: 15px;">
    Ссылка действительна в течение 60 минут.
</p>',
                    ],
                    'en' => [
                        'title' => 'Reset Password for Your Account',
                        'message' => '<div class="badge badge-info">🔐 Reset Password</div>

<p>You have requested a password reset for your SubCloudy account.</p>

<p>To create a new password, click the button below:</p>

{{button}}

<p style="color: #999; font-size: 14px; margin-top: 20px;">
    If you did not request a password reset, please ignore this email. Your password will remain unchanged.
</p>

<p style="color: #999; font-size: 12px; margin-top: 15px;">
    The link is valid for 60 minutes.
</p>',
                    ],
                ],
            ],
        ];

        foreach ($templates as $templateData) {
            $template = EmailTemplate::updateOrCreate(
                ['code' => $templateData['code']],
                ['name' => $templateData['name']]
            );

            foreach ($templateData['translations'] as $locale => $translationData) {
                foreach ($translationData as $field => $value) {
                    EmailTemplateTranslation::updateOrCreate(
                        [
                            'email_template_id' => $template->id,
                            'locale' => $locale,
                            'code' => $field,
                        ],
                        [
                            'value' => $value,
                        ]
                    );
                }
            }
        }

        $this->command->info('Email templates seeded successfully!');
    }
}

