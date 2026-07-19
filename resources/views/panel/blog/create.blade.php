@extends('panel.layout')

@section('title', isset($post) ? 'Edit Artikel' : 'Tambah Artikel')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var title = document.querySelector('input[name="title"]');
    var slug = document.querySelector('input[name="slug"]');
    if (title && slug) {
        title.addEventListener('keyup', function() {
            if (!slug.dataset.manual) {
                slug.value = title.value
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        });
        slug.addEventListener('input', function() {
            slug.dataset.manual = '1';
        });
    }
});
</script>
@endpush

@section('content')

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900">{{ isset($post) ? 'Edit Artikel' : 'Tambah Artikel' }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ isset($post) ? 'Perbarui konten dan pengaturan artikel.' : 'Buat artikel baru untuk blog.' }}</p>
        </div>
        <a href="{{ route('panel.blog.index') }}" class="text-sm text-slate-500 hover:text-indigo-600 transition-colors">&larr; Kembali</a>
    </div>

    <form action="{{ isset($post) ? route('panel.blog.update', $post->id) : route('panel.blog.store') }}" method="POST" class="space-y-6">
        @csrf
        @if(isset($post))
            @method('PUT')
        @endif

        {{-- Basic info --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-6">
            <h3 class="text-sm font-bold text-slate-900 mb-4">Informasi Dasar</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Judul <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $post->title ?? '') }}"
                           class="w-full px-3 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           required maxlength="255">
                    @error('title') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Slug <span class="text-rose-500">*</span></label>
                    <input type="text" name="slug" value="{{ old('slug', $post->slug ?? '') }}"
                           class="w-full px-3 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none font-mono text-slate-600"
                           required maxlength="255">
                    <p class="text-xs text-slate-400 mt-1">Otomatis dari judul, atau isi manual.</p>
                    @error('slug') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kategori</label>
                    <select name="category_id" class="w-full px-3 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none bg-white">
                        <option value="">— Tanpa Kategori —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $post->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Konten <span class="text-rose-500">*</span></label>
                    <textarea name="content" rows="20"
                              class="w-full px-3 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none font-mono"
                              required>{{ old('content', $post->content ?? '') }}</textarea>
                    <p class="text-xs text-slate-400 mt-1">HTML diperbolehkan. Gunakan &lt;h2&gt;, &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;img&gt;.</p>
                    @error('content') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kutipan / Excerpt</label>
                    <textarea name="excerpt" rows="3" maxlength="500"
                              class="w-full px-3 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                              placeholder="Ringkasan singkat (maks 500 karakter)">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                    <p class="text-xs text-slate-400 mt-1">Tampil di card blog dan meta description jika tidak diisi.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Featured Image URL</label>
                    <input type="text" name="featured_image" value="{{ old('featured_image', $post->featured_image ?? '') }}"
                           class="w-full px-3 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="/images/blog/artikel-saya.jpg" maxlength="500">
                </div>
            </div>
        </div>

        {{-- SEO --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-6">
            <h3 class="text-sm font-bold text-slate-900 mb-4">SEO Meta</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title ?? '') }}" maxlength="255"
                           class="w-full px-3 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="Biarkan kosong untuk pakai judul artikel">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Meta Description</label>
                    <textarea name="meta_description" rows="2" maxlength="500"
                              class="w-full px-3 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                              placeholder="Biarkan kosong untuk pakai excerpt">{{ old('meta_description', $post->meta_description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Publishing --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-6">
            <h3 class="text-sm font-bold text-slate-900 mb-4">Publikasi</h3>

            <div class="space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" {{ old('is_published', $post->is_published ?? false) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-slate-700">Terbitkan artikel</span>
                </label>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Publikasi</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at', isset($post) && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}"
                           class="w-full max-w-xs px-3 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <p class="text-xs text-slate-400 mt-1">Kosongkan untuk auto-set ke tanggal sekarang saat publish.</p>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-3 justify-end">
            <a href="{{ route('panel.blog.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-800 px-4 py-2.5">Batal</a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl px-6 py-2.5 transition-colors shadow-sm">
                {{ isset($post) ? 'Simpan Perubahan' : 'Terbitkan Artikel' }}
            </button>
        </div>
    </form>
</div>
@endsection
