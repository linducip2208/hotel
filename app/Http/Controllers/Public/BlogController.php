<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = BlogPost::with('category', 'author')
            ->published()
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        $categories = BlogCategory::withCount(['posts' => fn ($q) => $q->published()])->get();
        $recentPosts = BlogPost::published()->orderBy('published_at', 'desc')->take(5)->get();

        $seoTitle = 'Blog — ' . config('app.name');
        $seoDescription = 'Tips perhotelan, panduan wisata, review hotel, dan berita terbaru seputar dunia perhotelan Indonesia.';

        return view('blog.index', compact('posts', 'categories', 'recentPosts', 'seoTitle', 'seoDescription'));
    }

    public function show(string $slug)
    {
        $post = BlogPost::with('category', 'author')->where('slug', $slug)->published()->firstOrFail();
        $relatedPosts = BlogPost::published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->take(3)->get();
        $recentPosts = BlogPost::published()->latest('published_at')->take(5)->get();
        $categories = BlogCategory::withCount(['posts' => fn ($q) => $q->published()])->get();

        $seoTitle = ($post->meta_title ?: $post->title) . ' — ' . config('app.name');
        $seoDescription = $post->meta_description ?: $post->excerpt;

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => $post->excerpt,
            'image' => $post->featured_image ? asset($post->featured_image) : null,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
            'author' => ['@type' => 'Person', 'name' => $post->author?->name ?? config('app.name')],
            'publisher' => ['@type' => 'Organization', 'name' => config('app.name')],
        ];

        return view('blog.show', compact('post', 'relatedPosts', 'recentPosts', 'categories', 'seoTitle', 'seoDescription', 'schema'));
    }

    public function category(string $slug)
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();
        $posts = BlogPost::with('author')->published()
            ->where('category_id', $category->id)
            ->orderBy('published_at', 'desc')
            ->paginate(12);
        $categories = BlogCategory::withCount(['posts' => fn ($q) => $q->published()])->get();
        $recentPosts = BlogPost::published()->latest('published_at')->take(5)->get();

        $seoTitle = "Kategori: {$category->name} — Blog " . config('app.name');

        return view('blog.index', compact('posts', 'categories', 'recentPosts', 'seoTitle', 'category'));
    }

    public function feed()
    {
        $posts = BlogPost::published()->orderBy('published_at', 'desc')->take(20)->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        $xml .= '<channel><title>' . e(config('app.name') . ' Blog') . '</title>';
        $xml .= '<link>' . e(route('blog.index')) . '</link>';
        $xml .= '<description>Tips perhotelan dan panduan wisata Indonesia</description>';
        $xml .= '<language>id</language>';
        $xml .= '<atom:link href="' . e(route('blog.feed')) . '" rel="self" type="application/rss+xml"/>';

        foreach ($posts as $post) {
            $xml .= '<item>';
            $xml .= '<title>' . e($post->title) . '</title>';
            $xml .= '<link>' . e(route('blog.show', $post->slug)) . '</link>';
            $xml .= '<guid>' . e(route('blog.show', $post->slug)) . '</guid>';
            $xml .= '<description>' . e($post->excerpt) . '</description>';
            $xml .= '<pubDate>' . $post->published_at->toRfc2822String() . '</pubDate>';
            $xml .= '</item>';
        }

        $xml .= '</channel></rss>';

        return response($xml, 200, ['Content-Type' => 'application/rss+xml']);
    }
}
