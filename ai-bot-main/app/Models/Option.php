<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Option extends Model
{
    protected $fillable = [
        'name',
        'value',
    ];

    public static function set(string $name, $value): self
    {
        $value = is_array($value) ? json_encode($value) : $value;

        $option = self::updateOrCreate(
            ['name' => $name],
            ['value' => $value]
        );

        // Очищаем кэш при изменении
        Cache::forget("option_{$name}");

        return $option;
    }

    public static function get(string $name, $defaultValue = null)
    {
        $value = Cache::remember("option_{$name}", 86400, function () use ($name, $defaultValue) {
            return self::where('name', $name)->first()->value ?? $defaultValue;
        });

        if (is_string($value) && (str_starts_with($value, '[') || str_starts_with($value, '{'))) {
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        return $value;
    }
}
