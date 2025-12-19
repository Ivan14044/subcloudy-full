<?php

namespace App\Console\Commands;

use App\Models\Content;
use Illuminate\Console\Command;

class AddFaqContent extends Command
{
    protected $signature = 'content:add-faq';
    protected $description = 'Добавить начальные данные FAQ';

    public function handle()
    {
        $faqContent = Content::firstOrCreate(
            ['code' => 'faq'],
            ['name' => 'FAQ (Часто задаваемые вопросы)']
        );

        $faqData = $this->getFaqData();

        // Сохраняем переводы для каждого языка
        $translationData = [
            'value' => $faqData,
        ];

        $faqContent->saveTranslation($translationData);

        $this->info('FAQ контент успешно создан/обновлен!');
        return 0;
    }

    private function getFaqData(): array
    {
        return [
            'ru' => json_encode([
                [
                    'question' => 'Будут ли другие пользователи видеть мои чаты в ChatGPT?',
                    'answer' => 'Да, так как это общий аккаунт, история чатов может быть видна другим пользователям. Мы рекомендуем не вводить в чаты конфиденциальные данные (пароли, личные документы, финансовую информацию). Вы также можете использовать функцию архивации или удаления чатов после завершения работы.'
                ],
                [
                    'question' => 'Что делать, если пароль от аккаунта перестанет подходить?',
                    'answer' => 'Такое случается крайне редко, но если это произошло — сразу пишите нам в техподдержку. Мы оперативно выдадим вам новый пароль или заменим аккаунт, чтобы вы могли продолжить работу без длительных пауз.'
                ],
                [
                    'question' => 'Нужно ли использовать VPN?',
                    'answer' => 'Это зависит от сервиса и вашего местоположения. Например, для ChatGPT или Midjourney может потребоваться VPN, если эти сервисы официально недоступны в вашей стране. Сам наш сайт работает без VPN.'
                ],
                [
                    'question' => 'Это официальная подписка или взломанный аккаунт?',
                    'answer' => 'Мы предоставляем доступ к официальным, легально оплаченным подпискам. Экономия достигается за счет шеринга (совместного использования) одного аккаунта группой пользователей. Это безопасно и стабильно.'
                ],
                [
                    'question' => 'Можно ли продлить подписку на тот же аккаунт или каждый месяц выдается новый?',
                    'answer' => 'Обычно при продлении подписки мы выдаем доступ к новому аккаунту для обеспечения безопасности и стабильности работы сервиса. Вся история ваших диалогов остается на старом аккаунте, поэтому рекомендуем сохранять важные данные локально.'
                ],
                [
                    'question' => 'Как быстро я получу доступ после оплаты?',
                    'answer' => 'Доступ к аккаунту предоставляется автоматически в течение нескольких минут после успешной оплаты. В редких случаях это может занять до 1 часа. Если доступ не был предоставлен в течение этого времени, пожалуйста, свяжитесь с нашей службой поддержки.'
                ],
                [
                    'question' => 'Какие способы оплаты доступны?',
                    'answer' => 'Мы принимаем различные способы оплаты: банковские карты (Visa, Mastercard), электронные кошельки и криптовалюты. Все платежи обрабатываются через защищенные платежные системы с использованием шифрования. Детали доступных способов оплаты отображаются на странице оформления подписки.'
                ],
                [
                    'question' => 'Можно ли отменить подписку в любой момент?',
                    'answer' => 'Да, вы можете отменить подписку в любой момент через личный кабинет. Отмена подписки означает, что автоматическое продление не будет происходить, но доступ к сервису сохранится до конца оплаченного периода. Возврат средств за неиспользованный период не предусмотрен.'
                ],
                [
                    'question' => 'Что делать, если сервис временно недоступен?',
                    'answer' => 'Если сервис временно недоступен из-за технических проблем на стороне провайдера (например, ChatGPT или Midjourney), мы работаем над восстановлением доступа как можно быстрее. В таких случаях мы уведомляем пользователей и продлеваем подписку на период простоя. Если проблема сохраняется более 24 часов, свяжитесь с поддержкой.'
                ],
                [
                    'question' => 'Есть ли ограничения по использованию аккаунта?',
                    'answer' => 'Поскольку аккаунт используется совместно несколькими пользователями, мы просим соблюдать разумные ограничения: не создавать чрезмерное количество запросов, не использовать аккаунт для спама или незаконной деятельности. Конкретные лимиты зависят от сервиса и указаны в описании подписки. При злоупотреблении доступ может быть ограничен.'
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'en' => json_encode([
                [
                    'question' => 'Will other users see my chats in ChatGPT?',
                    'answer' => 'Yes, since this is a shared account, chat history may be visible to other users. We recommend not entering confidential data (passwords, personal documents, financial information) into chats. You can also use the archiving or deletion function after completing your work.'
                ],
                [
                    'question' => 'What to do if the account password stops working?',
                    'answer' => 'This happens extremely rarely, but if it does happen, contact our technical support immediately. We will quickly issue you a new password or replace the account so you can continue working without long pauses.'
                ],
                [
                    'question' => 'Do I need to use a VPN?',
                    'answer' => 'This depends on the service and your location. For example, for ChatGPT or Midjourney, a VPN may be required if these services are officially unavailable in your country. Our site itself works without a VPN.'
                ],
                [
                    'question' => 'Is this an official subscription or a hacked account?',
                    'answer' => 'We provide access to official, legally paid subscriptions. Savings are achieved through sharing (joint use) of one account by a group of users. This is safe and stable.'
                ],
                [
                    'question' => 'Can I renew the subscription on the same account or is a new one issued each month?',
                    'answer' => 'Usually, when renewing a subscription, we provide access to a new account to ensure security and stability of the service. All your chat history remains on the old account, so we recommend saving important data locally.'
                ],
                [
                    'question' => 'How quickly will I get access after payment?',
                    'answer' => 'Account access is provided automatically within a few minutes after successful payment. In rare cases, this may take up to 1 hour. If access has not been provided within this time, please contact our support service.'
                ],
                [
                    'question' => 'What payment methods are available?',
                    'answer' => 'We accept various payment methods: bank cards (Visa, Mastercard), e-wallets, and cryptocurrencies. All payments are processed through secure payment systems using encryption. Details of available payment methods are displayed on the subscription page.'
                ],
                [
                    'question' => 'Can I cancel my subscription at any time?',
                    'answer' => 'Yes, you can cancel your subscription at any time through your personal account. Canceling a subscription means that automatic renewal will not occur, but access to the service will remain until the end of the paid period. Refunds for unused periods are not provided.'
                ],
                [
                    'question' => 'What to do if the service is temporarily unavailable?',
                    'answer' => 'If the service is temporarily unavailable due to technical issues on the provider\'s side (for example, ChatGPT or Midjourney), we work to restore access as quickly as possible. In such cases, we notify users and extend subscriptions for the downtime period. If the problem persists for more than 24 hours, contact support.'
                ],
                [
                    'question' => 'Are there any usage limits for the account?',
                    'answer' => 'Since the account is shared by multiple users, we ask you to observe reasonable limits: do not create an excessive number of requests, do not use the account for spam or illegal activities. Specific limits depend on the service and are indicated in the subscription description. In case of abuse, access may be restricted.'
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'uk' => json_encode([
                [
                    'question' => 'Чи будуть інші користувачі бачити мої чати в ChatGPT?',
                    'answer' => 'Так, оскільки це спільний акаунт, історія чатів може бути видима іншим користувачам. Ми рекомендуємо не вводити в чати конфіденційні дані (паролі, особисті документи, фінансову інформацію). Ви також можете використовувати функцію архівації або видалення чатів після завершення роботи.'
                ],
                [
                    'question' => 'Що робити, якщо пароль від акаунта перестане підходити?',
                    'answer' => 'Таке трапляється надзвичайно рідко, але якщо це сталося — одразу пишіть нам у техпідтримку. Ми оперативно видамо вам новий пароль або замінимо акаунт, щоб ви могли продовжити роботу без тривалих пауз.'
                ],
                [
                    'question' => 'Чи потрібно використовувати VPN?',
                    'answer' => 'Це залежить від сервісу та вашого місцезнаходження. Наприклад, для ChatGPT або Midjourney може знадобитися VPN, якщо ці сервіси офіційно недоступні у вашій країні. Сам наш сайт працює без VPN.'
                ],
                [
                    'question' => 'Це офіційна підписка чи зламаний акаунт?',
                    'answer' => 'Ми надаємо доступ до офіційних, легально оплачених підписок. Економія досягається за рахунок шерингу (спільного використання) одного акаунта групою користувачів. Це безпечно та стабільно.'
                ],
                [
                    'question' => 'Чи можна продовжити підписку на той же акаунт чи кожного місяця видається новий?',
                    'answer' => 'Зазвичай при продовженні підписки ми видаємо доступ до нового акаунта для забезпечення безпеки та стабільності роботи сервісу. Вся історія ваших діалогів залишається на старому акаунті, тому рекомендуємо зберігати важливі дані локально.'
                ],
                [
                    'question' => 'Як швидко я отримаю доступ після оплати?',
                    'answer' => 'Доступ до акаунта надається автоматично протягом кількох хвилин після успішної оплати. У рідкісних випадках це може зайняти до 1 години. Якщо доступ не був наданий протягом цього часу, будь ласка, зв\'яжіться з нашою службою підтримки.'
                ],
                [
                    'question' => 'Які способи оплати доступні?',
                    'answer' => 'Ми приймаємо різні способи оплати: банківські картки (Visa, Mastercard), електронні гаманці та криптовалюти. Всі платежі обробляються через захищені платіжні системи з використанням шифрування. Деталі доступних способів оплати відображаються на сторінці оформлення підписки.'
                ],
                [
                    'question' => 'Чи можна скасувати підписку в будь-який момент?',
                    'answer' => 'Так, ви можете скасувати підписку в будь-який момент через особистий кабінет. Скасування підписки означає, що автоматичне продовження не відбуватиметься, але доступ до сервісу збережеться до кінця оплаченого періоду. Повернення коштів за невикористаний період не передбачено.'
                ],
                [
                    'question' => 'Що робити, якщо сервіс тимчасово недоступний?',
                    'answer' => 'Якщо сервіс тимчасово недоступний через технічні проблеми на стороні провайдера (наприклад, ChatGPT або Midjourney), ми працюємо над відновленням доступу якнайшвидше. У таких випадках ми повідомляємо користувачів і продовжуємо підписку на період простою. Якщо проблема зберігається більше 24 годин, зв\'яжіться з підтримкою.'
                ],
                [
                    'question' => 'Чи є обмеження щодо використання акаунта?',
                    'answer' => 'Оскільки акаунт використовується спільно кількома користувачами, ми просимо дотримуватися розумних обмежень: не створювати надмірну кількість запитів, не використовувати акаунт для спаму або незаконної діяльності. Конкретні ліміти залежать від сервісу та вказані в описі підписки. При зловживанні доступ може бути обмежений.'
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'es' => json_encode([
                [
                    'question' => '¿Verán otros usuarios mis chats en ChatGPT?',
                    'answer' => 'Sí, como esta es una cuenta compartida, el historial de chats puede ser visible para otros usuarios. Recomendamos no ingresar datos confidenciales (contraseñas, documentos personales, información financiera) en los chats. También puede usar la función de archivo o eliminación después de completar su trabajo.'
                ],
                [
                    'question' => '¿Qué hacer si la contraseña de la cuenta deja de funcionar?',
                    'answer' => 'Esto ocurre extremadamente raro, pero si sucede, contáctenos de inmediato en soporte técnico. Le emitiremos rápidamente una nueva contraseña o reemplazaremos la cuenta para que pueda continuar trabajando sin largas pausas.'
                ],
                [
                    'question' => '¿Necesito usar una VPN?',
                    'answer' => 'Esto depende del servicio y su ubicación. Por ejemplo, para ChatGPT o Midjourney puede ser necesaria una VPN si estos servicios no están oficialmente disponibles en su país. Nuestro sitio funciona sin VPN.'
                ],
                [
                    'question' => '¿Es una suscripción oficial o una cuenta pirateada?',
                    'answer' => 'Proporcionamos acceso a suscripciones oficiales y legalmente pagadas. El ahorro se logra compartiendo (uso conjunto) de una cuenta por un grupo de usuarios. Esto es seguro y estable.'
                ],
                [
                    'question' => '¿Puedo renovar la suscripción en la misma cuenta o se emite una nueva cada mes?',
                    'answer' => 'Por lo general, al renovar una suscripción, proporcionamos acceso a una nueva cuenta para garantizar la seguridad y estabilidad del servicio. Todo su historial de chats permanece en la cuenta anterior, por lo que recomendamos guardar datos importantes localmente.'
                ],
                [
                    'question' => '¿Qué tan rápido obtendré acceso después del pago?',
                    'answer' => 'El acceso a la cuenta se proporciona automáticamente en unos minutos después del pago exitoso. En casos raros, esto puede tomar hasta 1 hora. Si el acceso no se ha proporcionado dentro de este tiempo, por favor contacte con nuestro servicio de soporte.'
                ],
                [
                    'question' => '¿Qué métodos de pago están disponibles?',
                    'answer' => 'Aceptamos varios métodos de pago: tarjetas bancarias (Visa, Mastercard), billeteras electrónicas y criptomonedas. Todos los pagos se procesan a través de sistemas de pago seguros con encriptación. Los detalles de los métodos de pago disponibles se muestran en la página de suscripción.'
                ],
                [
                    'question' => '¿Puedo cancelar mi suscripción en cualquier momento?',
                    'answer' => 'Sí, puede cancelar su suscripción en cualquier momento a través de su cuenta personal. Cancelar una suscripción significa que la renovación automática no ocurrirá, pero el acceso al servicio permanecerá hasta el final del período pagado. No se proporcionan reembolsos por períodos no utilizados.'
                ],
                [
                    'question' => '¿Qué hacer si el servicio está temporalmente no disponible?',
                    'answer' => 'Si el servicio está temporalmente no disponible debido a problemas técnicos del lado del proveedor (por ejemplo, ChatGPT o Midjourney), trabajamos para restaurar el acceso lo más rápido posible. En tales casos, notificamos a los usuarios y extendemos las suscripciones por el período de inactividad. Si el problema persiste por más de 24 horas, contacte con soporte.'
                ],
                [
                    'question' => '¿Hay límites de uso para la cuenta?',
                    'answer' => 'Dado que la cuenta es compartida por múltiples usuarios, le pedimos que observe límites razonables: no crear un número excesivo de solicitudes, no usar la cuenta para spam o actividades ilegales. Los límites específicos dependen del servicio y se indican en la descripción de la suscripción. En caso de abuso, el acceso puede ser restringido.'
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'zh' => json_encode([
                [
                    'question' => '其他用户会看到我在ChatGPT中的聊天吗？',
                    'answer' => '是的，由于这是共享账户，聊天记录可能对其他用户可见。我们建议不要在聊天中输入机密数据（密码、个人文档、财务信息）。您也可以在完成工作后使用归档或删除功能。'
                ],
                [
                    'question' => '如果账户密码停止工作该怎么办？',
                    'answer' => '这种情况极少发生，但如果确实发生了，请立即联系我们的技术支持。我们将快速为您发放新密码或更换账户，以便您可以继续工作而不会长时间中断。'
                ],
                [
                    'question' => '需要使用VPN吗？',
                    'answer' => '这取决于服务和服务位置。例如，对于ChatGPT或Midjourney，如果这些服务在您所在的国家/地区正式不可用，则可能需要VPN。我们的网站本身可以在没有VPN的情况下运行。'
                ],
                [
                    'question' => '这是官方订阅还是破解账户？',
                    'answer' => '我们提供对官方、合法付费订阅的访问。通过共享（共同使用）一个账户来实现节省。这是安全和稳定的。'
                ],
                [
                    'question' => '可以在同一账户上续订订阅，还是每个月都会发放新账户？',
                    'answer' => '通常，在续订订阅时，我们会提供对新账户的访问权限，以确保服务的安全性和稳定性。您的所有聊天历史记录都保留在旧账户上，因此我们建议在本地保存重要数据。'
                ],
                [
                    'question' => '付款后多久能获得访问权限？',
                    'answer' => '账户访问权限会在付款成功后几分钟内自动提供。在极少数情况下，这可能需要长达1小时。如果在此时间内未提供访问权限，请联系我们的支持服务。'
                ],
                [
                    'question' => '有哪些可用的支付方式？',
                    'answer' => '我们接受多种支付方式：银行卡（Visa、Mastercard）、电子钱包和加密货币。所有付款都通过使用加密的安全支付系统处理。可用支付方式的详细信息显示在订阅页面上。'
                ],
                [
                    'question' => '我可以随时取消订阅吗？',
                    'answer' => '是的，您可以随时通过个人账户取消订阅。取消订阅意味着不会发生自动续订，但服务访问权限将保留到付费期结束。不提供未使用期间的退款。'
                ],
                [
                    'question' => '如果服务暂时不可用怎么办？',
                    'answer' => '如果由于提供商方面的技术问题（例如ChatGPT或Midjourney）导致服务暂时不可用，我们会尽快恢复访问。在这种情况下，我们会通知用户并延长停机期间的订阅。如果问题持续超过24小时，请联系支持。'
                ],
                [
                    'question' => '账户使用是否有限制？',
                    'answer' => '由于账户由多个用户共享，我们要求您遵守合理的限制：不要创建过多的请求，不要将账户用于垃圾邮件或非法活动。具体限制取决于服务，并在订阅说明中注明。如果滥用，访问可能会受到限制。'
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ];
    }
}

