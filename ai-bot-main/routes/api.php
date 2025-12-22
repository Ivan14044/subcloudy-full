<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\CryptomusController;
use App\Http\Controllers\MonoController;
use App\Http\Controllers\Api\BrowserController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SavingsBlockController;
use App\Http\Controllers\Api\ContentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Controllers\ExtensionController;
use App\Http\Controllers\Api\PromocodeController;
use App\Http\Controllers\Api\SupportController;

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

// Support Routes (авторизованные пользователи)
Route::middleware('auth:sanctum')->prefix('support')->group(function () {
    Route::get('/ticket', [SupportController::class, 'getOrCreateTicket']);
    Route::get('/ticket/{id}', [SupportController::class, 'getTicket']);
    Route::post('/ticket/{id}/message', [SupportController::class, 'sendMessage']);
    Route::get('/ticket/{id}/messages', [SupportController::class, 'getNewMessages']);
    Route::get('/ticket/{id}/telegram-link', [SupportController::class, 'getTelegramLink']);
});

// Support Routes для неавторизованных пользователей (с email)
Route::prefix('support')->group(function () {
    Route::post('/ticket', [SupportController::class, 'getOrCreateTicket']);
    Route::get('/ticket/{id}', [SupportController::class, 'getTicket']);
    Route::post('/ticket/{id}/message', [SupportController::class, 'sendMessage']);
    Route::get('/ticket/{id}/messages', [SupportController::class, 'getNewMessages']);
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
