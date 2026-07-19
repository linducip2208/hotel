@extends('panel.layout')

@section('title', 'Blog')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Blog</h2>
        <p class="text-sm text-slate-500 mt-0.5">Kelola artikel blog — publikasikan konten, optimasi SEO, tarik trafik organik.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.blog.categories.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 hover:text-indigo-600 bg-white border border-slate-200 hover:border-indigo-200 rounded-xl px-4 py-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            Kategori
        </a>
        <a href="{{ route('panel.blog.create') }}" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl px-4 py-2.5 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Artikel
        </a>
    </div>
</div>

{{-- Search & Filters --}}
<div class="bg-white border border-slate-200 rounded-2xl p-4 mb-6">
    <form action="{{ route('panel.blog.index') }}" method="GET" class="flex items-center gap-3">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari artikel..."
                   class="w-full pl-9 pr-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
        </div>
        <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium px-4 py-2 rounded-xl transition-colors">Cari</button>
        @if(request('search'))
            <a href="{{ route('panel.blog.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-[0.06em]">Judul</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-[0.06em]">Kategori</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-[0.06em]">Status</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-[0.06em]">Tanggal Publikasi</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-[0.06em]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($posts as $post)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="font-medium text-slate-900 text-sm max-w-xs truncate">{{ $post->title }}</div>
                        @if($post->excerpt)
                            <div class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $post->excerpt }}</div>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        @if($post->category)
                            <span class="inline-block text-xs font-medium text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full">{{ $post->category->name }}</span>
                        @else
                            <span class="text-xs text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        @if($post->is_published)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Published
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Draft
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-sm text-slate-600">
                        {{ $post->published_at ? $post->published_at->format('d M Y H:i') : '—' }}
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('panel.blog.edit', $post->id) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </a>
                            <form action="{{ route('panel.blog.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Hapus artikel ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-16 text-center text-slate-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        <p class="text-sm">Belum ada artikel.</p>
                        <a href="{{ route('panel.blog.create') }}" class="inline-block mt-2 text-indigo-600 text-sm font-medium hover:underline">Buat artikel pertama →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($posts->hasPages())
    <div class="border-t border-slate-200 px-5 py-3">
        {{ $posts->links() }}
    </div>
    @endif
</div>
@endsection
