<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'text',
        'lang',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Получить активные шаблоны
     */
    public static function getActive($lang = 'ru')
    {
        return self::where('is_active', true)
            ->where('lang', $lang)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }
}

