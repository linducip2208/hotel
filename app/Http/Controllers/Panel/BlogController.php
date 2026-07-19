<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = BlogPost::with('category')
            ->where('property_id', app('current_property')->id)
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('panel.blog.index', compact('posts'));
    }

    public function create()
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('panel.blog.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:blog_posts,slug',
            'category_id' => 'nullable|exists:blog_categories,id',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $data['property_id'] = app('current_property')->id;
        $data['author_id'] = auth()->id();

        if ($request->boolean('is_published') && ! $request->filled('published_at')) {
            $data['published_at'] = now();
        }

        $post = BlogPost::create($data);

        if ($post->is_published && class_exists(\App\Services\Seo\IndexNowService::class)) {
            try {
                (new \App\Services\Seo\IndexNowService)->submitSingle(route('blog.show', $post->slug));
            } catch (\Throwable) {
            }
        }

        return redirect()->route('panel.blog.index')->with('success', 'Artikel diterbitkan.');
    }

    public function edit(string $id)
    {
        $post = BlogPost::findOrFail($id);
        $categories = BlogCategory::orderBy('name')->get();
        return view('panel.blog.create', compact('post', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        $post = BlogPost::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:blog_posts,slug,' . $post->id,
            'category_id' => 'nullable|exists:blog_categories,id',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        if ($request->boolean('is_published') && ! $request->filled('published_at')) {
            $data['published_at'] = $post->published_at ?? now();
        }

        if (! $request->boolean('is_published')) {
            $data['published_at'] = null;
        }

        $data['author_id'] = auth()->id();
        $post->update($data);

        if ($post->is_published && class_exists(\App\Services\Seo\IndexNowService::class)) {
            try {
                (new \App\Services\Seo\IndexNowService)->submitSingle(route('blog.show', $post->slug));
            } catch (\Throwable) {
            }
        }

        return redirect()->route('panel.blog.index')->with('success', 'Artikel diperbarui.');
    }

    public function destroy(string $id)
    {
        $post = BlogPost::findOrFail($id);
        $post->delete();

        return redirect()->route('panel.blog.index')->with('success', 'Artikel dihapus.');
    }
}
