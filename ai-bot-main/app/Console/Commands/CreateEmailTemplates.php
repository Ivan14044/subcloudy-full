<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateTranslation;

class CreateEmailTemplates extends Command
{
    protected $signature = 'email-templates:create';
    protected $description = 'Create email templates in the database with full HTML structure';

    public function handle(): int
    {
        $templates = [
            [
                'code' => 'welcome',
                'name' => 'Приветственное письмо',
                'translations' => [
                    'ru' => [
                        'title' => 'Добро пожаловать в SubCloudy!',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">👋 Добро пожаловать!</div></td></tr></table><p>Рады видеть вас в SubCloudy — вашем надежном помощнике для доступа к мировым ИИ-сервисам.</p><p>Мы подготовили всё необходимое, чтобы ваш опыт работы с искусственным интеллектом был максимально комфортным и продуктивным.</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">С ЧЕГО НАЧАТЬ?</div><div class="list-container"><ul><li>Выберите интересующий вас сервис (ChatGPT, Midjourney и др.)</li><li>Активируйте подходящий тарифный план</li><li>Используйте ИИ без границ прямо сейчас!</li></ul></div></td></tr></table><p>Если у вас возникнут вопросы, наша база знаний и служба поддержки всегда к вашим услугам.</p>',
                    ],
                    'en' => [
                        'title' => 'Welcome to SubCloudy!',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">👋 Welcome!</div></td></tr></table><p>We are glad to see you at SubCloudy — your reliable assistant for access to global AI services.</p><p>We have prepared everything you need to make your experience with artificial intelligence as comfortable and productive as possible.</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">WHERE TO START?</div><div class="list-container"><ul><li>Choose the service you are interested in (ChatGPT, Midjourney, etc.)</li><li>Activate a suitable tariff plan</li><li>Use AI without borders right now!</li></ul></div></td></tr></table><p>If you have any questions, our knowledge base and support service are always at your service.</p>',
                    ],
                    'uk' => [
                        'title' => 'Ласкаво просимо до SubCloudy!',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">👋 Ласкаво просимо!</div></td></tr></table><p>Раді бачити вас у SubCloudy — вашому надійному помічнику для доступу до світових ШІ-сервісів.</p><p>Ми підготували все необхідне, щоб ваш досвід роботи з штучним інтелектом був максимально комфортним та продуктивним.</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">З ЧОГО ПОЧАТИ?</div><div class="list-container"><ul><li>Виберіть сервіс, який вас цікавить (ChatGPT, Midjourney та ін.)</li><li>Активуйте відповідний тарифний план</li><li>Використовуйте ШІ без кордонів прямо зараз!</li></ul></div></td></tr></table><p>Якщо у вас виникнуть питання, наша база знань та служба підтримки завжди до ваших послуг.</p>',
                    ],
                    'zh' => [
                        'title' => '欢迎来到 SubCloudy！',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">👋 欢迎！</div></td></tr></table><p>很高兴在 SubCloudy 见到您 — 您访问全球 AI 服务的可靠助手。</p><p>我们已经为您准备好了一切，让您在使用人工智能时的体验尽可能舒适且富有成效。</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">从哪里开始？</div><div class="list-container"><ul><li>选择您感兴趣的服务（ChatGPT、Midjourney 等）</li><li>激活合适的资费计划</li><li>现在就开始无国界使用 AI！</li></ul></div></td></tr></table><p>如果您有任何问题，我们的知识库和支持服务随时为您提供帮助。</p>',
                    ],
                    'es' => [
                        'title' => '¡Bienvenido a SubCloudy!',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">👋 ¡Bienvenido!</div></td></tr></table><p>Nos complace verte en SubCloudy, tu asistente confiable para el acceso a servicios globales de IA.</p><p>Hemos preparado todo lo necesario para que tu experiencia con la inteligencia artificial sea lo más cómoda y productiva posible.</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">¿POR DÓNDE EMPEZAR?</div><div class="list-container"><ul><li>Elige el servicio que te interesa (ChatGPT, Midjourney, etc.)</li><li>Activa un plan de tarifas adecuado</li><li>¡Usa la IA sin fronteras ahora mismo!</li></ul></div></td></tr></table><p>Si tienes alguna pregunta, nuestra base de conocimientos y servicio de soporte están siempre a tu disposición.</p>',
                    ],
                ],
            ],
            [
                'code' => 'subscription_activated',
                'name' => 'Активация подписки',
                'translations' => [
                    'ru' => [
                        'title' => 'Подписка активирована - {{service_name}}',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-success">✓ ПОДПИСКА АКТИВИРОВАНА</div></td></tr></table><p>Поздравляем! Ваша подписка на сервис успешно активирована. Теперь вы можете использовать все премиум-возможности.</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">ДЕТАЛИ ПОДПИСКИ</div><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr class="info-row"><td class="info-label" style="padding: 10px 0;">СЕРВИС</td><td class="info-value" style="padding: 10px 0;">{{service_name}}</td></tr><tr class="info-row"><td class="info-label" style="padding: 10px 0;">СТАТУС</td><td class="info-value" style="padding: 10px 0;">АКТИВНА</td></tr></table></td></tr></table><p>Если у вас возникнут вопросы, наша служба поддержки всегда на связи.</p>',
                    ],
                    'en' => [
                        'title' => 'Subscription Activated - {{service_name}}',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-success">✓ SUBSCRIPTION ACTIVATED</div></td></tr></table><p>Congratulations! Your subscription has been successfully activated. You can now use all premium features.</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">SUBSCRIPTION DETAILS</div><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr class="info-row"><td class="info-label" style="padding: 10px 0;">SERVICE</td><td class="info-value" style="padding: 10px 0;">{{service_name}}</td></tr><tr class="info-row"><td class="info-label" style="padding: 10px 0;">STATUS</td><td class="info-value" style="padding: 10px 0;">ACTIVE</td></tr></table></td></tr></table><p>If you have any questions, our support team is always available.</p>',
                    ],
                    'uk' => [
                        'title' => 'Передплату активовано - {{service_name}}',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-success">✓ ПЕРЕДПЛАТУ АКТИВОВАНО</div></td></tr></table><p>Вітаємо! Вашу передплату на сервіс успішно активовано. Тепер ви можете використовувати всі преміум-можливості.</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">ДЕТАЛІ ПЕРЕДПЛАТИ</div><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr class="info-row"><td class="info-label" style="padding: 10px 0;">СЕРВІС</td><td class="info-value" style="padding: 10px 0;">{{service_name}}</td></tr><tr class="info-row"><td class="info-label" style="padding: 10px 0;">СТАТУС</td><td class="info-value" style="padding: 10px 0;">АКТИВНА</td></tr></table></td></tr></table><p>Якщо у вас виникнуть питання, наша служба підтримки завжди на зв\'язку.</p>',
                    ],
                    'zh' => [
                        'title' => '订阅已激活 - {{service_name}}',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-success">✓ 订阅已激活</div></td></tr></table><p>恭喜！您的订阅已成功激活。您现在可以使用所有高级功能。</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">订阅详情</div><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr class="info-row"><td class="info-label" style="padding: 10px 0;">服务</td><td class="info-value" style="padding: 10px 0;">{{service_name}}</td></tr><tr class="info-row"><td class="info-label" style="padding: 10px 0;">状态</td><td class="info-value" style="padding: 10px 0;">已激活</td></tr></table></td></tr></table><p>如果您有任何问题，我们的支持团队随时为您服务。</p>',
                    ],
                    'es' => [
                        'title' => 'Suscripción activada - {{service_name}}',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-success">✓ SUSCRIPCIÓN ACTIVADA</div></td></tr></table><p>¡Felicidades! Tu suscripción se ha activado correctamente. Ahora puedes usar todas las funciones premium.</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">DETALLES DE LA SUSCRIPCIÓN</div><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr class="info-row"><td class="info-label" style="padding: 10px 0;">SERVICIO</td><td class="info-value" style="padding: 10px 0;">{{service_name}}</td></tr><tr class="info-row"><td class="info-label" style="padding: 10px 0;">ESTADO</td><td class="info-value" style="padding: 10px 0;">ACTIVA</td></tr></table></td></tr></table><p>Si tienes alguna pregunta, nuestro equipo de soporte está siempre disponible.</p>',
                    ],
                ],
            ],
            [
                'code' => 'payment_confirmation',
                'name' => 'Подтверждение оплаты',
                'translations' => [
                    'ru' => [
                        'title' => 'Оплата подтверждена',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">💰 ОПЛАТА ПОДТВЕРЖДЕНА</div></td></tr></table><p>Мы получили ваш платеж. Благодарим за доверие к SubCloudy!</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">ДЕТАЛИ ТРАНЗАКЦИИ</div><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr class="info-row"><td class="info-label" style="padding: 10px 0;">СУММА</td><td class="info-value" style="padding: 10px 0;">{{amount}}</td></tr><tr class="info-row"><td class="info-label" style="padding: 10px 0;">СТАТУС</td><td class="info-value" style="padding: 10px 0;">ОПЛАЧЕНО</td></tr></table></td></tr></table><p>Чек об оплате доступен в вашем личном кабинете.</p>',
                    ],
                    'en' => [
                        'title' => 'Payment Confirmed',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">💰 PAYMENT CONFIRMED</div></td></tr></table><p>We have received your payment. Thank you for choosing SubCloudy!</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">TRANSACTION DETAILS</div><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr class="info-row"><td class="info-label" style="padding: 10px 0;">AMOUNT</td><td class="info-value" style="padding: 10px 0;">{{amount}}</td></tr><tr class="info-row"><td class="info-label" style="padding: 10px 0;">STATUS</td><td class="info-value" style="padding: 10px 0;">PAID</td></tr></table></td></tr></table><p>The payment receipt is available in your account.</p>',
                    ],
                    'uk' => [
                        'title' => 'Оплату підтверджено',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">💰 ОПЛАТУ ПІДТВЕРДЖЕНО</div></td></tr></table><p>Ми отримали ваш платіж. Дякуємо за довіру до SubCloudy!</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">ДЕТАЛІ ТРАНЗАКЦІЇ</div><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr class="info-row"><td class="info-label" style="padding: 10px 0;">СУМА</td><td class="info-value" style="padding: 10px 0;">{{amount}}</td></tr><tr class="info-row"><td class="info-label" style="padding: 10px 0;">СТАТУС</td><td class="info-value" style="padding: 10px 0;">ОПЛАЧЕНО</td></tr></table></td></tr></table><p>Квитанція про оплату доступна у вашому особистому кабінеті.</p>',
                    ],
                    'zh' => [
                        'title' => '付款已确认',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">💰 付款已确认</div></td></tr></table><p>我们已收到您的付款。感谢您对 SubCloudy 的信任！</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">交易详情</div><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr class="info-row"><td class="info-label" style="padding: 10px 0;">金额</td><td class="info-value" style="padding: 10px 0;">{{amount}}</td></tr><tr class="info-row"><td class="info-label" style="padding: 10px 0;">状态</td><td class="info-value" style="padding: 10px 0;">已支付</td></tr></table></td></tr></table><p>付款收据可在您的个人帐户中找到。</p>',
                    ],
                    'es' => [
                        'title' => 'Pago confirmado',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">💰 PAGO CONFIRMADO</div></td></tr></table><p>Hemos recibido tu pago. ¡Gracias por confiar en SubCloudy!</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">DETALLES DE LA TRANSACCIÓN</div><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr class="info-row"><td class="info-label" style="padding: 10px 0;">MONTO</td><td class="info-value" style="padding: 10px 0;">{{amount}}</td></tr><tr class="info-row"><td class="info-label" style="padding: 10px 0;">ESTADO</td><td class="info-value" style="padding: 10px 0;">PAGADO</td></tr></table></td></tr></table><p>El recibo de pago está disponible en tu cuenta personal.</p>',
                    ],
                ],
            ],
            [
                'code' => 'subscription_expiring',
                'name' => 'Истечение подписки',
                'translations' => [
                    'ru' => [
                        'title' => 'Подписка скоро истечет - {{service_name}}',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-warning">⏳ СРОК ИСТЕКАЕТ</div></td></tr></table><p>Уведомляем вас, что срок действия вашей подписки скоро заканчивается. Продлите её сейчас, чтобы не потерять доступ к сервису.</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">ИНФОРМАЦИЯ</div><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr class="info-row"><td class="info-label" style="padding: 10px 0;">СЕРВИС</td><td class="info-value" style="padding: 10px 0;">{{service_name}}</td></tr><tr class="info-row"><td class="info-label" style="padding: 10px 0;">СТАТУС</td><td class="info-value" style="padding: 10px 0;">СКОРО ИСТЕКАЕТ</td></tr></table></td></tr></table><p>Для продления перейдите на сайт и выберите удобный тарифный план.</p>',
                    ],
                    'en' => [
                        'title' => 'Subscription Expiring Soon - {{service_name}}',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-warning">⏳ EXPIRING SOON</div></td></tr></table><p>We notify you that your subscription will expire soon. Renew it now to maintain access to the service.</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">INFORMATION</div><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr class="info-row"><td class="info-label" style="padding: 10px 0;">SERVICE</td><td class="info-value" style="padding: 10px 0;">{{service_name}}</td></tr><tr class="info-row"><td class="info-label" style="padding: 10px 0;">STATUS</td><td class="info-value" style="padding: 10px 0;">EXPIRING SOON</td></tr></table></td></tr></table><p>To renew, go to the website and choose a suitable tariff plan.</p>',
                    ],
                    'uk' => [
                        'title' => 'Передплата скоро закінчиться - {{service_name}}',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-warning">⏳ ТЕРМІН ЗАКІНЧУЄТЬСЯ</div></td></tr></table><p>Повідомляємо вас, що термін дії вашої передплати скоро закінчується. Продовжіть її зараз, щоб не втратити доступ до сервісу.</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">ІНФОРМАЦІЯ</div><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr class="info-row"><td class="info-label" style="padding: 10px 0;">СЕРВІС</td><td class="info-value" style="padding: 10px 0;">{{service_name}}</td></tr><tr class="info-row"><td class="info-label" style="padding: 10px 0;">СТАТУС</td><td class="info-value" style="padding: 10px 0;">СКОРО ЗАКІНЧИТЬСЯ</td></tr></table></td></tr></table><p>Для продовження перейдіть на сайт і виберіть зручний тарифний план.</p>',
                    ],
                    'zh' => [
                        'title' => '订阅即将到期 - {{service_name}}',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-warning">⏳ 即将到期</div></td></tr></table><p>我们通知您，您的订阅即将到期。请立即续订，以保持对服务的访问。</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">信息</div><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr class="info-row"><td class="info-label" style="padding: 10px 0;">服务</td><td class="info-value" style="padding: 10px 0;">{{service_name}}</td></tr><tr class="info-row"><td class="info-label" style="padding: 10px 0;">状态</td><td class="info-value" style="padding: 10px 0;">即将到期</td></tr></table></td></tr></table><p>要续订，请前往网站并选择合适的资费计划。</p>',
                    ],
                    'es' => [
                        'title' => 'La suscripción caducará pronto - {{service_name}}',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-warning">⏳ CADUCA PRONTO</div></td></tr></table><p>Te notificamos que tu suscripción caducará pronto. Renuévala ahora para mantener el acceso al servicio.</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">INFORMACIÓN</div><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr class="info-row"><td class="info-label" style="padding: 10px 0;">SERVICIO</td><td class="info-value" style="padding: 10px 0;">{{service_name}}</td></tr><tr class="info-row"><td class="info-label" style="padding: 10px 0;">ESTADO</td><td class="info-value" style="padding: 10px 0;">CADUCA PRONTO</td></tr></table></td></tr></table><p>Para renovar, ve al sitio web y elige un plan de tarifas adecuado.</p>',
                    ],
                ],
            ],
            [
                'code' => 'reset_password',
                'name' => 'Сброс пароля',
                'translations' => [
                    'ru' => [
                        'title' => 'Сброс пароля',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">🔐 СБРОС ПАРОЛЯ</div></td></tr></table><p>Вы запросили сброс пароля для вашего аккаунта SubCloudy.</p><p>Чтобы создать новый пароль, нажмите кнопку ниже:</p>{{button}}<p style="color: #999; font-size: 14px; margin-top: 20px;">Если вы не запрашивали сброс, просто проигнорируйте это письмо.</p>',
                    ],
                    'en' => [
                        'title' => 'Reset Password',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">🔐 RESET PASSWORD</div></td></tr></table><p>You have requested a password reset for your SubCloudy account.</p><p>To create a new password, click the button below:</p>{{button}}<p style="color: #999; font-size: 14px; margin-top: 20px;">If you did not request a reset, please ignore this email.</p>',
                    ],
                    'uk' => [
                        'title' => 'Скидання пароля',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">🔐 СКИДАННЯ ПАРОЛЯ</div></td></tr></table><p>Ви запросили скидання пароля для вашого аккаунта SubCloudy.</p><p>Щоб створити новий пароль, натисніть кнопку нижче:</p>{{button}}<p style="color: #999; font-size: 14px; margin-top: 20px;">Якщо ви не запитували скидання, просто ігноруйте цей лист.</p>',
                    ],
                    'zh' => [
                        'title' => '重置密码',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">🔐 重置密码</div></td></tr></table><p>您已请求重置 SubCloudy 帐户的密码。</p><p>要创建新密码，请点击下面的按钮：</p>{{button}}<p style="color: #999; font-size: 14px; margin-top: 20px;">如果您没有请求重置，请忽略此邮件。</p>',
                    ],
                    'es' => [
                        'title' => 'Restablecer contraseña',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">🔐 RESTABLECER CONTRASEÑA</div></td></tr></table><p>Has solicitado un restablecimiento de contraseña para tu cuenta de SubCloudy.</p><p>Para crear una nueva contraseña, haz clic en el botón de abajo:</p>{{button}}<p style="color: #999; font-size: 14px; margin-top: 20px;">Si no solicitaste un restablecimiento, ignora este correo electrónico.</p>',
                    ],
                ],
            ],
            [
                'code' => 'support_reply',
                'name' => 'Ответ поддержки',
                'translations' => [
                    'ru' => [
                        'title' => 'Новый ответ от службы поддержки',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">💬 ОТВЕТ ПОДДЕРЖКИ</div></td></tr></table><p>Специалист службы поддержки ответил на ваше обращение.</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">ТЕКСТ СООБЩЕНИЯ</div><p style="text-align: left; font-style: italic; color: #1e293b; margin: 0;">{{message_text}}</p></td></tr></table><p>Вы можете прочитать полный текст переписки и ответить на сайте.</p>',
                    ],
                    'en' => [
                        'title' => 'New Support Reply',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">💬 SUPPORT REPLY</div></td></tr></table><p>A support specialist has responded to your request.</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">MESSAGE TEXT</div><p style="text-align: left; font-style: italic; color: #1e293b; margin: 0;">{{message_text}}</p></td></tr></table><p>You can read the full text of the correspondence and reply on the website.</p>',
                    ],
                    'uk' => [
                        'title' => 'Нова відповідь від служби підтримки',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">💬 ВІДПОВІДЬ ПІДТРИМКИ</div></td></tr></table><p>Фахівець служби підтримки відповів на ваше звернення.</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">ТЕКСТ ПОВІДОМЛЕННЯ</div><p style="text-align: left; font-style: italic; color: #1e293b; margin: 0;">{{message_text}}</p></td></tr></table><p>Ви можете прочитати повний текст листування та відповісти на сайті.</p>',
                    ],
                    'zh' => [
                        'title' => '新的支持回复',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">💬 支持回复</div></td></tr></table><p>支持专家已回复您的请求。</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">邮件正文</div><p style="text-align: left; font-style: italic; color: #1e293b; margin: 0;">{{message_text}}</p></td></tr></table><p>您可以阅读信件的全文并在网站上回复。</p>',
                    ],
                    'es' => [
                        'title' => 'Nueva respuesta de soporte',
                        'message' => '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding-bottom: 20px;"><div class="badge badge-info">💬 RESPUESTA DE SOPORTE</div></td></tr></table><p>Un especialista de soporte ha respondido a tu solicitud.</p><table border="0" cellpadding="0" cellspacing="0" class="info-card"><tr><td class="info-body"><div class="info-title">TEXTO DEL MENSAJE</div><p style="text-align: left; font-style: italic; color: #1e293b; margin: 0;">{{message_text}}</p></td></tr></table><p>Puedes leer el texto completo de la correspondencia y responder en el sitio web.</p>',
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
                        ['value' => $value]
                    );
                }
            }
        }

        $this->info('Email templates and translations created/updated successfully!');
        return 0;
    }
}
