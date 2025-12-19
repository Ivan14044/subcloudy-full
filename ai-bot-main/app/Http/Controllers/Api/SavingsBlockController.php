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
            ->map(function ($block) {
                if ($block->logo) {
                    $block->logo = asset('storage/' . $block->logo);
                }
                return $block;
            });
        
        return response()->json([
            'success' => true,
            'data' => $blocks
        ]);
    }
}

