<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesktopActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'service_id',
        'service_name',
        'action',
        'timestamp',
        'duration',
        'ip',
    ];

    protected $casts = [
        'timestamp' => 'integer',
        'duration' => 'integer',
    ];

    /**
     * Получить пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить сервис
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
