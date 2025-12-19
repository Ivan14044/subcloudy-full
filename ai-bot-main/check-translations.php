<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Page;

$pages = Page::whereIn('slug', ['about', 'contacts', 'privacy-policy', 'terms-of-service', 'faq'])->get();

echo "=== Checking Page Translations ===\n\n";

foreach ($pages as $page) {
    echo "Page: {$page->slug} ({$page->name})\n";
    echo "Active: " . ($page->is_active ? 'Yes' : 'No') . "\n";
    
    $translations = $page->translations()->get();
    $locales = $translations->pluck('locale')->unique();
    
    echo "Locales in DB: " . $locales->implode(', ') . "\n";
    echo "Total translations: " . $translations->count() . "\n\n";
    
    foreach (['ru', 'en', 'uk', 'es', 'zh'] as $locale) {
        $localeTranslations = $translations->where('locale', $locale);
        $hasTitle = $localeTranslations->where('code', 'title')->isNotEmpty();
        $hasContent = $localeTranslations->where('code', 'content')->isNotEmpty();
        
        echo "  {$locale}: ";
        if ($hasTitle && $hasContent) {
            $title = $localeTranslations->where('code', 'title')->first();
            echo "OK - Title: " . substr($title->value ?? '', 0, 30) . "...\n";
        } else {
            echo "MISSING - Title: " . ($hasTitle ? 'OK' : 'NO') . ", Content: " . ($hasContent ? 'OK' : 'NO') . "\n";
        }
    }
    
    echo "\n";
}

echo "\n=== Testing API Response ===\n";
$controller = new \App\Http\Controllers\PageController();
$response = $controller->index();
$data = json_decode($response->getContent(), true);

foreach (['about', 'contacts', 'privacy-policy', 'terms-of-service', 'faq'] as $slug) {
    if (isset($data[$slug])) {
        echo "\n{$slug}:\n";
        foreach (['ru', 'en', 'uk', 'es', 'zh'] as $locale) {
            if (isset($data[$slug][$locale])) {
                $hasTitle = isset($data[$slug][$locale]['title']);
                $hasContent = isset($data[$slug][$locale]['content']);
                echo "  {$locale}: " . ($hasTitle && $hasContent ? 'OK' : 'INCOMPLETE') . "\n";
            } else {
                echo "  {$locale}: MISSING\n";
            }
        }
    } else {
        echo "\n{$slug}: NOT FOUND IN API\n";
    }
}

