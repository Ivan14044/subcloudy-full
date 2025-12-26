<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Content;
use App\Models\Service;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $locales = ['ru', 'uk', 'en', 'es', 'zh'];
        $appUrl = config('app.url');

        $urls = [];

        // Home
        $urls[] = [
            'loc' => $appUrl . '/',
            'priority' => '1.0',
            'changefreq' => 'daily',
            'alternates' => $this->getAlternates($appUrl . '/', $locales)
        ];

        // Articles list
        $urls[] = [
            'loc' => $appUrl . '/articles',
            'priority' => '0.8',
            'changefreq' => 'daily',
            'alternates' => $this->getAlternates($appUrl . '/articles', $locales)
        ];

        // Services
        $services = Service::where('is_active', 1)->get();
        foreach ($services as $service) {
            $urls[] = [
                'loc' => $appUrl . '/service/' . $service->id,
                'priority' => '0.9',
                'changefreq' => 'weekly',
                'alternates' => $this->getAlternates($appUrl . '/service/' . $service->id, $locales)
            ];
        }

        // Articles
        $articles = Article::where('status', 'published')->get();
        foreach ($articles as $article) {
            $urls[] = [
                'loc' => $appUrl . '/articles/' . $article->id,
                'priority' => '0.7',
                'changefreq' => 'weekly',
                'lastmod' => $article->updated_at ? $article->updated_at->toAtomString() : null,
                'alternates' => $this->getAlternates($appUrl . '/articles/' . $article->id, $locales)
            ];
        }

        // Categories
        $categories = Category::all();
        foreach ($categories as $category) {
            $urls[] = [
                'loc' => $appUrl . '/categories/' . $category->id,
                'priority' => '0.6',
                'changefreq' => 'weekly',
                'alternates' => $this->getAlternates($appUrl . '/categories/' . $category->id, $locales)
            ];
        }

        // Dynamic Pages (Content model)
        $pages = Content::where('is_systems', 0)->get();
        foreach ($pages as $page) {
            $urls[] = [
                'loc' => $appUrl . '/' . $page->code,
                'priority' => '0.5',
                'changefreq' => 'monthly',
                'alternates' => $this->getAlternates($appUrl . '/' . $page->code, $locales)
            ];
        }

        return response()->view('sitemap', compact('urls'))->header('Content-Type', 'text/xml');
    }

    private function getAlternates($path, $locales)
    {
        $baseUrl = config('app.url');
        $pathOnly = parse_url($path, PHP_URL_PATH) ?: '/';
        if ($pathOnly === '/') $pathOnly = '';
        
        $alternates = [];

        foreach ($locales as $locale) {
            $alternates[] = [
                'lang' => $locale,
                'href' => $baseUrl . '/' . $locale . $pathOnly
            ];
        }

        return $alternates;
    }
}

