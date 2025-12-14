<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Обновляем название пункта меню для истории активности
        $menu = Config::get('adminlte.menu', []);
        foreach ($menu as $idx => $item) {
            if (is_array($item) && ($item['url'] ?? null) === 'browser-sessions') {
                $item['text'] = 'История активности';
                $menu[$idx] = $item;
                break;
            }
        }

        Config::set('adminlte.menu', $menu);
    }
}
