<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{
    /**
     * Отображает страницу настроек
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $currency = Option::get('currency');

        return view('admin.settings.index', compact('currency'));
    }

    /**
     * Сохраняет настройки системы
     *
     * Валидирует данные в зависимости от типа формы и сохраняет их в базу данных.
     * При сохранении SMTP настроек очищает соответствующий кэш.
     *
     * @param Request $request HTTP запрос с данными формы
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->getRules($request->form));

        foreach ($validated as $key => $value) {
            // Обрабатываем пустое значение encryption
            if ($key === 'encryption' && ($value === '' || $value === null)) {
                $value = null;
            }
            Option::set($key, $value);
        }

        // Очищаем кэш SMTP настроек при изменении
        if ($request->form === 'smtp') {
            // Очищаем кэш всех SMTP опций
            Cache::forget('option_host');
            Cache::forget('option_port');
            Cache::forget('option_encryption');
            Cache::forget('option_username');
            Cache::forget('option_password');
            Cache::forget('option_from_address');
            Cache::forget('option_from_name');
            
            // Очищаем кэш конфигурации Laravel
            Artisan::call('config:clear');
        }

        return redirect()->route('admin.settings.index')
            ->with('active_tab', $request->form)
            ->with('success', 'Settings saved successfully.');
    }

    /**
     * Тестирует SMTP подключение без сохранения настроек
     *
     * Временно настраивает SMTP с переданными параметрами и отправляет тестовое письмо.
     * Используется для проверки корректности настроек перед сохранением.
     *
     * @param Request $request HTTP запрос с SMTP параметрами
     * @return \Illuminate\Http\JsonResponse JSON ответ с результатом тестирования
     */
    public function testSmtp(Request $request)
    {
        $validated = $request->validate([
            'from_address' => ['required', 'email'],
            'from_name' => ['required', 'string', 'max:255'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'encryption' => ['nullable', 'string', 'in:tls,ssl'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        try {
            // Временно настраиваем SMTP для тестирования
            $encryption = !empty($validated['encryption']) ? $validated['encryption'] : null;
            
            Config::set('mail.mailers.test', [
                'transport' => 'smtp',
                'host' => $validated['host'],
                'port' => $validated['port'],
                'encryption' => $encryption,
                'username' => $validated['username'],
                'password' => $validated['password'],
                'timeout' => 10,
                'auth_mode' => null,
            ]);

            Config::set('mail.default', 'test');

            Config::set('mail.from', [
                'address' => $validated['from_address'],
                'name' => $validated['from_name'],
            ]);

            // Отправляем тестовое письмо
            $testEmail = $validated['from_address'];
            
            Mail::send('emails.test', [
                'host' => $validated['host'],
                'port' => $validated['port'],
                'encryption' => $encryption ?? 'Без шифрования',
                'from_address' => $validated['from_address'],
                'from_name' => $validated['from_name'],
                'timestamp' => now()->format('d.m.Y H:i:s'),
            ], function ($message) use ($testEmail) {
                $message->to($testEmail)
                        ->subject('Тест SMTP подключения - Subcloudy');
            });

            Log::info('SMTP test successful', [
                'host' => $validated['host'],
                'port' => $validated['port'],
                'encryption' => $encryption,
                'from' => $validated['from_address'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Тестовое письмо успешно отправлено! Проверьте почтовый ящик ' . $testEmail,
            ]);
        } catch (\Exception $e) {
            Log::error('SMTP test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'host' => $validated['host'] ?? null,
                'port' => $validated['port'] ?? null,
            ]);

            $errorMessage = 'Ошибка при отправке тестового письма: ' . $e->getMessage();
            
            // Более понятные сообщения для распространенных ошибок
            if (str_contains($e->getMessage(), 'Connection timed out')) {
                $errorMessage = 'Не удалось подключиться к SMTP серверу. Проверьте правильность хоста и порта, а также доступность сервера.';
            } elseif (str_contains($e->getMessage(), 'Authentication failed')) {
                $errorMessage = 'Ошибка аутентификации. Проверьте правильность имени пользователя и пароля.';
            } elseif (str_contains($e->getMessage(), 'Could not connect to host')) {
                $errorMessage = 'Не удалось подключиться к хосту. Проверьте правильность адреса SMTP сервера.';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 422);
        }
    }

    /**
     * Возвращает правила валидации для указанного типа формы
     *
     * @param string $form Тип формы ('smtp', 'header_menu', 'footer_menu', 'cookie' или default)
     * @return array Массив правил валидации
     */
    private function getRules($form)
    {
        return match ($form) {
            'header_menu' => [
                'header_menu' => ['required', 'array'],
            ],
            'footer_menu' => [
                'footer_menu' => ['required', 'array'],
            ],
            'cookie' => [
                'cookie_countries' => ['required', 'array'],
            ],
            
            'smtp' => [
                'from_address' => ['required', 'email', 'max:255'],
                'from_name' => ['required', 'string', 'max:255'],
                'host' => ['required', 'string', 'max:255'],
                'port' => ['required', 'integer', 'min:1', 'max:65535'],
                'encryption' => ['nullable', 'string', 'in:tls,ssl'],
                'username' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string'],
            ],
            default => [
                'currency' => ['required', 'string'],
                'trial_days' => ['required', 'integer', 'between:0,30'],
                'discount_2' => ['required', 'integer'],
                'discount_3' => ['required', 'integer'],
            ],
        };
    }
}
