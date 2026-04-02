<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Template;
use App\Models\Category;
use App\Models\TemplateType;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::all();
        $categories = Category::all();
        $types = TemplateType::all();
        
        return view('central.admin.templates', compact('templates', 'categories', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'type' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('template'), $filename);
            $path = $filename;
        }

        Template::create([
            'name' => $request->name,
            'category' => $request->category,
            'type' => $request->type,
            'image' => $path
        ]);

        return back()->with('success', 'Template created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'type' => 'required|string',
        ]);

        $template = Template::findOrFail($id);
        $template->update([
            'name' => $request->name,
            'category' => $request->category,
            'type' => $request->type,
        ]);

        return back()->with('success', 'Template updated successfully');
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name'
        ]);

        Category::create([
            'name' => $request->name
        ]);

        return back()->with('success', 'Category added successfully');
    }

    public function storeType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:template_types,name'
        ]);

        TemplateType::create([
            'name' => $request->name
        ]);

        return back()->with('success', 'Type added successfully');
    }
}
