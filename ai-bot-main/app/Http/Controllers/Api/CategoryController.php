<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\\Http\\Controllers\\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('translations')->get();

        $data = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'translations' => $category->translations
                    ->groupBy('locale')
                    ->map(fn($translations) => $translations->pluck('value', 'code')),
            ];
        });

        return response()->json($data);
    }
}
