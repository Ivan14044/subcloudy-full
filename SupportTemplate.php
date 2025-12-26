<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTemplate extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    const TRANSLATION_FIELDS = ['title', 'text'];

    public function translations()
    {
        return $this->hasMany(SupportTemplateTranslation::class);
    }

    /**
     * Получить перевод поля
     */
    public function getTranslation(string $code, string $locale = 'ru'): ?string
    {
        $translation = $this->translations
            ->where('code', $code)
            ->where('locale', $locale)
            ->first();

        if (!$translation && $locale !== 'ru') {
            $translation = $this->translations
                ->where('code', $code)
                ->where('locale', 'ru')
                ->first();
        }

        return $translation?->value;
    }

    /**
     * Получить активные шаблоны
     */
    public static function getActive($lang = 'ru')
    {
        return self::where('is_active', true)
            ->with(['translations' => function($q) use ($lang) {
                $q->where('locale', $lang);
            }])
            ->orderBy('sort_order')
            ->get();
    }
}

