<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\\Http\\Controllers\\Controller;
use App\Models\Service;
use App\Models\ServiceAccount;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with('translations')
            ->where('is_active', true)
            ->orderBy('position', 'asc')
            ->get();

        $minExpiry = now()->addHours(config('services.accounts.min_expiry_hours', 3));

        $data = $services->map(function ($service) use ($minExpiry) {
            $availableCount = ServiceAccount::query()
                ->where('service_id', $service->id)
                ->where('is_active', true)
                ->where(function ($q) use ($minExpiry) {
                    $q->whereNull('expiring_at')->orWhere('expiring_at', '>=', $minExpiry);
                })
                ->count();

            return [
                'id' => $service->id,
                'code' => $service->code,
                'logo' => $service->logo,
                'amount' => $service->amount,
                'trial_amount' => $service->trial_amount,
                'params' => $service->params,
                'available_accounts' => $availableCount,
                'translations' => $service->translations->groupBy('locale')->map(function ($translations) {
                    return $translations->pluck('value', 'code');
                })
            ];
        });

        return response()->json($data);
    }
}


