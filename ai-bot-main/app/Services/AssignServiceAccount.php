<?php

namespace App\Services;

use App\Models\ServiceAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AssignServiceAccount
{
    public function assignToUser(int $serviceId, User $user): ?ServiceAccount
    {
        $now = now();
        $minExpiry = now()->addHours(config('services.accounts.min_expiry_hours', 3));

        // 1) Если у пользователя уже есть актуальный аккаунт — вернуть его и обновить last_used_at
        $existing = $user->serviceAccounts()
            ->where('service_accounts.service_id', $serviceId)
            ->where('service_accounts.is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('service_accounts.expiring_at')
                  ->orWhere('service_accounts.expiring_at', '>', $now);
            })
            ->first();

        if ($existing) {
            $existing->forceFill(['last_used_at' => $now])->save();
            return $existing;
        }

        // 2) Подбор нового аккаунта с балансировкой: users_count -> used -> last_used_at -> id
        // Исключаем аккаунты, где достигнут лимит пользователей
        $pick = function ($expiryThreshold) use ($serviceId) {
            return ServiceAccount::query()
                ->withCount('users')
                ->where('service_id', $serviceId)
                ->where('is_active', true)
                ->where(function ($q) use ($expiryThreshold) {
                    $q->whereNull('expiring_at')->orWhere('expiring_at', '>=', $expiryThreshold);
                })
                ->where(function ($q) {
                    // Исключаем аккаунты, где достигнут лимит пользователей
                    // NULL max_users = без ограничений
                    $q->whereNull('max_users')
                      ->orWhereRaw('(SELECT COUNT(*) FROM user_service_accounts WHERE service_account_id = service_accounts.id) < max_users');
                })
                ->orderBy('users_count', 'asc')
                ->orderBy('used', 'asc')
                ->orderBy('last_used_at', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->first();
        };

        return DB::transaction(function () use ($user, $pick, $minExpiry, $now) {
            // Попытка с запасом времени
            $candidate = $pick($minExpiry);
            // Если нет — берем любые активные (>= now())
            if (!$candidate) {
                $candidate = $pick($now);
            }

            if (!$candidate) {
                return null;
            }

            $user->serviceAccounts()->syncWithoutDetaching([
                $candidate->id => ['created_at' => $now, 'updated_at' => $now],
            ]);

            $candidate->increment('used');
            $candidate->forceFill(['last_used_at' => $now])->save();

            return $candidate;
        });
    }
}
