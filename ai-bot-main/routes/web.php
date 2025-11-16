<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProxyController;
use App\Http\Controllers\Admin\PromocodeController;
use App\Http\Controllers\Admin\PromocodeUsageController;
use App\Http\Controllers\Admin\ServiceAccountController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\NotificationTemplateController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\BrowserSessionController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('/')
    ->name('admin.')
    ->group(function () {
        Route::middleware('guest')->group(function () {
            Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
            Route::post('/login', [LoginController::class, 'login']);
        });

        Route::middleware('admin.auth')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

            Route::resource('users', UserController::class)->except(['show']);
            Route::get('users/bulk-insert', [UserController::class, 'bulkInsert'])->name('users.bulk-insert');
            Route::post('users/bulk-insert', [UserController::class, 'bulkInsertStore'])->name('users.bulk-insert.store');
            Route::get('/admin/users/bulk-download', [UserController::class, 'downloadBulkUsers'])
                ->name('users.bulk-download');
            Route::post('users/{user}/block', [UserController::class, 'block'])->name('users.block');
            Route::get('users/{user}/subscriptions', [UserController::class, 'subscriptions'])->name('users.subscriptions');

            Route::resource('subscriptions', SubscriptionController::class)->except(['show']);
            Route::get('subscriptions/{subscription}/transactions', [SubscriptionController::class, 'transactions'])
                ->name('subscriptions.transactions');
            Route::put('subscriptions/{subscription}/update-next-payment', [SubscriptionController::class, 'updateNextPayment'])
                ->name('subscriptions.update-next-payment');
            Route::put('/admin/subscriptions/{subscription}/toggle-status', [SubscriptionController::class, 'toggleStatus'])
                ->name('subscriptions.toggle-status');
            Route::get('/subscriptions/extend', [SubscriptionController::class, 'extendForm'])->name('subscriptions.extend');
            Route::post('/subscriptions/extend', [SubscriptionController::class, 'extendMany']);

            Route::resource('proxies', ProxyController::class)->except(['show']);
            Route::resource('promocodes', PromocodeController::class)->except(['show']);
            Route::get('promocode-usages', [PromocodeUsageController::class, 'index'])->name('promocode-usages.index');
            Route::delete('promocodes-bulk', [PromocodeController::class, 'bulkDestroy'])->name('promocodes.bulk-destroy');
            Route::resource('services', ServiceController::class)->except(['show']);
            Route::resource('pages', PageController::class)->except(['show']);
            Route::resource('notification-templates', NotificationTemplateController::class)->only(['index', 'edit', 'update', 'destroy']);
            Route::resource('notifications', NotificationController::class)->only(['index', 'create', 'store', 'destroy']);
            Route::resource('contents', ContentController::class)->except(['show']);
            Route::resource('articles', ArticleController::class)->except(['show']);
            Route::resource('categories', CategoryController::class)->except(['show']);
            Route::resource('email-templates', EmailTemplateController::class)->except(['create', 'store']);
            Route::resource('settings', SettingController::class)->only(['index', 'store']);
            Route::resource('service-accounts', ServiceAccountController::class)->except(['show']);

            // Browser sessions management
            Route::get('browser-sessions', [BrowserSessionController::class, 'index'])->name('browser-sessions.index');
            Route::get('browser-sessions/data', [BrowserSessionController::class, 'data'])->name('browser-sessions.data');
            Route::post('browser-sessions/start', [BrowserSessionController::class, 'start'])->name('browser-sessions.start');
            Route::post('browser-sessions/start-json', [BrowserSessionController::class, 'startJson'])->name('browser-sessions.start-json');
            Route::post('browser-sessions/stop-pid', [BrowserSessionController::class, 'stopByPid'])->name('browser-sessions.stop-pid');
            Route::post('browser-sessions/stop-port', [BrowserSessionController::class, 'stopByPort'])->name('browser-sessions.stop-port');
            Route::post('browser-sessions/stop-all', [BrowserSessionController::class, 'stopAll'])->name('browser-sessions.stop-all');

            // Admin notifications
            Route::get('admin_notifications/get', [AdminNotificationController::class, 'get'])->name('admin_notifications.get');
            Route::get('admin_notifications/read/{id}', [AdminNotificationController::class, 'read'])->name('admin_notifications.read');
            Route::post('admin_notifications/read-all', [AdminNotificationController::class, 'readAll'])->name('admin_notifications.read-all');
            Route::resource('admin_notifications', AdminNotificationController::class)
                ->only(['index', 'destroy'])
                ->parameters(['admin_notifications' => 'id']);

            Route::middleware(['admin.main'])->group(function () {
                Route::resource('admins', AdminController::class)->except(['show']);
                Route::post('admins/{admin}/block', [AdminController::class, 'block'])->name('admins.block');
            });

            Route::resource('profile', ProfileController::class)->only(['index', 'store']);
            Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
        });
    });

Route::prefix('auth')->group(function () {
    // Google OAuth
    Route::get('/google', [SocialAuthController::class, 'redirectToGoogle']);
    Route::get('/google/reauth', [SocialAuthController::class, 'redirectToGoogleWithPrompt']);
    Route::get('/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

    // Telegram авторизация
    Route::match(['get', 'post'], '/telegram/callback', [SocialAuthController::class, 'handleTelegramCallback']);
});
