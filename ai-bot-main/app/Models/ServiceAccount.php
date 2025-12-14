<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'profile_id',
        'credentials',
        'used',
        'expiring_at',
        'last_used_at',
        'is_active',
        'max_users',
    ];

    protected $casts = [
        'credentials' => 'array',
        'expiring_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_service_accounts')->withTimestamps();
    }

    /**
     * Проверяет, доступен ли аккаунт для назначения новому пользователю
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        if ($this->max_users === null) {
            return true; // Без ограничений
        }
        return $this->users()->count() < $this->max_users;
    }

    /**
     * Получить текущее количество пользователей на аккаунте
     *
     * @return int
     */
    public function getUsersCountAttribute(): int
    {
        return $this->users()->count();
    }
}
