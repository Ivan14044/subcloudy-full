<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavingsBlock;
use Illuminate\Http\Request;

class SavingsBlockController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->get('lang', 'ru');
        
        $blocks = SavingsBlock::getActiveForLocale($locale)
            ->load('service')
            ->map(function ($block) use ($locale) {
                // Получаем переводы для текущей локали
                $block->title = $block->getTranslation('title', $locale);
                $block->text = $block->getTranslation('text', $locale);
                $block->our_price = $block->getTranslation('our_price', $locale);
                $block->normal_price = $block->getTranslation('normal_price', $locale);
                $block->advantage = $block->getTranslation('advantage', $locale);
                
                if ($block->logo) {
                    $block->logo = asset($block->logo);
                }
                return $block;
            });
        
        return response()->json([
            'success' => true,
            'data' => $blocks
        ]);
    }
}

