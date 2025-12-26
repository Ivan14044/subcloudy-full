<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTemplate;
use Illuminate\Http\Request;

class SupportTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = SupportTemplate::with('translations')->orderBy('sort_order')->paginate(20);
        return view('admin.support-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.support-templates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ];

        foreach (config('langs') as $lang => $flag) {
            foreach (SupportTemplate::TRANSLATION_FIELDS as $field) {
                $rules[$field . '.' . $lang] = 'nullable|string|max:4000';
            }
        }

        $request->validate($rules);

        $template = SupportTemplate::create($request->only(['is_active', 'sort_order']));
        $template->saveTranslation($request->all());

        return redirect()->route('admin.support-templates.index')
            ->with('success', 'Шаблон успешно создан');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $template = SupportTemplate::with('translations')->findOrFail($id);
        $templateData = $template->translations->groupBy('locale')->map(function ($translations) {
            return $translations->pluck('value', 'code')->toArray();
        });

        return view('admin.support-templates.edit', compact('template', 'templateData'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ];

        foreach (config('langs') as $lang => $flag) {
            foreach (SupportTemplate::TRANSLATION_FIELDS as $field) {
                $rules[$field . '.' . $lang] = 'nullable|string|max:4000';
            }
        }

        $request->validate($rules);

        $template = SupportTemplate::findOrFail($id);
        $template->update($request->only(['is_active', 'sort_order']));
        $template->saveTranslation($request->all());

        return redirect()->route('admin.support-templates.index')
            ->with('success', 'Шаблон успешно обновлен');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $template = SupportTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('admin.support-templates.index')
            ->with('success', 'Шаблон успешно удален');
    }

    /**
     * Get templates as JSON for AJAX
     */
    public function getTemplates(Request $request)
    {
        $lang = $request->input('lang', 'ru');
        $templates = SupportTemplate::getActive($lang);
        
        $formattedTemplates = $templates->map(function($t) use ($lang) {
            return [
                'id' => $t->id,
                'title' => $t->getTranslation('title', $lang),
                'text' => $t->getTranslation('text', $lang)
            ];
        });

        return response()->json([
            'success' => true,
            'templates' => $formattedTemplates
        ]);
    }
}

