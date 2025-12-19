<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->get('lang', 'ru');
        
        // Получаем все активные отзывы со всеми переводами
        $reviews = Review::where('is_active', true)
            ->with('translations')
            ->orderBy('order')
            ->orderBy('id')
            ->get();
        
        // Формируем данные для ответа
        $reviews = $reviews->map(function ($review) use ($locale) {
            $name = $review->getTranslation('name', $locale);
            $text = $review->getTranslation('text', $locale);
            $photo = $review->getTranslation('photo', $locale);
            $logo = $review->getTranslation('logo', $locale);
            
            // Пропускаем отзывы без перевода для текущей локали
            if (!$name || !$text) {
                return null;
            }
            
            // Формируем правильные URL для изображений
            if ($photo) {
                // Если уже полный URL, оставляем как есть
                if (strpos($photo, 'http://') === 0 || strpos($photo, 'https://') === 0) {
                    // Уже полный URL
                } else {
                    // Убираем дублирование storage/storage
                    $photo = ltrim($photo, '/');
                    if (strpos($photo, 'storage/') === 0) {
                        $photo = url($photo);
                    } else {
                        $photo = url('storage/' . $photo);
                    }
                }
            }
            
            if ($logo) {
                // Если уже полный URL, оставляем как есть
                if (strpos($logo, 'http://') === 0 || strpos($logo, 'https://') === 0) {
                    // Уже полный URL
                } else {
                    // Убираем дублирование storage/storage
                    $logo = ltrim($logo, '/');
                    if (strpos($logo, 'storage/') === 0) {
                        $logo = url($logo);
                    } else {
                        $logo = url('storage/' . $logo);
                    }
                }
            }
            
            return [
                'id' => $review->id,
                'name' => $name,
                'text' => $text,
                'rating' => $review->rating,
                'photo' => $photo,
                'logo' => $logo,
                'order' => $review->order,
            ];
        })->filter(); // Удаляем null значения
        
        return response()->json([
            'success' => true,
            'data' => $reviews->values() // Переиндексируем массив
        ]);
    }
}

