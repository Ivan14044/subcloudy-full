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
        $blocks = SavingsBlock::orderBy('order')->orderBy('id', 'desc')->get();
        return view('admin.savings-blocks.index', compact('blocks'));
    }

    public function create()
    {
        $services = Service::all();
        return view('admin.savings-blocks.create', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'nullable|exists:services,id',
            'title' => 'required|string|max:255',
            'text' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'our_price' => 'nullable|string|max:255',
            'normal_price' => 'nullable|string|max:255',
            'advantage' => 'nullable|string',
            'locale' => 'required|string|max:10',
            'order' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('savings-blocks', 'public');
            // Автоматически конвертируем в WebP и удаляем оригинал
            $webpPath = ImageOptimizer::convertToWebP($logoPath, 400, 400, 85);
            $validated['logo'] = $webpPath ?: $logoPath;
        }

        $validated['is_active'] = $request->has('is_active');

        SavingsBlock::create($validated);

        return redirect()->route('admin.savings-blocks.index')
            ->with('success', 'Блок экономии успешно создан');
    }

    public function edit(SavingsBlock $savingsBlock)
    {
        $services = Service::all();
        return view('admin.savings-blocks.edit', compact('savingsBlock', 'services'));
    }

    public function update(Request $request, SavingsBlock $savingsBlock)
    {
        $validated = $request->validate([
            'service_id' => 'nullable|exists:services,id',
            'title' => 'required|string|max:255',
            'text' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'our_price' => 'nullable|string|max:255',
            'normal_price' => 'nullable|string|max:255',
            'advantage' => 'nullable|string',
            'locale' => 'required|string|max:10',
            'order' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('savings-blocks', 'public');
            // Автоматически конвертируем в WebP и удаляем оригинал
            $webpPath = ImageOptimizer::convertToWebP($logoPath, 400, 400, 85);
            $validated['logo'] = $webpPath ?: $logoPath;
        }

        $validated['is_active'] = $request->has('is_active');

        $savingsBlock->update($validated);

        return redirect()->route('admin.savings-blocks.index')
            ->with('success', 'Блок экономии успешно обновлен');
    }

    public function destroy(SavingsBlock $savingsBlock)
    {
        $savingsBlock->delete();
        return redirect()->route('admin.savings-blocks.index')
            ->with('success', 'Блок экономии успешно удален');
    }
}

