<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function index()
    {
        // Исключаем системные контенты, которые управляются через отдельные разделы
        $excludedCodes = ['homepage_reviews', 'saving_on_subscriptions'];
        $contents = Content::with('translations')
            ->whereNotIn('code', $excludedCodes)
            ->orderBy('id', 'desc')
            ->get();
        return view('admin.contents.index', compact('contents'));
    }

    public function edit(Content $content)
    {
        try {
            $content->load('translations');
            
            // Формируем данные переводов аналогично другим контроллерам
            $contentData = $content->translations
                ->groupBy('locale')
                ->map(function ($translations) {
                    return $translations->pluck('value', 'code')->toArray();
                })
                ->toArray();

            // Убеждаемся, что для всех языков есть структура данных и парсим FAQ
            $allLangs = array_keys(config('langs', []));
            $faqData = [];
            
            foreach ($allLangs as $lang) {
                if (!isset($contentData[$lang])) {
                    $contentData[$lang] = [];
                }
                if (!isset($contentData[$lang]['value'])) {
                    $contentData[$lang]['value'] = '[]';
                }
                
                // Парсим JSON для каждого языка заранее
                $value = $contentData[$lang]['value'] ?? '[]';
                $decoded = json_decode($value, true);
                $faqData[$lang] = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
            }

            return view('admin.contents.edit', compact('content', 'contentData', 'faqData'));
        } catch (\Exception $e) {
            \Log::error('ContentController::edit error: ' . $e->getMessage(), [
                'content_id' => $content->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.contents.index')
                ->with('error', 'Ошибка при загрузке страницы редактирования: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Content $content)
    {
        $validated = $request->validate(
            $this->getRules(),
            [],
            getTransAttributes(['value'])
        );

        $content->saveTranslation($validated);

        $route = $request->has('save')
            ? route('admin.contents.edit', $content->id)
            : route('admin.contents.index');

        return redirect($route)->with('success', 'Контент успешно обновлен');
    }

    private function getRules(): array
    {
        $rules = [];

        // Добавляем правила для каждого языка
        foreach (config('langs') as $lang => $flag) {
            $rules["value.{$lang}"] = ['required', 'string'];
        }

        return $rules;
    }
}

