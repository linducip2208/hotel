@extends('admin.layout')
@section('title', 'Knowledge Base')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Knowledge Base</h1>
    <p class="text-sm text-gray-500 mt-1">Artikel bantuan internal</p>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Judul</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kategori</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Views</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($articles as $a)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-medium text-gray-900">{{ $a->title }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $a->category }}</td>
                <td class="px-4 py-3 text-center">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $a->is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $a->is_published ? 'Published' : 'Draft' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right text-gray-500">{{ $a->views_count }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="py-12 text-center text-sm text-gray-400">Belum ada artikel KB.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if ($articles->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $articles->links() }}</div>
    @endif
</div>
@endsection
