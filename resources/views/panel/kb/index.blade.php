@extends('panel.layout')
@section('title', 'Knowledge Base')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Knowledge Base</h1>
    <p class="text-sm text-gray-500 mt-0.5">Internal SOPs and guest-facing help articles</p>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Article list --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Articles</h2>
                <span class="text-xs text-gray-400">{{ $articles->count() }} articles</span>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse ($articles as $a)
                <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/60 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-900 truncate">{{ $a->title }}</span>
                            @if ($a->is_public)
                            <span class="text-xs font-medium bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full shrink-0">Public</span>
                            @else
                            <span class="text-xs font-medium bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full shrink-0">Internal</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 mt-0.5 text-xs text-gray-400">
                            @if ($a->category)
                            <span class="capitalize">{{ $a->category }}</span>
                            @endif
                            <span>{{ $a->views_count }} views</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <div class="w-2 h-2 rounded-full {{ $a->is_published ?? false ? 'bg-emerald-500' : 'bg-gray-300' }}"></div>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                    <svg class="w-8 h-8 mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <p class="text-sm text-gray-500">No articles yet</p>
                </div>
                @endforelse
            </div>
            @if (method_exists($articles, 'hasPages') && $articles->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $articles->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- New article form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">New Article</h2>
        </div>
        <form method="POST" action="{{ route('panel.kb.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="How to handle early check-in…"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Category</label>
                <input type="text" name="category" value="{{ old('category') }}" placeholder="e.g. Front Office"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Body <span class="text-red-500">*</span></label>
                <textarea name="body" rows="7" required placeholder="Markdown supported…"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none">{{ old('body') }}</textarea>
            </div>
            <div class="space-y-2 pt-1">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published')) class="rounded text-primary-600 border-gray-300 focus:ring-primary-400">
                    Published
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="is_public" value="1" @checked(old('is_public')) class="rounded text-primary-600 border-gray-300 focus:ring-primary-400">
                    Public (guests can read)
                </label>
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Create Article
            </button>
        </form>
    </div>

</div>

@endsection
