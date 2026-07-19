<?php

namespace App\Http\Controllers\Panel\Kb;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KbController extends Controller
{
    public function index()
    {
        $articles = KbArticle::where('property_id', app('current_property')->id)->orWhereNull('property_id')->paginate(50);
        return view('panel.kb.index', compact('articles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'category' => 'nullable|string',
            'body' => 'required|string',
            'locale' => 'nullable|string',
            'is_published' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
        ]);
        KbArticle::create($data + [
            'property_id' => app('current_property')->id,
            'slug' => Str::slug($data['title']).'-'.Str::random(4),
            'author_user_id' => $request->user()?->id,
        ]);
        return back();
    }
}
