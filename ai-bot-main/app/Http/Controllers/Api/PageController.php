<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->input('lang', 'ru');
        
        $pages = Page::with('translations')
            ->where('is_active', true)
            ->get()
            ->keyBy('slug');

        // Формируем данные в формате: { slug: { locale: { title, content, ... } } }
        $data = $pages->map(function ($page) use ($lang) {
            $translationsByLocale = $page->translations->groupBy('locale');
            
            // Формируем объект с переводами для каждой локали
            $result = [];
            foreach ($translationsByLocale as $locale => $translations) {
                $result[$locale] = $translations->pluck('value', 'code')->toArray();
            }
            
            return $result;
        });

        return response()->json($data);
    }
}

