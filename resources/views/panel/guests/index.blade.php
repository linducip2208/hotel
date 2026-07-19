@extends('panel.layout')
@section('title', 'Tamu')
@section('content')

{{-- Page Header --}}
<div class="mb-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tamu</h1>
            <p class="text-sm text-gray-500 mt-1">Direktori tamu yang pernah menginap</p>
        </div>
        {{-- Search --}}
        <form method="GET" class="flex items-center gap-2">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="search" name="q" placeholder="Cari nama, email..."
                       value="{{ request('q') }}"
                       class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 w-64 transition">
            </div>
            @if (request('q'))
                <a href="{{ request()->url() }}"
                   class="border border-gray-200 hover:bg-gray-50 text-gray-600 px-3 py-2 rounded-xl text-sm transition">
                    Reset
                </a>
            @endif
        </form>
    </div>
</div>

{{-- Table Card --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">

    @if (request('q'))
        <div class="px-5 py-3 bg-primary-50 border-b border-primary-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm text-primary-700">
                Hasil pencarian untuk <strong>"{{ request('q') }}"</strong>
                — {{ $guests->total() }} tamu ditemukan
            </span>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Telepon</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Negara</th>
                    <th class="px-5 py-3.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse ($guests as $g)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-primary-600">{{ strtoupper(substr($g->full_name ?? '?', 0, 1)) }}</span>
                            </div>
                            <a href="{{ route('panel.guests.show', $g->id) }}"
                               class="font-medium text-gray-900 hover:text-primary-600 transition">
                                {{ $g->full_name }}
                            </a>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-gray-500 text-xs">{{ $g->email ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-gray-500 text-xs">{{ $g->phone ?? '—' }}</td>
                    <td class="px-5 py-3.5">
                        @if ($g->country)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                {{ $g->country }}
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('panel.guests.show', $g->id) }}"
                           class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-800 text-xs font-medium transition">
                            Detail
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700">
                                    {{ request('q') ? 'Tamu tidak ditemukan' : 'Belum ada data tamu' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ request('q') ? 'Coba kata kunci lain' : 'Data tamu akan muncul setelah ada reservasi' }}
                                </p>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if ($guests->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
            {{ $guests->links() }}
        </div>
    @endif
</div>

@endsection
