<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsBlock extends Model
{
    use HasTranslations;

    protected $fillable = [
        'service_id',
        'logo',
        'order',
        'is_active'
    ];

    protected $casts = [
        'order' => 'integer',
        'is_active' => 'boolean'
    ];

    const TRANSLATION_FIELDS = [
        'title',
        'text',
        'our_price',
        'normal_price',
        'advantage',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SavingsBlockTranslation::class);
    }

    // Получить значение перевода
    public function getTranslation(string $code, string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        
        // Если translations уже загружены (через with), используем коллекцию
        if ($this->relationLoaded('translations')) {
            $translation = $this->translations
                ->where('code', $code)
                ->where('locale', $locale)
                ->first();

            if (!$translation) {
                // Fallback на английский
                $translation = $this->translations
                    ->where('code', $code)
                    ->where('locale', 'en')
                    ->first();
            }

            return $translation?->value;
        }
        
        // Если translations не загружены, делаем запрос к БД
        $translation = $this->translations()
            ->where('code', $code)
            ->where('locale', $locale)
            ->first();

        if (!$translation) {
            // Fallback на английский
            $translation = $this->translations()
                ->where('code', $code)
                ->where('locale', 'en')
                ->first();
        }

        return $translation?->value;
    }

    // Получить активные блоки для языка
    public static function getActiveForLocale(string $locale): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->with('translations')
            ->orderBy('order')
            ->get()
            ->filter(function ($block) use ($locale) {
                // Фильтруем только те, у которых есть переводы для нужной локали или английского (fallback)
                return $block->getTranslation('title', $locale) !== null;
            });
    }
}


