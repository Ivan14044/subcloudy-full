<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Content;
use App\Models\ContentTranslation;

// Находим контент с ID 2
$content = Content::find(2);

if (!$content) {
    echo "Content with ID 2 not found!\n";
    exit(1);
}

echo "Content found: {$content->name} ({$content->code})\n";

// Реалистичные отзывы на разных языках
$reviews = [
    'ru' => [
        [
            'rating' => '5',
            'name' => 'Александр Петров',
            'text' => 'Отличный сервис! Использую ChatGPT через SubCloudy уже 3 месяца. Экономия значительная, а качество доступа такое же, как при прямой подписке. Рекомендую!'
        ],
        [
            'rating' => '5',
            'name' => 'Мария Иванова',
            'text' => 'Очень довольна! Подписка на Midjourney через SubCloudy работает без нареканий. Цена в два раза ниже, чем официальная. Обслуживание на высшем уровне.'
        ],
        [
            'rating' => '5',
            'name' => 'Дмитрий Соколов',
            'text' => 'Пользуюсь несколькими сервисами через SubCloudy. Все работает стабильно, поддержка отвечает быстро. Отличная альтернатива дорогим подпискам!'
        ],
        [
            'rating' => '5',
            'name' => 'Елена Кузнецова',
            'text' => 'Прекрасный сервис для экономии! Использую Canva Pro и ChatGPT. Все функции доступны, никаких ограничений. Очень рекомендую попробовать!'
        ],
        [
            'rating' => '5',
            'name' => 'Игорь Волков',
            'text' => 'Отличное решение для бизнеса! Экономим на подписках для команды, при этом получаем полный доступ ко всем функциям. Спасибо SubCloudy!'
        ]
    ],
    'en' => [
        [
            'rating' => '5',
            'name' => 'John Smith',
            'text' => 'Great service! I\'ve been using ChatGPT through SubCloudy for 3 months. Significant savings, and the access quality is the same as a direct subscription. Highly recommend!'
        ],
        [
            'rating' => '5',
            'name' => 'Sarah Johnson',
            'text' => 'Very satisfied! My Midjourney subscription through SubCloudy works flawlessly. Price is half of the official one. Excellent customer service.'
        ],
        [
            'rating' => '5',
            'name' => 'Michael Brown',
            'text' => 'Using multiple services through SubCloudy. Everything works stable, support responds quickly. Great alternative to expensive subscriptions!'
        ],
        [
            'rating' => '5',
            'name' => 'Emily Davis',
            'text' => 'Perfect service for saving money! I use Canva Pro and ChatGPT. All features available, no limitations. Highly recommend trying it!'
        ],
        [
            'rating' => '5',
            'name' => 'David Wilson',
            'text' => 'Excellent solution for business! We save on team subscriptions while getting full access to all features. Thanks SubCloudy!'
        ]
    ],
    'uk' => [
        [
            'rating' => '5',
            'name' => 'Олександр Петренко',
            'text' => 'Чудовий сервіс! Використовую ChatGPT через SubCloudy вже 3 місяці. Економія значна, а якість доступу така сама, як при прямій підписці. Рекомендую!'
        ],
        [
            'rating' => '5',
            'name' => 'Марія Коваленко',
            'text' => 'Дуже задоволена! Підписка на Midjourney через SubCloudy працює без нарікань. Ціна в два рази нижча, ніж офіційна. Обслуговування на високому рівні.'
        ],
        [
            'rating' => '5',
            'name' => 'Дмитро Шевченко',
            'text' => 'Користуюся кількома сервісами через SubCloudy. Все працює стабільно, підтримка відповідає швидко. Відмінна альтернатива дорогим підпискам!'
        ],
        [
            'rating' => '5',
            'name' => 'Олена Мельник',
            'text' => 'Прекрасний сервіс для економії! Використовую Canva Pro та ChatGPT. Всі функції доступні, жодних обмежень. Дуже рекомендую спробувати!'
        ],
        [
            'rating' => '5',
            'name' => 'Ігор Бондаренко',
            'text' => 'Відмінне рішення для бізнесу! Економимо на підписках для команди, при цьому отримуємо повний доступ до всіх функцій. Дякую SubCloudy!'
        ]
    ],
    'es' => [
        [
            'rating' => '5',
            'name' => 'Carlos García',
            'text' => '¡Excelente servicio! Llevo 3 meses usando ChatGPT a través de SubCloudy. Ahorro significativo y la calidad de acceso es la misma que una suscripción directa. ¡Muy recomendado!'
        ],
        [
            'rating' => '5',
            'name' => 'Ana Martínez',
            'text' => '¡Muy satisfecha! Mi suscripción a Midjourney a través de SubCloudy funciona perfectamente. El precio es la mitad del oficial. Excelente atención al cliente.'
        ],
        [
            'rating' => '5',
            'name' => 'Luis Rodríguez',
            'text' => 'Uso varios servicios a través de SubCloudy. Todo funciona de forma estable, el soporte responde rápidamente. ¡Excelente alternativa a suscripciones caras!'
        ],
        [
            'rating' => '5',
            'name' => 'María López',
            'text' => '¡Servicio perfecto para ahorrar dinero! Uso Canva Pro y ChatGPT. Todas las funciones disponibles, sin limitaciones. ¡Muy recomendado probarlo!'
        ],
        [
            'rating' => '5',
            'name' => 'José Fernández',
            'text' => '¡Excelente solución para negocios! Ahorramos en suscripciones del equipo mientras obtenemos acceso completo a todas las funciones. ¡Gracias SubCloudy!'
        ]
    ],
    'zh' => [
        [
            'rating' => '5',
            'name' => '张伟',
            'text' => '很棒的服务！我已经通过 SubCloudy 使用 ChatGPT 3 个月了。节省了很多钱，访问质量与直接订阅相同。强烈推荐！'
        ],
        [
            'rating' => '5',
            'name' => '李娜',
            'text' => '非常满意！我通过 SubCloudy 的 Midjourney 订阅运行完美。价格是官方的一半。客户服务很棒。'
        ],
        [
            'rating' => '5',
            'name' => '王强',
            'text' => '通过 SubCloudy 使用多个服务。一切运行稳定，支持响应迅速。昂贵的订阅的绝佳替代品！'
        ],
        [
            'rating' => '5',
            'name' => '刘芳',
            'text' => '完美的省钱服务！我使用 Canva Pro 和 ChatGPT。所有功能可用，没有限制。强烈推荐尝试！'
        ],
        [
            'rating' => '5',
            'name' => '陈明',
            'text' => '商业的绝佳解决方案！我们节省团队订阅费用，同时获得所有功能的完全访问权限。谢谢 SubCloudy！'
        ]
    ]
];

// Удаляем старые переводы для этого контента
ContentTranslation::where('content_id', $content->id)->delete();

$addedCount = 0;

// Добавляем отзывы для каждого языка
foreach ($reviews as $locale => $localeReviews) {
    foreach ($localeReviews as $index => $review) {
        // Добавляем каждое поле отзыва
        foreach ($review as $fieldKey => $value) {
            $code = "{$content->code}.{$fieldKey}.{$index}";
            
            ContentTranslation::create([
                'content_id' => $content->id,
                'locale' => $locale,
                'code' => $code,
                'value' => $value
            ]);
        }
        $addedCount++;
    }
}

echo "Successfully added {$addedCount} reviews for " . count($reviews) . " languages!\n";
echo "Total translations created: " . ($addedCount * 3) . " (rating, name, text for each review)\n";

