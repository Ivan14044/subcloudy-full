<?php

namespace App\Services;

use App\Models\User;
use App\Models\Option;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Throwable;

class EmailService
{
    /**
     * Отправляет email письмо пользователю по шаблону
     *
     * @param string $templateCode Код шаблона письма
     * @param int $userId ID пользователя-получателя
     * @param array $params Параметры для подстановки в шаблон (ключ => значение)
     * @return bool true при успешной отправке, false при ошибке
     * @throws \Exception При отсутствии шаблона или неполных SMTP настройках
     */
    public static function send(string $templateCode, int $userId, array $params = []): bool
    {
        $startTime = microtime(true);
        $user = null;
        $locale = null;
        $subject = null;
        
        try {
            $user = User::findOrFail($userId);
            $locale = $user->lang ?? 'en';

            App::setLocale($locale);

            $translation = self::getTemplateTranslation($templateCode, $locale);

            if (!$translation || !isset($translation['title'], $translation['message'])) {
                throw new \Exception("Email template translation missing title or message.");
            }

            self::configureMailFromOptions();

            $subject = self::renderTemplate($translation['title'], $params);
            $body = self::renderTemplate($translation['message'], $params);

            Mail::send('emails.base', [
                'subject' => $subject,
                'body' => $body,
            ], function ($message) use ($user, $subject) {
                $message->to($user->email)->subject($subject);
            });

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::channel('mail')->info('Email sent successfully', [
                'template' => $templateCode,
                'user_id' => $userId,
                'email' => $user->email,
                'locale' => $locale,
                'subject' => $subject,
                'execution_time_ms' => $executionTime,
            ]);

            return true;
        } catch (Throwable $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::channel('mail')->error('Email send failed', [
                'template' => $templateCode,
                'user_id' => $userId,
                'email' => $user->email ?? null,
                'locale' => $locale ?? null,
                'subject' => $subject ?? null,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'execution_time_ms' => $executionTime,
            ]);
            
            report($e);
            return false;
        }
    }

    /**
     * Отправляет email письмо на указанный адрес по шаблону
     *
     * @param string $templateCode Код шаблона письма
     * @param string $email Email получателя
     * @param array $params Параметры для подстановки в шаблон (ключ => значение)
     * @param string $locale Локаль для письма (по умолчанию 'en')
     * @return bool true при успешной отправке, false при ошибке
     */
    public static function sendToEmail(string $templateCode, string $email, array $params = [], string $locale = 'en'): bool
    {
        $startTime = microtime(true);
        $subject = null;
        
        try {
            App::setLocale($locale);

            $translation = self::getTemplateTranslation($templateCode, $locale);

            if (!$translation || !isset($translation['title'], $translation['message'])) {
                throw new \Exception("Email template translation missing title or message for locale: {$locale}");
            }

            self::configureMailFromOptions();

            $subject = self::renderTemplate($translation['title'], $params);
            $body = self::renderTemplate($translation['message'], $params);

            Mail::send('emails.base', [
                'subject' => $subject,
                'body' => $body,
            ], function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::channel('mail')->info('Email sent successfully to custom email', [
                'template' => $templateCode,
                'email' => $email,
                'locale' => $locale,
                'subject' => $subject,
                'execution_time_ms' => $executionTime,
            ]);

            return true;
        } catch (Throwable $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::channel('mail')->error('Email send to custom email failed', [
                'template' => $templateCode,
                'email' => $email,
                'locale' => $locale,
                'subject' => $subject ?? null,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'execution_time_ms' => $executionTime,
            ]);
            
            report($e);
            return false;
        }
    }

    /**
     * Получает перевод шаблона письма для указанной локали
     *
     * @param string $code Код шаблона
     * @param string $locale Локаль (например, 'ru', 'en')
     * @return array|null Массив с ключами 'title' и 'message', или null если шаблон не найден
     */
    public static function getTemplateTranslation(string $code, string $locale): ?array
    {
        $template = EmailTemplate::where('code', $code)
            ->with(['translations' => fn($q) => $q->where('locale', $locale)])
            ->first();

        if (!$template || $template->translations->isEmpty()) {
            $template = EmailTemplate::where('code', $code)
                ->with(['translations' => fn($q) => $q->where('locale', 'en')])
                ->first();
        }

        if (!$template || $template->translations->isEmpty()) {
            return null;
        }

        return $template->translations
            ->pluck('value', 'code')
            ->toArray();
    }

    /**
     * Настраивает SMTP конфигурацию из настроек в базе данных
     *
     * Получает настройки SMTP из таблицы options и применяет их к конфигурации Laravel.
     * Валидирует обязательные поля и обрабатывает пустые значения.
     *
     * @return void
     * @throws \Exception Если обязательные SMTP настройки не заполнены
     */
    public static function configureMailFromOptions(): void
    {
        // Получаем настройки из базы данных
        $host = Option::get('host');
        $port = Option::get('port');
        $encryption = Option::get('encryption');
        $username = Option::get('username');
        $password = Option::get('password');
        $fromAddress = Option::get('from_address');
        $fromName = Option::get('from_name');

        // Проверяем, что все обязательные поля заполнены
        if (empty($host) || empty($port) || empty($username) || empty($password) || empty($fromAddress)) {
            throw new \Exception('SMTP настройки неполные. Пожалуйста, заполните все обязательные поля в настройках SMTP.');
        }

        // Обрабатываем пустое значение encryption
        if (empty($encryption) || $encryption === 'null' || $encryption === '') {
            $encryption = null;
        }

        // Валидируем encryption (должно быть tls, ssl или null)
        if ($encryption !== null && !in_array($encryption, ['tls', 'ssl'])) {
            $encryption = null;
        }

        // Устанавливаем значение по умолчанию для порта, если не указано
        if (empty($port)) {
            $port = 587;
        }

        Config::set('mail.mailers.dynamic', [
            'transport' => 'smtp',
            'host' => $host,
            'port' => (int)$port,
            'encryption' => $encryption,
            'username' => $username,
            'password' => $password,
            'timeout' => null,
            'auth_mode' => null,
        ]);

        Config::set('mail.default', 'dynamic');

        Config::set('mail.from', [
            'address' => $fromAddress,
            'name' => $fromName ?: 'Subcloudy',
        ]);
    }

    /**
     * Рендерит шаблон текста, подставляя параметры
     *
     * Заменяет плейсхолдеры вида {{key}} на соответствующие значения из массива $params
     *
     * @param string|null $text Текст шаблона с плейсхолдерами
     * @param array $params Массив параметров для подстановки
     * @return string|null Обработанный текст или null если входной текст пустой
     */
    public static function renderTemplate(?string $text, array $params): ?string
    {
        if (!$text) {
            return null;
        }

        foreach ($params as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }

        return $text;
    }
}
