<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

class CreateLegalPages extends Command
{
    protected $signature = 'pages:create-legal';
    protected $description = 'Создать юридические страницы (Публичная оферта, Политика конфиденциальности, Политика возврата)';

    public function handle()
    {
        $this->info('Создание юридических страниц...');

        $pages = [
            [
                'slug' => 'terms-of-service',
                'name' => 'Публичная оферта',
                'is_active' => true,
                'translations' => [
                    'ru' => [
                        'title' => 'Публичная оферта',
                        'content' => '<h2>Публичная оферта</h2><p>Здесь будет размещен текст публичной оферты.</p><p>Пожалуйста, заполните содержимое страницы через админ-панель.</p>'
                    ],
                    'en' => [
                        'title' => 'Terms of Service',
                        'content' => '<h2>Terms of Service</h2><p>Terms of Service content will be placed here.</p><p>Please fill in the page content through the admin panel.</p>'
                    ],
                    'uk' => [
                        'title' => 'Публічна оферта',
                        'content' => '<h2>Публічна оферта</h2><p>Тут буде розміщено текст публічної оферти.</p><p>Будь ласка, заповніть вміст сторінки через адмін-панель.</p>'
                    ],
                    'es' => [
                        'title' => 'Términos de servicio',
                        'content' => '<h2>Términos de servicio</h2><p>Aquí se colocará el contenido de los términos de servicio.</p><p>Por favor, complete el contenido de la página a través del panel de administración.</p>'
                    ],
                    'zh' => [
                        'title' => '服务条款',
                        'content' => '<h2>服务条款</h2><p>服务条款内容将放在这里。</p><p>请通过管理面板填写页面内容。</p>'
                    ],
                ]
            ],
            [
                'slug' => 'privacy-policy',
                'name' => 'Политика конфиденциальности',
                'is_active' => true,
                'translations' => [
                    'ru' => [
                        'title' => 'Политика конфиденциальности',
                        'content' => '<h2>Политика конфиденциальности</h2><p>Здесь будет размещена политика конфиденциальности.</p><p>Пожалуйста, заполните содержимое страницы через админ-панель.</p>'
                    ],
                    'en' => [
                        'title' => 'Privacy Policy',
                        'content' => '<h2>Privacy Policy</h2><p>Privacy Policy content will be placed here.</p><p>Please fill in the page content through the admin panel.</p>'
                    ],
                    'uk' => [
                        'title' => 'Політика конфіденційності',
                        'content' => '<h2>Політика конфіденційності</h2><p>Тут буде розміщено політику конфіденційності.</p><p>Будь ласка, заповніть вміст сторінки через адмін-панель.</p>'
                    ],
                    'es' => [
                        'title' => 'Política de privacidad',
                        'content' => '<h2>Política de privacidad</h2><p>Aquí se colocará el contenido de la política de privacidad.</p><p>Por favor, complete el contenido de la página a través del panel de administración.</p>'
                    ],
                    'zh' => [
                        'title' => '隐私政策',
                        'content' => '<h2>隐私政策</h2><p>隐私政策内容将放在这里。</p><p>请通过管理面板填写页面内容。</p>'
                    ],
                ]
            ],
            [
                'slug' => 'refund-policy',
                'name' => 'Политика возврата',
                'is_active' => true,
                'translations' => [
                    'ru' => [
                        'title' => 'Политика возврата',
                        'content' => '<h2>Политика возврата</h2><p>Здесь будет размещена политика возврата средств.</p><p>Пожалуйста, заполните содержимое страницы через админ-панель.</p>'
                    ],
                    'en' => [
                        'title' => 'Refund Policy',
                        'content' => '<h2>Refund Policy</h2><p>Refund Policy content will be placed here.</p><p>Please fill in the page content through the admin panel.</p>'
                    ],
                    'uk' => [
                        'title' => 'Політика повернення',
                        'content' => '<h2>Політика повернення</h2><p>Тут буде розміщено політику повернення коштів.</p><p>Будь ласка, заповніть вміст сторінки через адмін-панель.</p>'
                    ],
                    'es' => [
                        'title' => 'Política de reembolso',
                        'content' => '<h2>Política de reembolso</h2><p>Aquí se colocará el contenido de la política de reembolso.</p><p>Por favor, complete el contenido de la página a través del panel de administración.</p>'
                    ],
                    'zh' => [
                        'title' => '退款政策',
                        'content' => '<h2>退款政策</h2><p>退款政策内容将放在这里。</p><p>请通过管理面板填写页面内容。</p>'
                    ],
                ]
            ],
        ];

        foreach ($pages as $pageData) {
            $existing = Page::where('slug', $pageData['slug'])->first();
            
            if ($existing) {
                $this->warn("Страница '{$pageData['slug']}' уже существует. Пропускаю...");
                continue;
            }

            $page = Page::create([
                'name' => $pageData['name'],
                'slug' => $pageData['slug'],
                'is_active' => $pageData['is_active'],
            ]);

            $translationData = [];
            foreach ($pageData['translations'] as $locale => $trans) {
                foreach (['title', 'content'] as $field) {
                    if (isset($trans[$field])) {
                        $translationData[$field][$locale] = $trans[$field];
                    }
                }
            }
            
            $page->saveTranslation($translationData);
            
            $this->info("Создана страница: {$pageData['name']} (slug: {$pageData['slug']})");
        }

        $this->info('Юридические страницы успешно созданы!');
        return 0;
    }
}

