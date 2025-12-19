<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Page;
use App\Models\Article;
use App\Models\Service;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate sitemap.xml for SEO';

    public function handle()
    {
        $this->info('Generating sitemap.xml...');

        $languages = array_keys(config('langs', ['ru' => '🇷🇺', 'en' => '🇬🇧', 'uk' => '🇺🇦', 'es' => '🇪🇸', 'zh' => '🇨🇳']));
        $baseUrl = config('app.url', 'https://subcloudy.com');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n\n";

        // Главная страница
        $xml .= $this->generateUrl($baseUrl . '/', '1.0', 'daily', $languages);

        // Страницы
        $pages = Page::where('is_active', true)->get();
        foreach ($pages as $page) {
            $slug = $page->slug;
            if ($slug) {
                $xml .= $this->generateUrl($baseUrl . '/' . $slug, '0.8', 'weekly', $languages, $slug);
            }
        }

        // Статьи
        $articles = Article::all();
        foreach ($articles as $article) {
            $xml .= $this->generateUrl($baseUrl . '/articles/' . $article->id, '0.7', 'weekly', $languages);
        }

        // Сервисы
        $services = Service::all();
        foreach ($services as $service) {
            $xml .= $this->generateUrl($baseUrl . '/services/' . $service->id, '0.8', 'monthly', $languages);
        }

        // Дополнительные страницы
        $additionalPages = ['/services', '/articles', '/about', '/contacts'];
        foreach ($additionalPages as $page) {
            $xml .= $this->generateUrl($baseUrl . $page, '0.6', 'weekly', $languages);
        }

        $xml .= '</urlset>';

        $path = public_path('sitemap.xml');
        File::put($path, $xml);

        $this->info('Sitemap generated successfully: ' . $path);
        return 0;
    }

    private function generateUrl($url, $priority, $changefreq, $languages, $slug = null)
    {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($url) . "</loc>\n";
        $xml .= "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
        $xml .= "    <changefreq>" . $changefreq . "</changefreq>\n";
        $xml .= "    <priority>" . $priority . "</priority>\n";

        // Добавляем hreflang для мультиязычности
        foreach ($languages as $lang) {
            $langUrl = $slug 
                ? config('app.url') . '/' . $lang . '/' . $slug
                : config('app.url') . '/?lang=' . $lang;
            $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"" . $lang . "\" href=\"" . htmlspecialchars($langUrl) . "\"/>\n";
        }

        $xml .= "  </url>\n\n";
        return $xml;
    }
}

