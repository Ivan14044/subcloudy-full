<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsBlock extends Model
{
    protected $fillable = [
        'service_id',
        'title',
        'text',
        'logo',
        'our_price',
        'normal_price',
        'advantage',
        'locale',
        'order',
        'is_active'
    ];

    protected $casts = [
        'order' => 'integer',
        'is_active' => 'boolean'
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    // Получить активные блоки для языка
    public static function getActiveForLocale(string $locale): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('locale', $locale)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }
}

