@extends('panel.layout')
@section('title', 'Detail Agen Travel')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.sales.travel-agents.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Detail Agen Travel</h1>
        <p class="text-sm text-gray-500 mt-0.5">Informasi agen, alotmen & reservasi</p>
    </div>
</div>

@php
    $agent = $agent ?? null;
    if (!$agent) {
        echo '<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-12 text-center text-gray-500">Data tidak ditemukan.</div>';
        return;
    }
@endphp

{{-- Header --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white text-lg font-bold shadow-md shadow-indigo-500/25">
                {{ strtoupper(substr($agent->name, 0, 2)) }}
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-gray-900">{{ $agent->name }}</h2>
                    @if($agent->iata_code)
                    <span class="text-xs font-mono font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md">{{ $agent->iata_code }}</span>
                    @endif
                </div>
                <div class="mt-1">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-0.5 rounded-full {{ ($agent->is_active ?? true) ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ ($agent->is_active ?? true) ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                        {{ ($agent->is_active ?? true) ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('panel.sales.travel-agents.edit', $agent->id) }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 px-4 py-2.5 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
        </div>
    </div>
</div>

{{-- Info Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ number_format($agent->default_commission_pct ?? 0, 1) }}%</p>
        <p class="text-xs text-gray-500 mt-0.5">Komisi</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900 tabular-nums">Rp {{ number_format($agent->credit_limit ?? 0, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Batas Kredit</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ $agent->reservations_count ?? $agent->total_reservations ?? 0 }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Total Reservasi</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ $agent->allotments_count ?? $agent->total_allotments ?? 0 }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Total Alotmen</p>
    </div>
</div>

{{-- Alotmen --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-violet-100 flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
        </div>
        <h2 class="text-sm font-bold text-gray-900">Alotmen</h2>
    </div>
    @if(($agent->allotments ?? collect())->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Periode</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe Kamar</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Diblok</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Terpakai</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Sisa</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($agent->allotments as $al)
                @php $remaining = $al->remaining ?? (($al->rooms_blocked ?? 0) - ($al->rooms_picked_up ?? 0)); @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 text-sm text-gray-700">
                        {{ $al->from_date?->format('d M') ?? '—' }} – {{ $al->to_date?->format('d M Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-800">{{ $al->roomType?->name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-right text-sm text-gray-700 tabular-nums">{{ $al->rooms_blocked ?? 0 }}</td>
                    <td class="px-4 py-3.5 text-right text-sm text-indigo-600 font-medium tabular-nums">{{ $al->rooms_picked_up ?? 0 }}</td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm font-bold {{ $remaining <= 0 ? 'text-red-500' : ($remaining <= 2 ? 'text-amber-600' : 'text-emerald-700') }} tabular-nums">
                            {{ $remaining }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="flex flex-col items-center justify-center py-16">
        <div class="w-12 h-12 rounded-2xl bg-violet-50 flex items-center justify-center mb-3">
            <svg class="w-6 h-6 text-violet-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
        </div>
        <p class="text-sm font-medium text-gray-600">Belum ada alotmen</p>
        <p class="text-xs text-gray-400 mt-1">Alotmen untuk agen ini akan muncul di sini.</p>
    </div>
    @endif
</div>

{{-- Reservasi Terbaru --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <h2 class="text-sm font-bold text-gray-900">Reservasi Terbaru</h2>
    </div>
    @if(($agent->recentReservations ?? collect())->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Booking Ref</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tamu</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Check-in</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Check-out</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($agent->recentReservations as $r)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <a href="{{ route('panel.fo.reservations.show', $r->id) }}" class="text-sm font-mono font-semibold text-indigo-600 hover:text-indigo-700">
                            {{ $r->ref ?? '#' . $r->id }}
                        </a>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-800">{{ $r->primaryGuest?->full_name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">{{ $r->check_in?->format('d M Y') ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">{{ $r->check_out?->format('d M Y') ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded-full">{{ $r->status_label ?? $r->status ?? '—' }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="flex flex-col items-center justify-center py-16">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center mb-3">
            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <p class="text-sm font-medium text-gray-600">Belum ada reservasi</p>
        <p class="text-xs text-gray-400 mt-1">Reservasi dari agen ini akan muncul di sini.</p>
    </div>
    @endif
</div>

@endsection
