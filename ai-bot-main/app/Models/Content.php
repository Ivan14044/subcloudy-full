<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasTranslations;

    protected $fillable = [
        'code',
        'name',
    ];

    const TRANSLATION_FIELDS = [
        'value',
    ];

    public function translations()
    {
        return $this->hasMany(ContentTranslation::class);
    }

    /**
     * Получить значение перевода для локали
     */
    public function getTranslation(string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $this->translations
            ->where('locale', $locale)
            ->where('code', 'value')
            ->first();

        if (!$translation) {
            // Fallback на английский
            $translation = $this->translations
                ->where('locale', 'en')
                ->where('code', 'value')
                ->first();
        }

        return $translation?->value;
    }

    /**
     * Получить контент по коду
     */
    public static function getByCode(string $code, string $locale = null): ?self
    {
        return static::where('code', $code)->with('translations')->first();
    }
}

