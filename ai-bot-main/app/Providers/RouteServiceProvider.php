<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            // API routes с опциональным доменом (для локальной разработки)
            $apiDomain = env('API_URL');
            
            if ($apiDomain && $apiDomain !== 'localhost' && !str_contains($apiDomain, '127.0.0.1')) {
                Route::middleware('api')
                    ->domain($apiDomain)
                    ->group(base_path('routes/api.php'));
            } else {
                // Для локальной разработки без домена
            Route::middleware('api')
                    ->prefix('api')
                ->group(base_path('routes/api.php'));
            }

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
