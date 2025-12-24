<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavingsBlock;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ImageOptimizer;

class SavingsBlockController extends Controller
{
    public function index()
    {
        $blocks = SavingsBlock::with('translations', 'service')->orderBy('order')->orderBy('id', 'desc')->get();
        return view('admin.savings-blocks.index', compact('blocks'));
    }

    public function create()
    {
        $services = Service::withEnglishName()->orderBy('id', 'desc')->get();
        return view('admin.savings-blocks.create', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->getRules(),
            [],
            getTransAttributes(['title', 'text', 'our_price', 'normal_price', 'advantage'])
        );

        // Обработка загрузки логотипа
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('savings-blocks', 'public');
            // Автоматически конвертируем в WebP и удаляем оригинал
            $webpPath = ImageOptimizer::convertToWebP($logoPath, 400, 400, 85);
            $logoPath = $webpPath ?: $logoPath;
        }

        $block = SavingsBlock::create([
            'service_id' => $validated['service_id'] ?? null,
            'logo' => $logoPath ? Storage::url($logoPath) : null,
            'order' => $validated['order'] ?? 0,
            'is_active' => $request->has('is_active')
        ]);

        // Сохраняем переводы
        $block->saveTranslation($validated);

        return redirect()->route('admin.savings-blocks.index')
            ->with('success', 'Блок экономии успешно создан');
    }

    public function edit(SavingsBlock $savingsBlock)
    {
        $savingsBlock->load('translations');
        $blockData = $savingsBlock->translations->groupBy('locale')->map(function ($translations) {
            return $translations->pluck('value', 'code')->toArray();
        });

        $services = Service::withEnglishName()->orderBy('id', 'desc')->get();
        return view('admin.savings-blocks.edit', compact('savingsBlock', 'services', 'blockData'));
    }

    public function update(Request $request, SavingsBlock $savingsBlock)
    {
        $validated = $request->validate(
            $this->getRules(),
            [],
            getTransAttributes(['title', 'text', 'our_price', 'normal_price', 'advantage'])
        );

        // Обработка загрузки логотипа
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('savings-blocks', 'public');
            // Автоматически конвертируем в WebP и удаляем оригинал
            $webpPath = ImageOptimizer::convertToWebP($logoPath, 400, 400, 85);
            $logoPath = $webpPath ?: $logoPath;
            $savingsBlock->logo = Storage::url($logoPath);
        }

        $savingsBlock->update([
            'service_id' => $validated['service_id'] ?? null,
            'order' => $validated['order'] ?? 0,
            'is_active' => $request->has('is_active')
        ]);

        // Сохраняем переводы
        $savingsBlock->saveTranslation($validated);

        return redirect()->route('admin.savings-blocks.index')
            ->with('success', 'Блок экономии успешно обновлен');
    }

    public function destroy(SavingsBlock $savingsBlock)
    {
        $savingsBlock->delete();
        return redirect()->route('admin.savings-blocks.index')
            ->with('success', 'Блок экономии успешно удален');
    }

    private function getRules(): array
    {
        $rules = [
            'service_id' => 'nullable|exists:services,id',
            'logo' => 'nullable|image|max:2048',
            'order' => 'nullable|integer',
            'is_active' => 'boolean'
        ];

        // Добавляем правила для каждого языка
        foreach (config('langs') as $locale => $flag) {
            $rules["title.{$locale}"] = 'required|string|max:255';
            $rules["text.{$locale}"] = 'nullable|string';
            $rules["our_price.{$locale}"] = 'nullable|string|max:255';
            $rules["normal_price.{$locale}"] = 'nullable|string|max:255';
            $rules["advantage.{$locale}"] = 'nullable|string';
        }

        return $rules;
    }
}

