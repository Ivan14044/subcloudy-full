<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Очищаем существующие отзывы (опционально)
        // DB::table('review_translations')->delete();
        // DB::table('reviews')->delete();

        $reviews = $this->getReviewsData();

        foreach ($reviews as $index => $reviewData) {
            $review = Review::create([
                'rating' => $reviewData['rating'],
                'order' => $index,
                'is_active' => true,
            ]);

            // Сохраняем переводы для каждого языка
            $translationData = [
                'name' => $reviewData['name'],
                'text' => $reviewData['text'],
                'photo' => $reviewData['photo'] ?? null,
                'logo' => $reviewData['logo'] ?? null,
            ];

            $review->saveTranslation($translationData);
        }

        $this->command->info('Создано ' . count($reviews) . ' отзывов');
    }

    private function getReviewsData(): array
    {
        return [
            [
                'rating' => 5,
                'name' => [
                    'ru' => 'Марина Волкова',
                    'en' => 'Marina Volkova',
                    'uk' => 'Марина Волкова',
                    'es' => 'Marina Volkova',
                    'zh' => '玛丽娜·沃尔科娃',
                ],
                'text' => [
                    'ru' => 'Использую SubCloudy уже три месяца, и это просто находка! Все сервисы в одном месте, удобный интерфейс, никаких проблем с оплатой. Экономия времени колоссальная. Рекомендую всем, кто работает с AI-инструментами.',
                    'en' => 'I\'ve been using SubCloudy for three months now, and it\'s a real gem! All services in one place, user-friendly interface, no payment issues. Massive time saver. I recommend it to everyone working with AI tools.',
                    'uk' => 'Використовую SubCloudy вже три місяці, і це просто знахідка! Всі сервіси в одному місці, зручний інтерфейс, жодних проблем з оплатою. Економія часу колосальна. Рекомендую всім, хто працює з AI-інструментами.',
                    'es' => 'Llevo usando SubCloudy tres meses y es una verdadera joya. Todos los servicios en un solo lugar, interfaz fácil de usar, sin problemas de pago. Ahorra muchísimo tiempo. Lo recomiendo a todos los que trabajan con herramientas de IA.',
                    'zh' => '我使用SubCloudy已经三个月了，它真是个好工具！所有服务都在一个地方，用户界面友好，没有支付问题。节省了大量时间。我向所有使用AI工具的人推荐它。',
                ],
            ],
            [
                'rating' => 5,
                'name' => [
                    'ru' => 'Дмитрий Козлов',
                    'en' => 'Dmitry Kozlov',
                    'uk' => 'Дмитро Козлов',
                    'es' => 'Dmitry Kozlov',
                    'zh' => '德米特里·科兹洛夫',
                ],
                'text' => [
                    'ru' => 'Отличная платформа! Особенно понравился доступ к премиум функциям без необходимости подписываться на каждый сервис отдельно. Техподдержка работает оперативно, всегда помогают решить вопросы. Оценка 5/5!',
                    'en' => 'Excellent platform! I especially liked access to premium features without needing to subscribe to each service separately. Tech support works quickly and always helps resolve issues. Rating 5/5!',
                    'uk' => 'Чудова платформа! Особливо сподобався доступ до преміум функцій без необхідності підписуватися на кожен сервіс окремо. Техпідтримка працює оперативно, завжди допомагають вирішити питання. Оцінка 5/5!',
                    'es' => '¡Excelente plataforma! Me gustó especialmente el acceso a funciones premium sin necesidad de suscribirse a cada servicio por separado. El soporte técnico funciona rápidamente y siempre ayuda a resolver problemas. ¡Calificación 5/5!',
                    'zh' => '优秀的平台！我特别喜欢无需分别订阅每个服务就能访问高级功能。技术支持响应迅速，总能帮助解决问题。评分5/5！',
                ],
            ],
            [
                'rating' => 5,
                'name' => [
                    'ru' => 'Анна Соколова',
                    'en' => 'Anna Sokolova',
                    'uk' => 'Анна Соколова',
                    'es' => 'Anna Sokolova',
                    'zh' => '安娜·索科洛娃',
                ],
                'text' => [
                    'ru' => 'Как дизайнер, мне постоянно нужны разные AI-инструменты. SubCloudy решил эту проблему раз и навсегда. Теперь все необходимое под рукой, плюс хорошие скидки при подписке. Очень довольна!',
                    'en' => 'As a designer, I constantly need different AI tools. SubCloudy solved this problem once and for all. Now everything I need is at hand, plus good discounts when subscribing. Very satisfied!',
                    'uk' => 'Як дизайнер, мені постійно потрібні різні AI-інструменти. SubCloudy вирішив цю проблему раз і назавжди. Тепер все необхідне під рукою, плюс хороші знижки при підписці. Дуже задоволена!',
                    'es' => 'Como diseñadora, constantemente necesito diferentes herramientas de IA. SubCloudy resolvió este problema de una vez por todas. Ahora todo lo que necesito está a mano, además de buenos descuentos al suscribirse. ¡Muy satisfecha!',
                    'zh' => '作为设计师，我经常需要不同的AI工具。SubCloudy一劳永逸地解决了这个问题。现在所需的一切都在手边，订阅时还有很好的折扣。非常满意！',
                ],
            ],
            [
                'rating' => 4,
                'name' => [
                    'ru' => 'Сергей Новиков',
                    'en' => 'Sergey Novikov',
                    'uk' => 'Сергій Новиков',
                    'es' => 'Sergey Novikov',
                    'zh' => '谢尔盖·诺维科夫',
                ],
                'text' => [
                    'ru' => 'Хороший сервис, удобно, что все в одном месте. Иногда бывают небольшие задержки при работе некоторых инструментов, но в целом все работает стабильно. Цена адекватная для такого функционала.',
                    'en' => 'Good service, convenient that everything is in one place. Sometimes there are minor delays when working with some tools, but overall everything works stably. The price is reasonable for such functionality.',
                    'uk' => 'Хороший сервіс, зручно, що все в одному місці. Іноді бувають невеликі затримки при роботі з деякими інструментами, але загалом все працює стабільно. Ціна адекватна для такого функціоналу.',
                    'es' => 'Buen servicio, conveniente que todo esté en un solo lugar. A veces hay pequeños retrasos al trabajar con algunas herramientas, pero en general todo funciona de forma estable. El precio es razonable para tal funcionalidad.',
                    'zh' => '服务很好，所有功能都集中在一个地方很方便。有时在使用某些工具时会有轻微的延迟，但总的来说一切运行稳定。对于这样的功能来说，价格是合理的。',
                ],
            ],
            [
                'rating' => 5,
                'name' => [
                    'ru' => 'Елена Петрова',
                    'en' => 'Elena Petrova',
                    'uk' => 'Олена Петрова',
                    'es' => 'Elena Petrova',
                    'zh' => '叶莲娜·彼得罗娃',
                ],
                'text' => [
                    'ru' => 'Пользуюсь для работы уже полгода. Качество сервисов на высоте, обновления приходят регулярно. Один раз был технический сбой, но поддержка быстро все исправила. Отличный сервис, рекомендую коллегам.',
                    'en' => 'I\'ve been using it for work for six months now. The quality of services is excellent, updates come regularly. There was one technical glitch, but support quickly fixed everything. Great service, I recommend it to colleagues.',
                    'uk' => 'Користуюся для роботи вже півроку. Якість сервісів на висоті, оновлення надходять регулярно. Одного разу був технічний збій, але підтримка швидко все виправила. Відмінний сервіс, рекомендую колегам.',
                    'es' => 'Llevo usándolo para trabajar seis meses. La calidad de los servicios es excelente, las actualizaciones llegan regularmente. Hubo un problema técnico, pero el soporte lo solucionó rápidamente. Excelente servicio, lo recomiendo a los colegas.',
                    'zh' => '我已经在工作中使用它半年了。服务质量非常好，更新定期发布。有一次技术故障，但支持人员很快就解决了。很棒的服务，我向同事们推荐。',
                ],
            ],
            [
                'rating' => 5,
                'name' => [
                    'ru' => 'Александр Морозов',
                    'en' => 'Alexander Morozov',
                    'uk' => 'Олександр Морозов',
                    'es' => 'Alexander Morozov',
                    'zh' => '亚历山大·莫罗佐夫',
                ],
                'text' => [
                    'ru' => 'Очень впечатлен функционалом. Использую для маркетинга и контент-производства. Экономия времени и денег ощутимая. Интерфейс интуитивно понятный, разобрался без инструкций. Планирую продлевать подписку.',
                    'en' => 'Very impressed with the functionality. I use it for marketing and content production. The savings in time and money are noticeable. The interface is intuitive, I figured it out without instructions. I plan to renew my subscription.',
                    'uk' => 'Дуже вражений функціоналом. Використовую для маркетингу та контент-виробництва. Економія часу та грошей відчутна. Інтерфейс інтуїтивно зрозумілий, розібрався без інструкцій. Планую продовжувати підписку.',
                    'es' => 'Muy impresionado con la funcionalidad. Lo uso para marketing y producción de contenido. El ahorro de tiempo y dinero es notable. La interfaz es intuitiva, la entendí sin instrucciones. Planeo renovar mi suscripción.',
                    'zh' => '对功能印象非常深刻。我将其用于营销和内容制作。节省的时间和金钱是显而易见的。界面直观，我无需说明就能理解。我计划续订订阅。',
                ],
            ],
            [
                'rating' => 4,
                'name' => [
                    'ru' => 'Ольга Лебедева',
                    'en' => 'Olga Lebedeva',
                    'uk' => 'Ольга Лебедєва',
                    'es' => 'Olga Lebedeva',
                    'zh' => '奥尔加·列别杰娃',
                ],
                'text' => [
                    'ru' => 'Хороший выбор инструментов, большинство работают отлично. Некоторые функции могли бы быть улучшены, но в целом платформа соответствует ожиданиям. Поддержка отвечает оперативно, это большой плюс.',
                    'en' => 'Good selection of tools, most work great. Some features could be improved, but overall the platform meets expectations. Support responds quickly, which is a big plus.',
                    'uk' => 'Хороший вибір інструментів, більшість працюють відмінно. Деякі функції могли б бути покращені, але загалом платформа відповідає очікуванням. Підтримка відповідає оперативно, це великий плюс.',
                    'es' => 'Buena selección de herramientas, la mayoría funcionan muy bien. Algunas funciones podrían mejorarse, pero en general la plataforma cumple con las expectativas. El soporte responde rápidamente, lo cual es una gran ventaja.',
                    'zh' => '工具选择很好，大多数都运行良好。有些功能可以改进，但总的来说平台符合期望。支持响应迅速，这是一个很大的优势。',
                ],
            ],
            [
                'rating' => 5,
                'name' => [
                    'ru' => 'Игорь Федоров',
                    'en' => 'Igor Fedorov',
                    'uk' => 'Ігор Федоров',
                    'es' => 'Igor Fedorov',
                    'zh' => '伊戈尔·费多罗夫',
                ],
                'text' => [
                    'ru' => 'Лучшее решение для работы с AI, которое я пробовал. Все основные сервисы доступны, интеграция простая, никаких проблем с авторизацией. Экономия существенная по сравнению с отдельными подписками. Всем советую!',
                    'en' => 'The best AI solution I\'ve tried. All major services are available, integration is simple, no authorization issues. Significant savings compared to separate subscriptions. I recommend it to everyone!',
                    'uk' => 'Найкраще рішення для роботи з AI, яке я пробував. Всі основні сервіси доступні, інтеграція проста, жодних проблем з авторизацією. Економія істотна порівняно з окремими підписками. Всім раджу!',
                    'es' => 'La mejor solución de IA que he probado. Todos los servicios principales están disponibles, la integración es simple, sin problemas de autorización. Ahorro significativo en comparación con suscripciones separadas. ¡Se lo recomiendo a todos!',
                    'zh' => '我尝试过的最好的AI解决方案。所有主要服务都可用，集成简单，没有授权问题。与单独订阅相比，节省了大量费用。我向大家推荐！',
                ],
            ],
            [
                'rating' => 5,
                'name' => [
                    'ru' => 'Наталья Смирнова',
                    'en' => 'Natalia Smirnova',
                    'uk' => 'Наталія Смирнова',
                    'es' => 'Natalia Smirnova',
                    'zh' => '娜塔莉亚·斯米尔诺娃',
                ],
                'text' => [
                    'ru' => 'Открыла для себя SubCloudy месяц назад и уже не представляю работу без него. Все инструменты на высоте, особенно удобно для создания контента. Очень довольна покупкой, уже посоветовала друзьям.',
                    'en' => 'I discovered SubCloudy a month ago and can\'t imagine working without it now. All tools are excellent, especially convenient for content creation. Very happy with the purchase, I\'ve already recommended it to friends.',
                    'uk' => 'Відкрила для себе SubCloudy місяць тому і вже не уявляю роботу без нього. Всі інструменти на висоті, особливо зручно для створення контенту. Дуже задоволена покупкою, вже порадила друзям.',
                    'es' => 'Descubrí SubCloudy hace un mes y ya no puedo imaginar trabajar sin él. Todas las herramientas son excelentes, especialmente convenientes para la creación de contenido. Muy contenta con la compra, ya se lo recomendé a amigos.',
                    'zh' => '一个月前发现了SubCloudy，现在无法想象没有它的工作。所有工具都非常出色，对内容创建特别方便。对这次购买非常满意，已经推荐给朋友了。',
                ],
            ],
            [
                'rating' => 4,
                'name' => [
                    'ru' => 'Михаил Кузнецов',
                    'en' => 'Mikhail Kuznetsov',
                    'uk' => 'Михайло Кузнєцов',
                    'es' => 'Mikhail Kuznetsov',
                    'zh' => '米哈伊尔·库兹涅佐夫',
                ],
                'text' => [
                    'ru' => 'Солидная платформа с хорошим набором инструментов. Использую для бизнес-задач, помогает оптимизировать процессы. Есть что улучшить, но в целом сервис работает как надо. Рекомендую попробовать.',
                    'en' => 'Solid platform with a good set of tools. I use it for business tasks, it helps optimize processes. There\'s room for improvement, but overall the service works as it should. I recommend giving it a try.',
                    'uk' => 'Солідна платформа з хорошим набором інструментів. Використовую для бізнес-задач, допомагає оптимізувати процеси. Є що покращити, але загалом сервіс працює як треба. Рекомендую спробувати.',
                    'es' => 'Plataforma sólida con un buen conjunto de herramientas. La uso para tareas comerciales, ayuda a optimizar procesos. Hay margen de mejora, pero en general el servicio funciona como debería. Recomiendo probarlo.',
                    'zh' => '一个可靠的平台，拥有良好的工具集。我将其用于业务任务，有助于优化流程。有改进的空间，但总的来说服务按预期工作。我建议尝试一下。',
                ],
            ],
            [
                'rating' => 5,
                'name' => [
                    'ru' => 'Виктория Романова',
                    'en' => 'Victoria Romanova',
                    'uk' => 'Вікторія Романова',
                    'es' => 'Victoria Romanova',
                    'zh' => '维多利亚·罗曼诺娃',
                ],
                'text' => [
                    'ru' => 'Как копирайтер, я использую множество AI-сервисов. SubCloudy объединил их все в одном месте - это просто спасение! Удобно, быстро, экономично. Очень довольна, буду пользоваться дальше.',
                    'en' => 'As a copywriter, I use many AI services. SubCloudy combined them all in one place - it\'s a lifesaver! Convenient, fast, cost-effective. Very satisfied, I\'ll continue using it.',
                    'uk' => 'Як копірайтер, я використовую безліч AI-сервісів. SubCloudy об\'єднав їх усі в одному місці - це просто порятунок! Зручно, швидко, економно. Дуже задоволена, буду користуватися далі.',
                    'es' => 'Como redactora, uso muchos servicios de IA. SubCloudy los combinó todos en un solo lugar: ¡es un salvavidas! Conveniente, rápido, rentable. Muy satisfecha, seguiré usándolo.',
                    'zh' => '作为一名文案撰稿人，我使用许多AI服务。SubCloudy将它们都集中在一个地方——这真是救星！方便、快速、经济高效。非常满意，我会继续使用它。',
                ],
            ],
            [
                'rating' => 5,
                'name' => [
                    'ru' => 'Павел Орлов',
                    'en' => 'Pavel Orlov',
                    'uk' => 'Павло Орлов',
                    'es' => 'Pavel Orlov',
                    'zh' => '帕维尔·奥尔洛夫',
                ],
                'text' => [
                    'ru' => 'Зарегистрировался по рекомендации коллеги и не пожалел. Платформа действительно экономит время и деньги. Все работает стабильно, интерфейс приятный. Обязательно продлю подписку, когда истечет текущая.',
                    'en' => 'I signed up on a colleague\'s recommendation and don\'t regret it. The platform really saves time and money. Everything works stably, the interface is pleasant. I\'ll definitely renew my subscription when the current one expires.',
                    'uk' => 'Зареєструвався за рекомендацією колеги і не пошкодував. Платформа дійсно економить час і гроші. Все працює стабільно, інтерфейс приємний. Обов\'язково продовжу підписку, коли сплине поточна.',
                    'es' => 'Me registré por recomendación de un colega y no me arrepiento. La plataforma realmente ahorra tiempo y dinero. Todo funciona de forma estable, la interfaz es agradable. Definitivamente renovaré mi suscripción cuando expire la actual.',
                    'zh' => '在同事的推荐下注册了，一点也不后悔。该平台确实节省了时间和金钱。一切运行稳定，界面令人愉快。当前订阅到期时我一定会续订。',
                ],
            ],
        ];
    }
}

