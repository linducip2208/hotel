<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogCategoryController extends Controller
{
    public function index()
    {
        $categories = BlogCategory::withCount(['posts' => fn ($q) => $q->where('is_published', true)])
            ->orderBy('name')
            ->get();

        return view('panel.blog.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name',
        ]);

        $data['slug'] = Str::slug($data['name']);

        BlogCategory::create($data);

        return redirect()->route('panel.blog.categories.index')->with('success', 'Kategori ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $category = BlogCategory::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $category->update($data);

        return redirect()->route('panel.blog.categories.index')->with('success', 'Kategori diperbarui.');
    }

    public function destroy(string $id)
    {
        $category = BlogCategory::findOrFail($id);

        if ($category->posts()->count() > 0) {
            return redirect()->route('panel.blog.categories.index')->with('error', 'Kategori tidak bisa dihapus karena masih memiliki artikel.');
        }

        $category->delete();

        return redirect()->route('panel.blog.categories.index')->with('success', 'Kategori dihapus.');
    }
}
