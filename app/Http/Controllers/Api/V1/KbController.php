<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use Illuminate\Http\Request;

class KbController extends Controller
{
    public function index(Request $request)
    {
        $property = $request->user()->property;
        $query = KbArticle::where('property_id', $property->id)
            ->with('author')
            ->orderByDesc('updated_at');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                  ->orWhere('content', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->tag) {
            $query->whereJsonContains('tags', $request->tag);
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:250',
            'content'    => 'required|string',
            'category'   => 'required|string',
            'tags'       => 'nullable|array',
            'is_public'  => 'boolean',
        ]);

        $article = KbArticle::create([
            ...$data,
            'property_id'    => $request->user()->property->id,
            'author_user_id' => $request->user()->id,
            'is_published'   => false,
        ]);

        return response()->json($article, 201);
    }

    public function show(Request $request, int $id)
    {
        $property = $request->user()->property;
        $article = KbArticle::where('property_id', $property->id)
            ->with('author')
            ->findOrFail($id);
        return response()->json($article);
    }

    public function update(Request $request, int $id)
    {
        $property = $request->user()->property;
        $article = KbArticle::where('property_id', $property->id)->findOrFail($id);
        $article->update($request->only(['title', 'content', 'category', 'tags', 'is_public', 'is_published']));
        return response()->json($article);
    }

    public function destroy(Request $request, int $id)
    {
        $property = $request->user()->property;
        $article = KbArticle::where('property_id', $property->id)->findOrFail($id);
        $article->delete();
        return response()->noContent();
    }
}
