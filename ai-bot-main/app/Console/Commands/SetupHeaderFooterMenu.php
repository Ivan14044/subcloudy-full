<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Page;
use App\Models\Option;

class SetupHeaderFooterMenu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menu:setup-header-footer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup header and footer menu with links to standard pages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up header and footer menus...');

        $languages = config('langs', []);
        
        // Получаем страницы из базы данных
        $pages = Page::where('is_active', true)
            ->whereIn('slug', ['about', 'contacts', 'privacy-policy', 'terms-of-service'])
            ->get()
            ->keyBy('slug');

        if ($pages->isEmpty()) {
            $this->error('No standard pages found. Please run pages:create-standard first.');
            return Command::FAILURE;
        }

        // Определяем структуру меню
        $headerPages = ['about', 'contacts'];
        $footerPages = ['about', 'contacts', 'faq', 'privacy-policy', 'terms-of-service'];

        // Формируем меню для хедера
        $headerMenu = $this->buildMenu($headerPages, $pages, $languages);
        
        // Формируем меню для футера
        $footerMenu = $this->buildMenu($footerPages, $pages, $languages);

        // Сохраняем меню в базу данных
        Option::set('header_menu', json_encode($headerMenu));
        Option::set('footer_menu', json_encode($footerMenu));

        $this->info('Header menu configured with ' . count($headerPages) . ' items');
        $this->info('Footer menu configured with ' . count($footerPages) . ' items');
        $this->info('Menus saved successfully!');

        return Command::SUCCESS;
    }

    /**
     * Build menu structure for given pages
     *
     * @param array $pageSlugs
     * @param \Illuminate\Support\Collection $pages
     * @param array $languages
     * @return array
     */
    private function buildMenu(array $pageSlugs, $pages, array $languages): array
    {
        $menu = [];

        foreach ($languages as $lang => $flag) {
            $menu[$lang] = [];

            foreach ($pageSlugs as $slug) {
                $page = $pages->get($slug);
                
                if (!$page) {
                    continue;
                }

                // Получаем переводы страницы
                $pageTranslations = $page->translations()
                    ->where('locale', $lang)
                    ->pluck('value', 'code')
                    ->toArray();

                // Получаем название из перевода title или используем name страницы
                $title = $pageTranslations['title'] ?? $page->name;

                $menu[$lang][] = [
                    'title' => $title,
                    'link' => '/' . $slug,
                    'is_blank' => false,
                ];
            }

            // Кодируем массив в JSON строку для каждого языка
            $menu[$lang] = json_encode($menu[$lang]);
        }

        return $menu;
    }
}

