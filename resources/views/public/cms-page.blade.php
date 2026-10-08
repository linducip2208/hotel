@extends('public.layout')
@section('title', $page->meta_title ?: $page->title)
@section('description', $page->meta_description ?: ($page->excerpt ?: $page->title))

@section('content')
<div class="pt-28 lg:pt-36 pb-16">
    <div class="max-w-3xl mx-auto px-4 lg:px-8">
        <nav class="text-xs text-slate-400 mb-6">
            <a href="/" class="hover:text-indigo-500">Beranda</a>
            <span class="mx-1.5">/</span>
            <span class="text-slate-600">{{ $page->title }}</span>
        </nav>

        <h1 class="font-display text-3xl lg:text-4xl font-bold text-slate-900 mb-4">{{ $page->title }}</h1>

        @if($page->excerpt)
        <p class="text-slate-500 text-lg leading-relaxed mb-8">{{ $page->excerpt }}</p>
        @endif

        <article class="prose prose-slate max-w-none prose-headings:font-display prose-a:text-indigo-600">
            {!! $page->content !!}
        </article>
    </div>
</div>
@endsection
