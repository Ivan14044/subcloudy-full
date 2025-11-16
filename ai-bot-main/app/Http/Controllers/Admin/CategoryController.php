<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('translations')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->getRules(),
            [],
            getTransAttributes(['name', 'meta_title', 'meta_description', 'text'])
        );

        $category = Category::create();

        $category->saveTranslation($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category successfully created.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate(
            $this->getRules($category->id),
            [],
            getTransAttributes(['name', 'meta_title', 'meta_description', 'text'])
        );

        $category->saveTranslation($validated);

        $route = $request->has('save')
            ? route('admin.categories.edit', $category->id)
            : route('admin.categories.index');

        return redirect($route)->with('success', 'Category successfully updated.');
    }

    public function edit(Category $category)
    {
        $category->load('translations');

        $categoryData = $category->translations
            ->groupBy('locale')
            ->map(function ($translations) {
                return [
                    'name' => optional($translations->firstWhere('code', 'name'))->value,
                    'meta_title' => optional($translations->firstWhere('code', 'meta_title'))->value,
                    'meta_description' => optional($translations->firstWhere('code', 'meta_description'))->value,
                    'text' => optional($translations->firstWhere('code', 'text'))->value,
                ];
            });

        return view('admin.categories.edit', compact('category', 'categoryData'));
    }

    public function destroy(Category $category)
    {
        $category->articles()->detach();
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category successfully deleted.');
    }

    private function getRules($id = false)
    {
        $rules = [];

        foreach (config('langs') as $lang => $flag) {
            $rules['name.' . $lang] = ['nullable', 'string', 'max:255'];
            $rules['meta_title.' . $lang] = ['nullable', 'string'];
            $rules['meta_description.' . $lang] = ['nullable', 'string'];
            $rules['text.' . $lang] = ['nullable', 'string'];
        }

        // require at least one language name
        $rules['name'] = ['array', 'required_without_all:' . implode(',', array_map(fn($l) => 'name.' . $l, array_keys(config('langs'))))];

        return $rules;
    }
}
