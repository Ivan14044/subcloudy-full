<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function getByCode(Request $request, string $code)
    {
        $locale = $request->get('lang', app()->getLocale());
        $content = Content::where('code', $code)->with('translations')->firstOrFail();

        $allFields = array_keys(config("contents.{$code}.fields", []));

        $translations = $content->translations
            ->where('locale', $locale);

        if ($translations->isEmpty() && $locale !== 'en') {
            // Fallback to English
            $translations = $content->translations
                ->where('locale', 'en');
        }

        // Проверяем, есть ли перевод с кодом 'value' (новый формат с JSON)
        $valueTranslation = $translations->where('code', 'value')->first();
        if ($valueTranslation) {
            $decoded = json_decode($valueTranslation->value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return response()->json([
                    'success' => true,
                    'code' => $code,
                    'data' => $decoded,
                ]);
            }
        }

        // Старый формат (faq.question.0)
        $entries = [];
        $prefix = $code . '.';
        foreach ($translations->filter(fn($t) => str_starts_with($t->code, $prefix)) as $t) {
            $parts = explode('.', $t->code);
            if (count($parts) !== 3) continue;

            [, $field, $index] = $parts;
            $entries[$index][$field] = $t->value;
        }

        foreach ($entries as &$entry) {
            foreach ($allFields as $field) {
                if (!array_key_exists($field, $entry)) {
                    $entry[$field] = null;
                }
            }
        }

        return response()->json([
            'success' => true,
            'code' => $code,
            'data' => array_values($entries),
        ]);
    }
}
