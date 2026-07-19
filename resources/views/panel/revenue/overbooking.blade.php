@extends('panel.layout')
@section('title', 'Overbooking Optimization')
@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Overbooking Optimization</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kalkulasi risiko overbooking berbasis data historis — hindari kerugian dari no-show & pembatalan</p>
    </div>
    <form method="GET" class="flex items-center gap-2">
        <input type="date" name="date" value="{{ $date->toDateString() }}"
            class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
        <button type="submit" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-3.5 py-2 rounded-xl transition-colors shadow-sm shadow-indigo-500/25">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Lihat
        </button>
    </form>
</div>

{{-- Stats cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900 tabular-nums">{{ $stats['total_rooms'] }}</div>
        <div class="text-xs text-gray-500 mt-0.5">Total Kamar</div>
    </div>

    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900 tabular-nums">{{ $stats['total_booked'] }}</div>
        <div class="text-xs text-gray-500 mt-0.5">Terpesan</div>
    </div>

    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900 tabular-nums">{{ $stats['total_blocked'] }}</div>
        <div class="text-xs text-gray-500 mt-0.5">Diblokir</div>
    </div>

    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900 tabular-nums">{{ $stats['overall_occupancy'] }}%</div>
        <div class="text-xs text-gray-500 mt-0.5">Occupancy Overall</div>
        <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
            <div class="h-1.5 rounded-full @if($stats['overall_occupancy'] >= 90) bg-rose-500 @elseif($stats['overall_occupancy'] >= 75) bg-amber-400 @else bg-emerald-500 @endif"
                 style="width: {{ min($stats['overall_occupancy'], 100) }}%"></div>
        </div>
    </div>
</div>

{{-- Alert banner --}}
@if($criticalCount > 0 || $highCount > 0)
<div class="mb-6 bg-rose-50 border border-rose-200 rounded-2xl p-4 flex items-start gap-3">
    <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
    <div>
        <p class="font-semibold text-rose-800 text-sm">Peringatan Overbooking — {{ $date->translatedFormat('d F Y') }}</p>
        <p class="text-xs text-rose-600 mt-0.5">{{ $criticalCount }} tipe kamar <strong>critical</strong>, {{ $highCount }} tipe kamar <strong>high risk</strong>. Lihat mitigasi di bawah.</p>
    </div>
</div>
@endif

{{-- Risk grid --}}
<h2 class="text-lg font-bold text-gray-900 mb-4">Risiko per Tipe Kamar</h2>
<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
    @foreach($risks as $risk)
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 @if($risk['risk_level'] === 'critical') ring-2 ring-rose-300 @endif">
        <div class="flex items-start justify-between mb-3">
            <div>
                <h3 class="font-semibold text-gray-900 text-sm">{{ $risk['room_type'] }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">{{ $risk['booked'] }}/{{ $risk['total'] }} terpesan</p>
            </div>
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold
                @switch($risk['risk_level'])
                    @case('critical') bg-rose-100 text-rose-700 @break
                    @case('high') bg-amber-100 text-amber-700 @break
                    @case('medium') bg-yellow-100 text-yellow-700 @break
                    @default bg-emerald-100 text-emerald-700
                @endswitch
            ">{{ strtoupper($risk['risk_level']) }}</span>
        </div>

        {{-- Occupancy bar --}}
        <div class="mb-3">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span>Occupancy</span>
                <span class="font-semibold">{{ $risk['occupancy_pct'] }}%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="h-2 rounded-full @if($risk['risk_level'] === 'critical') bg-rose-500 @elseif($risk['risk_level'] === 'high') bg-amber-400 @elseif($risk['risk_level'] === 'medium') bg-yellow-400 @else bg-emerald-500 @endif"
                     style="width: {{ min($risk['occupancy_pct'], 100) }}%"></div>
            </div>
        </div>

        <div class="flex items-center justify-between text-xs">
            <span class="text-gray-500">Safe Overbooking:</span>
            <span class="font-bold font-mono text-gray-900">{{ $risk['safe_overbook'] }} kamar</span>
        </div>
        <div class="flex items-center justify-between text-xs mt-1">
            <span class="text-gray-500">Diblokir:</span>
            <span class="font-mono text-gray-700">{{ $risk['blocked'] }}</span>
        </div>
    </div>
    @endforeach

    @if(empty($risks))
    <div class="md:col-span-3 bg-white rounded-2xl shadow-card border border-gray-100 p-12 text-center text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Tidak ada data inventory untuk tanggal ini.
    </div>
    @endif
</div>

{{-- Mitigation suggestions --}}
@if(!empty($mitigations))
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-6">
    <div class="p-5 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Rekomendasi Mitigasi</h2>
        <p class="text-xs text-gray-500 mt-0.5">Tindakan yang disarankan untuk mengurangi risiko overbooking</p>
    </div>
    <div class="p-5">
        <ul class="space-y-2">
            @foreach($mitigations as $action)
            <li class="flex items-start gap-3 text-sm">
                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-gray-700">{{ $action }}</span>
            </li>
            @endforeach
        </ul>
    </div>
</div>
@endif

{{-- Methodology note --}}
<div class="bg-slate-50 rounded-2xl border border-slate-200 p-5">
    <h3 class="font-semibold text-gray-700 text-sm mb-2">Metodologi Kalkulasi</h3>
    <p class="text-xs text-gray-500 leading-relaxed">
        Safe overbooking dihitung berdasarkan <strong>no-show rate 90 hari terakhir</strong> ditambah <strong>50% cancellation rate</strong> untuk hari yang sama dalam seminggu (30 hari terakhir).
        Rumus: <code class="bg-slate-200 px-1 py-0.5 rounded text-xs">floor(total_kamar × (no_show_rate + cancellation_rate × 0.5))</code>.
        Risiko dihitung dari occupancy saat ini: &lt;75% = low, 75-89% = medium, 90-99% = high, &ge;100% = critical.
    </p>
</div>

@endsection
