<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Page;
use Illuminate\Support\Str;

class CreateStandardPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pages:create-standard {--force : Update existing pages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update standard pages (About, Contacts, Privacy Policy, Terms of Service, FAQ)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating standard pages...');

        $pagesData = require database_path('data/standard_pages_content.php');
        $languages = config('langs', []);

        $created = 0;
        $updated = 0;
        $skipped = 0;

        $force = $this->option('force');

        foreach ($pagesData as $key => $pageData) {
            // Проверяем, существует ли страница с таким slug
            $existingPage = Page::where('slug', $pageData['slug'])->first();

            if ($existingPage) {
                if ($force) {
                    // Обновляем существующую страницу
                    $page = $existingPage;
                    $page->update([
                        'name' => $pageData['name'],
                        'is_active' => true,
                    ]);
                    $this->info("Updating page: {$pageData['name']} ({$pageData['slug']})");
                    $updated++;
                } else {
                    $this->warn("Page with slug '{$pageData['slug']}' already exists. Skipping... (use --force to update)");
                    $skipped++;
                    continue;
                }
            } else {
                // Создаем новую страницу
                $page = Page::create([
                    'name' => $pageData['name'],
                    'slug' => $pageData['slug'],
                    'is_active' => true,
                    'is_system' => false,
                ]);
                $this->info("Created page: {$pageData['name']} ({$pageData['slug']})");
                $created++;
            }

            // Сохраняем переводы
            $translations = [];
            foreach ($languages as $lang => $flag) {
                if (isset($pageData['translations'][$lang])) {
                    $langData = $pageData['translations'][$lang];
                    
                    foreach (Page::TRANSLATION_FIELDS as $field) {
                        if (isset($langData[$field])) {
                            $translations[$field][$lang] = $langData[$field];
                        }
                    }
                }
            }

            // Используем метод saveTranslation из трейта HasTranslations
            $page->saveTranslation($translations);
        }

        $this->info("\nDone! Created: {$created}, Updated: {$updated}, Skipped: {$skipped}");

        return Command::SUCCESS;
    }
}

