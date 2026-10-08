@extends('panel.layout')
@section('title', 'Kata Kunci Pencarian')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Kata Kunci Pencarian</h1>
    <p class="text-sm text-gray-500 mt-0.5">Analisis apa yang dicari calon tamu di booking engine & situs Anda</p>
</div>

{{-- Top keywords --}}
@if($top->isNotEmpty())
<div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5 mb-6">
    <h2 class="text-sm font-semibold text-gray-700 mb-4">Kata Kunci Terpopuler</h2>
    <div class="flex flex-wrap gap-2">
        @foreach($top as $k)
        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
            {{ $k->keyword }}
            <span class="text-xs text-indigo-400 font-bold">{{ $k->hits }}</span>
        </span>
        @endforeach
    </div>
</div>
@endif

{{-- Filters --}}
<div class="mb-4 flex flex-wrap gap-2">
    @foreach(['all' => 'Semua', 'booking' => 'Booking Engine', 'site' => 'Situs', 'panel' => 'Panel'] as $val => $label)
    <a href="{{ route('panel.marketing.search-keywords.index', ['source' => $val]) }}"
       class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors {{ $source === $val ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">{{ $label }}</a>
    @endforeach
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kata Kunci</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Sumber</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Pencarian</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Hasil</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Terakhir</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($keywords as $k)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3 font-medium text-gray-900">{{ $k->keyword }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $k->source }}</span>
                    </td>
                    <td class="px-4 py-3 text-center font-semibold text-indigo-600">{{ $k->hits }}</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $k->results_count }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $k->last_searched_at?->diffForHumans() ?? '-' }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('panel.marketing.search-keywords.destroy', $k->id) }}" onsubmit="return confirm('Hapus kata kunci ini?')" class="inline">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">Belum ada data pencarian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">
        {{ $keywords->links() }}
    </div>
</div>

@endsection
