<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NotificationTemplate;
use App\Models\NotificationTemplateTranslation;

class CreateNotificationTemplates extends Command
{
    protected $signature = 'notification-templates:create';
    protected $description = 'Create system notification templates in the database';

    public function handle(): int
    {
        $templates = [
            [
                'code' => 'registration',
                'name' => 'Регистрация (Приветствие)',
                'translations' => [
                    'ru' => [
                        'title' => 'Добро пожаловать в SubCloudy!',
                        'message' => 'Рады видеть вас! Теперь вам доступны лучшие ИИ-сервисы в одном месте. Если возникнут вопросы — пишите в поддержку.',
                    ],
                    'en' => [
                        'title' => 'Welcome to SubCloudy!',
                        'message' => 'We are glad to see you! Now you have access to the best AI services in one place. If you have any questions, feel free to contact support.',
                    ],
                    'uk' => [
                        'title' => 'Ласкаво просимо до SubCloudy!',
                        'message' => 'Раді бачити вас! Тепер вам доступні найкращі ШІ-сервіси в одному місці. Якщо виникнуть питання — пишіть у підтримку.',
                    ],
                    'zh' => [
                        'title' => '欢迎来到 SubCloudy！',
                        'message' => '很高兴见到你！现在您可以在一个地方访问最好的 AI 服务。如果您有任何问题，请随时联系支持人员。',
                    ],
                    'es' => [
                        'title' => '¡Bienvenido a SubCloudy!',
                        'message' => '¡Estamos encantados de verte! Ahora tienes acceso a los mejores servicios de IA en un solo lugar. Si tienes alguna pregunta, no dudes en contactar con el soporte.',
                    ],
                ],
            ],
            [
                'code' => 'purchase',
                'name' => 'Успешная покупка',
                'translations' => [
                    'ru' => [
                        'title' => 'Подписка активирована',
                        'message' => 'Вы успешно активировали подписку на :service. Следующий платеж: :date.',
                    ],
                    'en' => [
                        'title' => 'Subscription Activated',
                        'message' => 'You have successfully activated a subscription for :service. Next payment: :date.',
                    ],
                    'uk' => [
                        'title' => 'Передплату активовано',
                        'message' => 'Ви успішно активували передплату на :service. Наступний платіж: :date.',
                    ],
                    'zh' => [
                        'title' => '订阅已激活',
                        'message' => '您已成功激活 :service 的订阅。下次付款时间：:date。',
                    ],
                    'es' => [
                        'title' => 'Suscripción activada',
                        'message' => 'Has activado correctamente una suscripción para :service. Próximo pago: :date.',
                    ],
                ],
            ],
            [
                'code' => 'subscription_expiring',
                'name' => 'Истечение подписки',
                'translations' => [
                    'ru' => [
                        'title' => 'Срок подписки истекает',
                        'message' => 'Ваша подписка на :service истекает через 3 дня. Продлите её, чтобы не потерять доступ.',
                    ],
                    'en' => [
                        'title' => 'Subscription Expiring',
                        'message' => 'Your subscription for :service expires in 3 days. Renew it now to maintain access.',
                    ],
                    'uk' => [
                        'title' => 'Термін передплати закінчується',
                        'message' => 'Ваша передплата на :service закінчується через 3 дні. Продовжте її, щоб не втратити доступ.',
                    ],
                    'zh' => [
                        'title' => '订阅即将到期',
                        'message' => '您的 :service 订阅将在 3 天内到期。请立即续订以维持访问权限。',
                    ],
                    'es' => [
                        'title' => 'La suscripción caduca',
                        'message' => 'Tu suscripción para :service caduca en 3 días. Renuévala ahora para mantener el acceso.',
                    ],
                ],
            ],
            [
                'code' => 'support_reply',
                'name' => 'Ответ службы поддержки',
                'translations' => [
                    'ru' => [
                        'title' => 'Новый ответ от поддержки',
                        'message' => 'Специалист ответил на ваше обращение. Вы можете прочитать ответ в разделе поддержки.',
                    ],
                    'en' => [
                        'title' => 'New Support Reply',
                        'message' => 'A support specialist has responded to your request. You can read the reply in the support section.',
                    ],
                    'uk' => [
                        'title' => 'Нова відповідь від підтримки',
                        'message' => 'Фахівець відповів на ваше звернення. Ви можете прочитати відповідь у розділі підтримки.',
                    ],
                    'zh' => [
                        'title' => '新的支持回复',
                        'message' => '支持专家已回复您的请求。您可以在支持部分阅读回复。',
                    ],
                    'es' => [
                        'title' => 'Nueva respuesta de soporte',
                        'message' => 'Un especialista de soporte ha respondido a tu solicitud. Puedes leer la respuesta en la sección de soporte.',
                    ],
                ],
            ],
        ];

        foreach ($templates as $templateData) {
            $template = NotificationTemplate::updateOrCreate(
                ['code' => $templateData['code']],
                [
                    'name' => $templateData['name'],
                    'is_mass' => 0,
                ]
            );

            foreach ($templateData['translations'] as $locale => $translationData) {
                foreach ($translationData as $field => $value) {
                    NotificationTemplateTranslation::updateOrCreate(
                        [
                            'notification_template_id' => $template->id,
                            'locale' => $locale,
                            'code' => $field,
                        ],
                        ['value' => $value]
                    );
                }
            }
        }

        $this->info('Notification templates and translations created/updated successfully!');
        return 0;
    }
}

