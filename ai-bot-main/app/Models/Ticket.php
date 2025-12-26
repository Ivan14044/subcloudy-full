<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'external_channel',
        'telegram_chat_id',
        'status',
        'subject',
        'guest_email',
        'session_token',
        'lang',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Получить пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить все сообщения
     */
    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * Получить последнее сообщение
     */
    public function lastMessage()
    {
        return $this->hasOne(TicketMessage::class)->latestOfMany();
    }

    /**
     * Проверка, есть ли у тикета Telegram канал
     */
    public function hasTelegramChannel(): bool
    {
        return !empty($this->telegram_chat_id);
    }

    /**
     * Получить email пользователя (из user или guest_email)
     */
    public function getEmailAttribute(): ?string
    {
        return $this->user ? $this->user->email : $this->guest_email;
    }
}

