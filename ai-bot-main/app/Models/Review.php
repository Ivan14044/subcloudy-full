<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    use HasTranslations;

    protected $fillable = [
        'rating',
        'order',
        'is_active'
    ];

    protected $casts = [
        'rating' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean'
    ];

    const TRANSLATION_FIELDS = [
        'name',
        'text',
        'photo',
        'logo',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(ReviewTranslation::class);
    }

    // Получить значение перевода
    public function getTranslation(string $code, string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
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

    // Получить активные отзывы для языка
    public static function getActiveForLocale(string $locale): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->with(['translations' => function ($query) use ($locale) {
                $query->where('locale', $locale);
            }])
            ->orderBy('order')
            ->get()
            ->filter(function ($review) use ($locale) {
                // Фильтруем только те, у которых есть переводы для нужной локали
                return $review->getTranslation('name', $locale) !== null;
            });
    }
}

