<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DesktopActivityLog;
use App\Models\Service;
use App\Models\User;
use App\Services\AssignServiceAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DesktopController extends Controller
{
    protected AssignServiceAccount $assignServiceAccount;

    public function __construct(AssignServiceAccount $assignServiceAccount)
    {
        $this->assignServiceAccount = $assignServiceAccount;
    }

    /**
     * Аутентификация для десктоп-приложения
     * POST /api/desktop/auth
     */
    public function auth(Request $request)
    {
        $user = $request->user();
        
        // Создаем токен с расширенными правами для desktop
        $token = $user->createToken('desktop-app', ['desktop:access'])->plainTextToken;
        
        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'active_services' => $this->getUserActiveServices($user)
            ]
        ]);
    }

    /**
     * Получение защищенной ссылки для запуска сервиса
     * POST /api/desktop/service-url
     */
    public function getSecureServiceUrl(Request $request)
    {
        $request->validate([
            'service_id' => 'required|integer|exists:services,id'
        ]);

        $serviceId = $request->service_id;
        $user = $request->user();

        // Проверка доступа к сервису
        if (!$this->hasActiveService($user, $serviceId)) {
            return response()->json([
                'success' => false,
                'error' => 'Service not active for user'
            ], 403);
        }

        $service = Service::findOrFail($serviceId);
        
        // Назначаем аккаунт пользователю
        $account = $this->assignServiceAccount->assignToUser($serviceId, $user);
        
        if (!$account) {
            return response()->json([
                'success' => false,
                'error' => 'No available accounts for this service'
            ], 409);
        }

        // URL сервиса
        $serviceUrl = $service->params['link'] ?? 'https://google.com';
        
        // Добавляем схему если нет
        if (!str_starts_with($serviceUrl, 'http://') && !str_starts_with($serviceUrl, 'https://')) {
            $serviceUrl = 'https://' . $serviceUrl;
        }
        
        // Добавляем пометку пользователя
        $serviceUrl .= (str_contains($serviceUrl, '?') ? '&' : '?') . 'sc_pair=sc_u_' . $user->id;

        // Подготавливаем credentials для desktop приложения
        $credentials = $account->credentials ?? [];
        
        // Извлекаем cookies если есть
        $cookies = [];
        if (isset($credentials['cookies'])) {
            // Cookies могут быть строкой (JSON) или массивом
            if (is_string($credentials['cookies'])) {
                // Декодируем JSON строку
                $decoded = json_decode($credentials['cookies'], true);
                if (is_array($decoded)) {
                    $cookies = $decoded;
                }
            } elseif (is_array($credentials['cookies'])) {
                $cookies = $credentials['cookies'];
            }
        }

        \Log::info('[Desktop] Service account assigned', [
            'user_id' => $user->id,
            'service_id' => $serviceId,
            'account_id' => $account->id,
            'profile_id' => $account->profile_id,
            'cookies_count' => count($cookies)
        ]);

        // Получаем имя сервиса безопасно
        $serviceName = "Service {$serviceId}";
        try {
            if (method_exists($service, 'getTranslation')) {
                $serviceName = $service->getTranslation('name', 'en') ?? $service->name ?? $serviceName;
            } elseif ($service->name) {
                $serviceName = $service->name;
            }
        } catch (\Throwable $e) {
            \Log::warning('[Desktop] Failed to get service name', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'service_url' => $serviceUrl,
            'service_name' => $serviceName,
            'profile_id' => $account->profile_id,
            'account_id' => $account->id,
            'credentials' => [
                'cookies' => $cookies,
                'email' => $credentials['email'] ?? null,
                // Не отправляем пароль в plaintext - только если нужен
            ]
        ]);
    }

    /**
     * Получение списка активных сервисов пользователя
     * GET /api/desktop/my-services
     */
    public function myServices(Request $request)
    {
        $user = $request->user();
        $activeServiceIds = $this->getUserActiveServices($user);

        $services = Service::with('translations')
            ->whereIn('id', $activeServiceIds)
            ->where('is_active', true)
            ->orderBy('position', 'asc')
            ->get();

        $data = $services->map(function ($service) {
            // Безопасное получение имени
            $name = "Service {$service->id}";
            try {
                if (method_exists($service, 'getTranslation')) {
                    $name = $service->getTranslation('name', 'en') ?? $service->name ?? $name;
                } elseif ($service->name) {
                    $name = $service->name;
                }
            } catch (\Throwable $e) {
                \Log::warning('[Desktop] Failed to get service name for myServices', ['service_id' => $service->id]);
            }

            return [
                'id' => $service->id,
                'code' => $service->code,
                'name' => $name,
                'logo' => $service->logo,
                'amount' => $service->amount,
                'params' => $service->params,
                'translations' => $service->translations->groupBy('locale')->map(function ($translations) {
                    return $translations->pluck('value', 'code');
                })
            ];
        });

        return response()->json($data);
    }

    /**
     * Логирование активности из desktop приложения
     * POST /api/desktop/log
     */
    public function logActivity(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'service_id' => 'required|integer|exists:services,id',
            'action' => 'required|string|in:session_started,session_stopped',
            'timestamp' => 'required|integer',
            'duration' => 'nullable|integer|min:0',
            'service_name' => 'nullable|string|max:255',
            'session_id' => 'nullable|string|max:255',
        ]);

        // Получаем название сервиса
        $service = Service::with('translations')->find($request->service_id);
        $serviceName = $request->service_name ?? "Service {$request->service_id}";
        
        // Если название не передано или это дефолтное, пытаемся получить из БД
        if (!$request->service_name || str_starts_with($serviceName, 'Service ')) {
            if ($service) {
                try {
                    if (method_exists($service, 'getTranslation')) {
                        $serviceName = $service->getTranslation('name', 'en') ?? $service->getTranslation('name', 'ru') ?? $service->name ?? $serviceName;
                    } elseif ($service->name) {
                        $serviceName = $service->name;
                    }
                } catch (\Throwable $e) {
                    \Log::warning('[Desktop] Failed to get service name', ['error' => $e->getMessage()]);
                }
            }
        }

        // Сохраняем в БД
        try {
            // Если это session_stopped и есть session_id, обновляем существующую запись
            if ($request->action === 'session_stopped' && $request->session_id) {
                $existingLog = DesktopActivityLog::where('session_id', $request->session_id)
                    ->where('action', 'session_started')
                    ->where('user_id', $request->user_id)
                    ->where('service_id', $request->service_id)
                    ->first();
                
                if ($existingLog) {
                    // Обновляем существующую запись
                    $existingLog->update([
                        'action' => 'session_stopped',
                        'duration' => $request->duration,
                        'service_name' => $serviceName, // Обновляем название на случай, если оно изменилось
                    ]);
                    $log = $existingLog;
                } else {
                    // Если не найдена запись session_started, создаем новую
                    $log = DesktopActivityLog::create([
                        'session_id' => $request->session_id,
                        'user_id' => $request->user_id,
                        'service_id' => $request->service_id,
                        'service_name' => $serviceName,
                        'action' => $request->action,
                        'timestamp' => $request->timestamp,
                        'duration' => $request->duration,
                        'ip' => $request->ip(),
                    ]);
                }
            } else {
                // Создаем новую запись для session_started или если нет session_id
                $log = DesktopActivityLog::create([
                    'session_id' => $request->session_id,
                    'user_id' => $request->user_id,
                    'service_id' => $request->service_id,
                    'service_name' => $serviceName,
                    'action' => $request->action,
                    'timestamp' => $request->timestamp,
                    'duration' => $request->duration,
                    'ip' => $request->ip(),
                ]);
            }
        } catch (\Throwable $e) {
            throw $e;
        }

        // Также логируем в файл для отладки
        \Log::info('[Desktop Activity]', [
            'user_id' => $request->user_id,
            'service_id' => $request->service_id,
            'action' => $request->action,
            'timestamp' => $request->timestamp,
            'duration' => $request->duration,
            'ip' => $request->ip()
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Получение активных сервисов пользователя
     */
    private function getUserActiveServices(User $user): array
    {
        return $user->subscriptions()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('next_payment_at')
                  ->orWhere('next_payment_at', '>', now());
            })
            ->pluck('service_id')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Проверка активной подписки на сервис
     */
    private function hasActiveService(User $user, int $serviceId): bool
    {
        return in_array($serviceId, $this->getUserActiveServices($user));
    }
}


