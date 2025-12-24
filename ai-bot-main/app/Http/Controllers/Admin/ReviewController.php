<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ImageOptimizer;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with('translations')->orderBy('order')->orderBy('id', 'desc')->get();
        return view('admin.reviews.index', compact('reviews'));
    }

    public function create()
    {
        return view('admin.reviews.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->getRules(),
            [],
            getTransAttributes(['name', 'text', 'photo', 'logo'])
        );

        // Обработка загрузки файлов для каждой локализации
        $photoFiles = [];
        $logoFiles = [];
        
        foreach (config('langs') as $locale => $flag) {
            if ($request->hasFile("photo.{$locale}")) {
                $photoPath = $request->file("photo.{$locale}")->store('reviews', 'public');
                // Автоматически конвертируем в WebP и удаляем оригинал
                $webpPhotoPath = ImageOptimizer::convertToWebP($photoPath, 400, 400, 85);
                $photoPath = $webpPhotoPath ?: $photoPath;
                $photoFiles[$locale] = $photoPath;
            }
            if ($request->hasFile("logo.{$locale}")) {
                $logoPath = $request->file("logo.{$locale}")->store('reviews', 'public');
                // Автоматически конвертируем в WebP и удаляем оригинал
                $webpLogoPath = ImageOptimizer::convertToWebP($logoPath, 200, 200, 85);
                $logoPath = $webpLogoPath ?: $logoPath;
                $logoFiles[$locale] = $logoPath;
            }
        }

        $review = Review::create([
            'rating' => $validated['rating'],
            'order' => $validated['order'] ?? 0,
            'is_active' => $request->has('is_active')
        ]);

        // Сохраняем переводы
        $translationData = $validated;
        foreach ($photoFiles as $locale => $path) {
            if (!isset($translationData['photo'])) {
                $translationData['photo'] = [];
            }
            $translationData['photo'][$locale] = Storage::url($path);
        }
        foreach ($logoFiles as $locale => $path) {
            if (!isset($translationData['logo'])) {
                $translationData['logo'] = [];
            }
            $translationData['logo'][$locale] = Storage::url($path);
        }

        $review->saveTranslation($translationData);

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Отзыв успешно создан');
    }

    public function edit(Review $review)
    {
        $review->load('translations');
        $reviewData = $review->translations->groupBy('locale')->map(function ($translations) {
            return $translations->pluck('value', 'code')->toArray();
        });

        return view('admin.reviews.edit', compact('review', 'reviewData'));
    }

    public function update(Request $request, Review $review)
    {
        $validated = $request->validate(
            $this->getRules(),
            [],
            getTransAttributes(['name', 'text', 'photo', 'logo'])
        );

        // Обработка загрузки файлов для каждой локализации
        $photoFiles = [];
        $logoFiles = [];
        
        foreach (config('langs') as $locale => $flag) {
            if ($request->hasFile("photo.{$locale}")) {
                $photoPath = $request->file("photo.{$locale}")->store('reviews', 'public');
                // Автоматически конвертируем в WebP и удаляем оригинал
                $webpPhotoPath = ImageOptimizer::convertToWebP($photoPath, 400, 400, 85);
                $photoPath = $webpPhotoPath ?: $photoPath;
                $photoFiles[$locale] = $photoPath;
            }
            if ($request->hasFile("logo.{$locale}")) {
                $logoPath = $request->file("logo.{$locale}")->store('reviews', 'public');
                // Автоматически конвертируем в WebP и удаляем оригинал
                $webpLogoPath = ImageOptimizer::convertToWebP($logoPath, 200, 200, 85);
                $logoPath = $webpLogoPath ?: $logoPath;
                $logoFiles[$locale] = $logoPath;
            }
        }

        $review->update([
            'rating' => $validated['rating'],
            'order' => $validated['order'] ?? 0,
            'is_active' => $request->has('is_active')
        ]);

        // Сохраняем переводы
        $translationData = $validated;
        foreach ($photoFiles as $locale => $path) {
            if (!isset($translationData['photo'])) {
                $translationData['photo'] = [];
            }
            $translationData['photo'][$locale] = Storage::url($path);
        }
        foreach ($logoFiles as $locale => $path) {
            if (!isset($translationData['logo'])) {
                $translationData['logo'] = [];
            }
            $translationData['logo'][$locale] = Storage::url($path);
        }

        // Сохраняем существующие фото/лого, если они не были загружены новые
        foreach (config('langs') as $locale => $flag) {
            if ($request->has("photo_text.{$locale}") && !isset($photoFiles[$locale])) {
                if (!isset($translationData['photo'])) {
                    $translationData['photo'] = [];
                }
                $translationData['photo'][$locale] = $request->input("photo_text.{$locale}");
            }
            if ($request->has("logo_text.{$locale}") && !isset($logoFiles[$locale])) {
                if (!isset($translationData['logo'])) {
                    $translationData['logo'] = [];
                }
                $translationData['logo'][$locale] = $request->input("logo_text.{$locale}");
            }
        }

        $review->saveTranslation($translationData);

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Отзыв успешно обновлен');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return redirect()->route('admin.reviews.index')
            ->with('success', 'Отзыв успешно удален');
    }

    private function getRules(): array
    {
        $rules = [
            'rating' => 'required|integer|min:1|max:5',
            'order' => 'nullable|integer',
            'is_active' => 'boolean'
        ];

        // Добавляем правила для каждого языка
        foreach (config('langs') as $locale => $flag) {
            $rules["name.{$locale}"] = 'required|string|max:255';
            $rules["text.{$locale}"] = 'required|string';
            $rules["photo.{$locale}"] = 'nullable|image|max:2048';
            $rules["logo.{$locale}"] = 'nullable|image|max:2048';
        }

        return $rules;
    }
}

