<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\OptionController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\CookieConsentController;
use App\Http\Controllers\Api\CryptomusController;
use App\Http\Controllers\Api\MonoController;
use App\Http\Controllers\Api\BrowserController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SavingsBlockController;
use App\Http\Controllers\Api\ContentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ExtensionController;
use App\Http\Controllers\Api\PromocodeController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\TelegramController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
Route::post('/user', [AuthController::class, 'update'])->middleware('auth:sanctum');

Route::get('/notifications', [NotificationController::class, 'index'])->middleware('auth:sanctum');
Route::post('/notifications/read', [NotificationController::class, 'markNotificationsAsRead'])
    ->middleware('auth:sanctum');

Route::get('/services', [ServiceController::class, 'index']);

Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{article}', [ArticleController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);

Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/savings-blocks', [SavingsBlockController::class, 'index']);
Route::get('/content/{code}', [ContentController::class, 'getByCode']);

Route::get('/pages', [PageController::class, 'index']);
Route::get('/options', [OptionController::class, 'index']);
Route::post('/cart', [CartController::class, 'store'])->middleware('auth:sanctum');

Route::post('/toggle-auto-renew', [SubscriptionController::class, 'toggleAutoRenew'])->middleware('auth:sanctum');
Route::post('/cancel-subscription', [SubscriptionController::class, 'cancelSubscription'])->middleware('auth:sanctum');

Route::get('/cookie/check', [CookieConsentController::class, 'check']);

Route::post('/cryptomus/create-payment', [CryptomusController::class, 'createPayment'])->middleware('auth:sanctum');
Route::post('/cryptomus/webhook', [CryptomusController::class, 'webhook']); //->middleware('cryptomus');
Route::post('/mono/create-payment', [MonoController::class, 'createPayment'])->middleware('auth:sanctum');
Route::post('/mono/webhook', [MonoController::class, 'webhook']); //->middleware('cryptomus');


Route::get('/browser/new', [BrowserController::class, 'new']);

Route::post('/browser/stop', [BrowserController::class, 'stop']);

Route::post('/browser/stop_all', [BrowserController::class, 'stopAll']);

Route::get('/browser/list', [BrowserController::class, 'getList']);

Route::middleware('ext.auth')->group(function () {
    Route::post('/extension/settings', [ExtensionController::class, 'saveSettings']);
    Route::get('/extension/auth', [ExtensionController::class, 'authStatus']);
});

Route::post('/promocodes/validate', [PromocodeController::class, 'validateCode']);

// Support Routes (работают как для авторизованных, так и для неавторизованных пользователей)
Route::prefix('support')->group(function () {
    // Получение или создание тикета - 60 запросов в минуту
    Route::match(['get', 'post'], '/ticket', [SupportController::class, 'getOrCreateTicket'])
        ->middleware('throttle:60,1')
        ->name('api.support.ticket');
    
    // Получение тикета - без ограничений (нужен для polling)
    Route::get('/ticket/{id}', [SupportController::class, 'getTicket'])->name('api.support.get-ticket');
    
    // Отправка сообщения - 60 сообщений в минуту
    Route::post('/ticket/{id}/message', [SupportController::class, 'sendMessage'])
        ->middleware('throttle:60,1')
        ->name('api.support.send-message');
    
    // Получение новых сообщений - без ограничений (используется для polling)
    Route::get('/ticket/{id}/messages', [SupportController::class, 'getNewMessages'])->name('api.support.new-messages');
    
    // Telegram ссылка - 30 запросов в минуту
    Route::get('/ticket/{id}/telegram-link', [SupportController::class, 'getTelegramLink'])
        ->middleware('throttle:30,1')
        ->name('api.support.telegram-link');
    
    // Telegram webhook - без ограничений (внешний сервис)
    Route::post('/telegram/webhook', [TelegramController::class, 'handle'])->name('api.support.telegram-webhook');
});

// Desktop Application Routes
Route::middleware('auth:sanctum')->prefix('desktop')->group(function () {
    Route::post('/auth', [\App\Http\Controllers\Api\DesktopController::class, 'auth']);
    Route::post('/service-url', [\App\Http\Controllers\Api\DesktopController::class, 'getSecureServiceUrl']);
    Route::get('/my-services', [\App\Http\Controllers\Api\DesktopController::class, 'myServices']);
    Route::post('/log', [\App\Http\Controllers\Api\DesktopController::class, 'logActivity']);
});

// Download routes (без авторизации, так как файлы могут быть публичными)
Route::get('/desktop/download/{os}', [\App\Http\Controllers\Api\DesktopController::class, 'download'])
    ->where('os', 'windows|macos|linux');
