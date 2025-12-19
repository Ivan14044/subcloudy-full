<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    /**
     * Получить FAQ по коду
     */
    public function getByCode(Request $request, string $code)
    {
        $locale = $request->get('lang', 'ru');
        
        $content = Content::getByCode($code);
        
        if (!$content) {
            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }
        
        $value = $content->getTranslation($locale);
        
        // Парсим JSON если это FAQ
        $faqData = [];
        if ($value) {
            try {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $faqData = $decoded;
                }
            } catch (\Exception $e) {
                // Если не JSON, возвращаем как есть
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $faqData
        ]);
    }
}

