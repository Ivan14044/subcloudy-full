<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\NotificationTemplateService;
use App\Services\NotifierService;

class AuthService
{
    /**
     * Регистрация нового пользователя
     */
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'lang' => $data['lang'],
            'password' => Hash::make($data['password']),
        ]);

        app(NotificationTemplateService::class)->sendToUser($user, 'registration');

        NotifierService::send(
            'registration',
            __('notifier.new_user_title'),
            __('notifier.new_user_message', [
                'email' => $user->email,
                'name' => $user->name,
            ])
        );

        return $user;
    }

    /**
     * Создание токенов для пользователя
     */
    public function createTokens(User $user): array
    {
        return [
            'spa' => $user->createToken('auth_token')->plainTextToken,
            'extension' => $user->createToken('extension', ['extension'])->plainTextToken,
        ];
    }

    /**
     * Обновление данных пользователя
     */
    public function update(User $user, array $data): User
    {
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['email'])) {
            $user->email = $data['email'];
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if (isset($data['lang'])) {
            $user->lang = $data['lang'];
        }

        if (isset($data['browser_session_pid'])) {
            $user->session_pid = $data['browser_session_pid'] ?: null;
        }

        if (isset($data['keyboardLanguages'])) {
            $currentSettings = $user->extension_settings ?? [];
            $currentSettings['keyboardLanguages'] = array_values(array_unique($data['keyboardLanguages']));
            $user->extension_settings = $currentSettings;
        }

        $user->save();

        return $user;
    }
}

