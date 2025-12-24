<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\\Http\\Controllers\\Controller;
use App\Models\Service;
use App\Models\ServiceAccount;
use App\Services\AssignServiceAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BrowserController extends Controller
{
    protected AssignServiceAccount $assignServiceAccount;

    public function __construct(AssignServiceAccount $assignServiceAccount)
    {
        $this->assignServiceAccount = $assignServiceAccount;
    }

    public function new(Request $request)
    {
        $service = Service::findOrFail($request->service_id);

        $base = rtrim(config('services.browser_api.url'), '/');
        $appUrl = $service->params['link'] ?? null;
        $user = $this->getApiUser($request);

        if ($user && !$user->hasActiveService($service->id)) {
            return response()->json(['error' => 'Service not active for user'], 403);
        }

        if ($request->has('profile')) {
            $profile = $request->profile;
        } else {
            if ($user) {
                $account = $this->assignServiceAccount->assignToUser($service->id, $user);
                if (!$account) {
                    return response()->json(['error' => 'No available accounts'], 409);
                }
            } else {
                // Fallback без пользователя: старая логика
                $account = ServiceAccount::where('service_id', $service->id)
                    ->where('is_active', true)
                    ->where(function ($q) { $q->whereNull('expiring_at')->orWhere('expiring_at', '>', now()); })
                    ->orderBy('id', 'asc')
                    ->first();
                if (!$account) {
                    return response()->json(['error' => 'No available accounts'], 409);
                }
            }

            $profile = $account->profile_id ?? null;
        }

        if ($appUrl && !Str::startsWith($appUrl, ['http://', 'https://'])) {
            $appUrl = 'https://' . ltrim($appUrl, '/');
        }

        if (!filter_var($appUrl, FILTER_VALIDATE_URL)) {
            $appUrl = 'https://google.com';
        }

        if ($user) {
            $appUrl .= '#sc_pair=sc_u_' . $user->id;
        }

        $width = $request->input('width', 1366);
        $height = $request->input('height', 768);

        $resp = Http::timeout(60)->get($base . '/new', [
            'app' => $appUrl,
            'profile' => $profile,
            'lang' => $request->uiLanguage ?? 'en',
            'width' => $width,
            'height' => $height,
        ]);

        return response($resp->body(), $resp->status())
            ->withHeaders(['Content-Type' => 'application/json']);
    }

    public function stop(Request $request)
    {
        $base = rtrim(config('services.browser_api.url'), '/');
        $resp = Http::timeout(60)->asJson()->post($base . '/stop', $request->all());

        return response($resp->body(), $resp->status())
            ->withHeaders(['Content-Type' => 'application/json']);
    }

    public function stopAll(Request $request)
    {
        $base = rtrim(config('services.browser_api.url'), '/');
        $resp = Http::timeout(60)->asJson()->post($base . '/stop_all', $request->all());

        return response($resp->body(), $resp->status())
            ->withHeaders(['Content-Type' => 'application/json']);
    }

    public function getList()
    {
        $base = rtrim(config('services.browser_api.url'), '/');
        $resp = Http::timeout(60)->get($base . '/list');

        return response($resp->body(), $resp->status())
            ->withHeaders(['Content-Type' => 'application/json']);
    }
}
