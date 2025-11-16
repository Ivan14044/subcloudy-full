<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServiceTranslation;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        $services = [
            'chatgpt' => [
                'en' => [
                    'name' => 'ChatGPT',
                    'description' => "Advanced AI language model for content creation, coding assistance, and creative writing. Powered by OpenAI's cutting-edge technology."
                ],
                'es' => [
                    'name' => 'ChatGPT',
                    'description' => "Modelo avanzado de lenguaje AI para creación de contenido, asistencia en programación y escritura creativa. Impulsado por la tecnología de vanguardia de OpenAI."
                ],
                'ru' => [
                    'name' => 'ChatGPT',
                    'description' => "Передовая модель искусственного интеллекта для создания контента, помощи в программировании и креативного письма. Работает на передовой технологии OpenAI."
                ],
                'uk' => [
                    'name' => 'ChatGPT',
                    'description' => "Передова модель штучного інтелекту для створення контенту, допомоги в програмуванні та креативного письма. Працює на передовій технології OpenAI."
                ],
                'zh' => [
                    'name' => 'ChatGPT',
                    'description' => "先进的人工智能语言模型，用于内容创作、编程辅助和创意写作。由OpenAI的尖端技术提供支持。"
                ]
            ],
            'adspy' => [
                'en' => [
                    'name' => 'AdSpy',
                    'description' => "World's largest searchable database of Facebook and Instagram ads. Find winning ad creatives and spy on competitors' strategies."
                ],
                'es' => [
                    'name' => 'AdSpy',
                    'description' => "La base de datos más grande de anuncios de Facebook e Instagram. Encuentra creativos publicitarios exitosos y espía las estrategias de la competencia."
                ],
                'ru' => [
                    'name' => 'AdSpy',
                    'description' => "Крупнейшая база данных рекламы Facebook и Instagram. Находите успешные рекламные креативы и следите за стратегиями конкурентов."
                ],
                'uk' => [
                    'name' => 'AdSpy',
                    'description' => "Найбільша база даних реклами Facebook та Instagram. Знаходьте успішні рекламні креативи та слідкуйте за стратегіями конкурентів."
                ],
                'zh' => [
                    'name' => 'AdSpy',
                    'description' => "全球最大的Facebook和Instagram广告搜索数据库。发现成功的广告创意并监控竞争对手的策略。"
                ]
            ],
            'pipiads' => [
                'en' => [
                    'name' => 'PipiAds',
                    'description' => "Comprehensive ad intelligence tool for TikTok and Meta. Track trending ads, analyze successful campaigns, and discover winning products."
                ],
                'es' => [
                    'name' => 'PipiAds',
                    'description' => "Herramienta integral de inteligencia publicitaria para TikTok y Meta. Rastrea anuncios tendencia, analiza campañas exitosas y descubre productos rentables."
                ],
                'ru' => [
                    'name' => 'PipiAds',
                    'description' => "Комплексный инструмент анализа рекламы для TikTok и Meta. Отслеживайте популярную рекламу, анализируйте успешные кампании и находите прибыльные продукты."
                ],
                'uk' => [
                    'name' => 'PipiAds',
                    'description' => "Комплексний інструмент аналізу реклами для TikTok та Meta. Відстежуйте популярну рекламу, аналізуйте успішні кампанії та знаходьте прибуткові продукти."
                ],
                'zh' => [
                    'name' => 'PipiAds',
                    'description' => "针对TikTok和Meta的综合广告情报工具。跟踪热门广告，分析成功活动，发现盈利产品。"
                ]
            ],
            'canvapro' => [
                'en' => [
                    'name' => 'Canva Pro',
                    'description' => "Premium design platform with advanced features, Brand Kit, background remover, and unlimited premium content for professional designers."
                ],
                'es' => [
                    'name' => 'Canva Pro',
                    'description' => "Plataforma premium de diseño con funciones avanzadas, Kit de Marca, eliminador de fondos y contenido premium ilimitado para diseñadores profesionales."
                ],
                'ru' => [
                    'name' => 'Canva Pro',
                    'description' => "Премиум платформа для дизайна с расширенными функциями, Brand Kit, удалением фона и неограниченным премиум контентом для профессиональных дизайнеров."
                ],
                'uk' => [
                    'name' => 'Canva Pro',
                    'description' => "Преміум платформа для дизайну з розширеними функціями, Brand Kit, видаленням фону та необмеженим преміум контентом для професійних дизайнерів."
                ],
                'zh' => [
                    'name' => 'Canva Pro',
                    'description' => "高级设计平台，具有高级功能、品牌套件、背景移除和无限优质内容，适合专业设计师。"
                ]
            ],
            'adheart' => [
                'en' => [
                    'name' => 'Adheart',
                    'description' => "Advanced ad research tool for Facebook and Instagram. Monitor competitors, find profitable ads, and analyze marketing strategies."
                ],
                'es' => [
                    'name' => 'Adheart',
                    'description' => "Herramienta avanzada de investigación publicitaria para Facebook e Instagram. Monitorea competidores, encuentra anuncios rentables y analiza estrategias de marketing."
                ],
                'ru' => [
                    'name' => 'Adheart',
                    'description' => "Передовой инструмент исследования рекламы для Facebook и Instagram. Мониторинг конкурентов, поиск прибыльной рекламы и анализ маркетинговых стратегий."
                ],
                'uk' => [
                    'name' => 'Adheart',
                    'description' => "Передовий інструмент дослідження реклами для Facebook та Instagram. Моніторинг конкурентів, пошук прибуткової реклами та аналіз маркетингових стратегій."
                ],
                'zh' => [
                    'name' => 'Adheart',
                    'description' => "针对Facebook和Instagram的高级广告研究工具。监控竞争对手，寻找盈利广告，分析营销策略。"
                ]
            ]
        ];

        foreach ($services as $code => $translations) {
            $service = Service::create([
                'code' => $code,
                'amount' => 10,
                'trial_amount' => 1,
                'logo' => '/img/no-logo.png',
                'position' => 1
            ]);

            foreach ($translations as $locale => $data) {
                foreach ($data as $key => $value) {
                    ServiceTranslation::create([
                        'service_id' => $service->id,
                        'locale' => $locale,
                        'code' => $key,
                        'value' => $value,
                    ]);
                }
            }
        }
    }
}
