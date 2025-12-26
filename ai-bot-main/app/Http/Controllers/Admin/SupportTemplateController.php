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
        $templates = SupportTemplate::orderBy('sort_order')->orderBy('title')->paginate(20);
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
        $request->validate([
            'title' => 'required|string|max:255',
            'text' => 'required|string|max:4000',
            'lang' => 'required|string|max:5',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        SupportTemplate::create($request->all());

        return redirect()->route('admin.support-templates.index')
            ->with('success', 'Шаблон успешно создан');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $template = SupportTemplate::findOrFail($id);
        return view('admin.support-templates.edit', compact('template'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'text' => 'required|string|max:4000',
            'lang' => 'required|string|max:5',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $template = SupportTemplate::findOrFail($id);
        $template->update($request->all());

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
        
        return response()->json([
            'success' => true,
            'templates' => $templates
        ]);
    }
}

