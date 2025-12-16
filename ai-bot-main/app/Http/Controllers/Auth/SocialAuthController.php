<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Перенаправление на страницу авторизации Google
     */
    public function redirectToGoogle(Request $request)
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Перенаправление на страницу авторизации Google с принудительным выбором аккаунта
     */
    public function redirectToGoogleWithPrompt()
    {
        Log::info('Google OAuth redirect with prompt', [
            'redirect_uri' => config('services.google.redirect'),
            'client_id' => config('services.google.client_id')
        ]);

        // Используем метод with() для передачи параметра prompt (как рекомендует документация)
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Получение информации о пользователя из Google
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            Log::info('Google callback begin', [
                'from_desktop' => $request->has('from_desktop')
            ]);
            
            $googleUser = Socialite::driver('google')->user();

            Log::info('Google user received', [
                'id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName()
            ]);

            // Шаг 1: ищем пользователя по email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Шаг 2: обновляем google_id, если он еще не сохранён
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'provider' => 'google',
                        'avatar' => $googleUser->getAvatar(),
                    ]);
                }
            } else {
                // Шаг 3: создаем нового пользователя
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'provider' => 'google',
                    'avatar' => $googleUser->getAvatar(),
                    'password' => Hash::make(\Illuminate\Support\Str::random(12)),
                ]);
            }

            // Проверка на блокировку
            if ($user->is_blocked) {
                Log::warning('User blocked', ['id' => $user->id]);
                
                // Для desktop возвращаем JSON
                if ($request->has('from_desktop')) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Ваш аккаунт заблокирован'
                    ]);
                }
                
                return view('auth.callback', [
                    'success' => false,
                    'error' => 'Ваш аккаунт заблокирован',
                ]);
            }

            // Авторизация
            Auth::login($user);

            // Токен для API
            $token = $user->createToken('auth_token')->plainTextToken;
            $user->active_services = $user->activeServices();
            $userData = $user->only(['id', 'name', 'email', 'avatar']);
            $userData['active_services'] = $user->active_services;

            // Для desktop приложения возвращаем JSON
            if ($request->has('from_desktop')) {
                Log::info('Returning JSON for desktop app');
                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'user' => $userData
                ]);
            }

            // Для web возвращаем HTML с postMessage
            return view('auth.callback', [
                'success' => true,
                'token' => $token,
                'user' => $userData,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in Google callback', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Для desktop возвращаем JSON
            if ($request->has('from_desktop')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Ошибка авторизации: ' . $e->getMessage()
                ]);
            }

            return view('auth.callback', [
                'success' => false,
                'error' => 'Ошибка авторизации: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Обработка авторизации через Telegram
     */
    public function handleTelegramCallback(Request $request)
    {
        try {
            $telegramData = $request->all();

            Log::info('Telegram callback', [
                'from_desktop' => $request->has('from_desktop'),
                'has_id' => isset($telegramData['id']),
                'data_keys' => array_keys($telegramData),
                'has_hash' => isset($telegramData['hash']),
                'has_auth_date' => isset($telegramData['auth_date'])
            ]);

            $validationResult = $this->validateTelegramData($telegramData);
            Log::info('Telegram validation result', ['valid' => $validationResult]);

            if (!$validationResult) {
                Log::warning('Telegram data validation failed', [
                    'data_keys' => array_keys($telegramData),
                    'has_id' => isset($telegramData['id']),
                    'has_hash' => isset($telegramData['hash']),
                    'has_auth_date' => isset($telegramData['auth_date'])
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid Telegram data'
                ], 401);
            }

            Log::info('Telegram validation passed, processing user');

            $user = User::where('telegram_id', $telegramData['id'])->first();

            if (!$user) {
                $existingUser = null;
                if (!empty($telegramData['email'])) {
                    $existingUser = User::where('email', $telegramData['email'])->first();
                }

                if ($existingUser) {
                    $existingUser->telegram_id = $telegramData['id'];
                    $existingUser->telegram_username = $telegramData['username'] ?? null;
                    $existingUser->provider = 'telegram';
                    $existingUser->save();
                    $user = $existingUser;
                } else {
                    $user = User::create([
                        'name' => $telegramData['first_name'] . ' ' . ($telegramData['last_name'] ?? ''),
                        'email' => $telegramData['email'] ?? $telegramData['id'] . '@telegram.org',
                        'telegram_id' => $telegramData['id'],
                        'telegram_username' => $telegramData['username'] ?? null,
                        'avatar' => $telegramData['photo_url'] ?? null,
                        'provider' => 'telegram',
                        'password' => Hash::make(rand(100000, 999999)),
                    ]);
                }
            } else {
                $user->telegram_username = $telegramData['username'] ?? $user->telegram_username;
                $user->avatar = $telegramData['photo_url'] ?? $user->avatar;
                $user->save();
            }

            if ($user->is_blocked) {
                return response()->json([
                    'success' => false,
                    'error' => 'Account blocked'
                ], 403);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            $user->active_services = $user->activeServices();
            $userData = $user->only(['id', 'name', 'email', 'avatar', 'telegram_username']);
            $userData['active_services'] = $user->active_services;

            Log::info('Telegram auth successful', [
                'user_id' => $user->id,
                'has_token' => !empty($token)
            ]);

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $userData
            ]);
        } catch (\Exception $e) {
            Log::error('Telegram callback error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Проверка данных от Telegram
     */
    private function validateTelegramData($data)
    {
        // Если отсутствуют обязательные поля, данные недействительны
        if (!isset($data['id']) || !isset($data['auth_date']) || !isset($data['hash'])) {
            Log::warning('Telegram validation: missing required fields', [
                'has_id' => isset($data['id']),
                'has_auth_date' => isset($data['auth_date']),
                'has_hash' => isset($data['hash'])
            ]);
            return false;
        }

        // Проверяем, что авторизация не устарела (не более 24 часов)
        $authAge = time() - $data['auth_date'];
        if ($authAge > 86400) {
            Log::warning('Telegram validation: auth expired', [
                'auth_date' => $data['auth_date'],
                'current_time' => time(),
                'age_seconds' => $authAge
            ]);
            return false;
        }

        // Получаем секретный ключ для проверки
        $botToken = config('services.telegram.bot_token');
        if (empty($botToken)) {
            Log::error('Telegram validation: bot_token is empty in config');
            return false;
        }
        $secretKey = hash('sha256', $botToken, true);

        // Готовим данные для проверки хэша (копируем и удаляем хэш)
        $checkData = $data;
        $checkHash = $checkData['hash'];
        unset($checkData['hash']);

        // Сортируем массив по ключам и формируем строку для хэша
        ksort($checkData);
        $dataCheckString = '';
        foreach ($checkData as $key => $value) {
            $dataCheckString .= $key . '=' . $value . "\n";
        }
        $dataCheckString = rtrim($dataCheckString, "\n");

        // Вычисляем хэш и сравниваем с полученным от Telegram
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);
        $isValid = hash_equals($hash, $checkHash);

        if (!$isValid) {
            Log::warning('Telegram validation: hash mismatch', [
                'calculated_hash' => substr($hash, 0, 16) . '...',
                'received_hash' => substr($checkHash, 0, 16) . '...',
                'data_check_string_length' => strlen($dataCheckString)
            ]);
        }

        return $isValid;
    }
}
