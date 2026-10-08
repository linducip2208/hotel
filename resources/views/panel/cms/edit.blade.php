@extends('panel.layout')
@section('title', $page ? 'Edit Halaman' : 'Halaman Baru')
@section('content')

<div class="max-w-4xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $page ? 'Edit Halaman' : 'Halaman Baru' }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">Buat atau edit halaman statis website</p>
    </div>

    <form method="POST" action="{{ $page ? route('panel.cms.update', $page->id) : route('panel.cms.store') }}"
          class="bg-white rounded-2xl border border-gray-100 shadow-card p-6 space-y-5">
        @csrf
        @if($page) @method('PUT') @endif

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Judul Halaman</label>
            <input type="text" name="title" value="{{ old('title', $page->title ?? '') }}" required
                   class="w-full rounded-xl border-gray-200 text-sm px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Slug (URL)</label>
                <input type="text" name="slug" value="{{ old('slug', $page->slug ?? '') }}" placeholder="auto dari judul"
                       class="w-full rounded-xl border-gray-200 text-sm px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500 font-mono text-xs">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Urutan Footer</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order ?? 0) }}"
                       class="w-full rounded-xl border-gray-200 text-sm px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Meta Title (SEO)</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title ?? '') }}"
                       class="w-full rounded-xl border-gray-200 text-sm px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Meta Description</label>
                <input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description ?? '') }}"
                       class="w-full rounded-xl border-gray-200 text-sm px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Ringkasan (opsional)</label>
            <textarea name="excerpt" rows="2" class="w-full rounded-xl border-gray-200 text-sm px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">{{ old('excerpt', $page->excerpt ?? '') }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Konten (HTML)</label>
            <textarea name="content" required rows="14" class="w-full rounded-xl border-gray-200 text-sm px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500 font-mono">{{ old('content', $page->content ?? '') }}</textarea>
            <p class="text-xs text-gray-400 mt-1">Mendukung tag HTML: &lt;p&gt;, &lt;h2&gt;, &lt;ul&gt;, &lt;img&gt;, dll.</p>
        </div>

        <div class="flex items-center gap-6">
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', $page->is_published ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                Terbitkan
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="show_in_footer" value="1" {{ old('show_in_footer', $page->show_in_footer ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                Tampilkan di footer website
            </label>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors">Simpan</button>
            <a href="{{ route('panel.cms.index') }}" class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5">Batal</a>
        </div>
    </form>
</div>

@endsection
