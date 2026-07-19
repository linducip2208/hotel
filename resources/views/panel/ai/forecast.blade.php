@extends('panel.layout')
@section('title', 'AI Demand Forecast')
@section('content')

@php
    $days = $forecast['days'] ?? [];
    $note = $forecast['note'] ?? null;
@endphp

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-br from-emerald-50 to-teal-50 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-md">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        </div>
        <div>
            <h1 class="text-base font-bold text-slate-900">AI Demand Forecast</h1>
            <p class="text-xs text-slate-500">Prediksi okupansi 30 hari ke depan berdasarkan AI + data historis.</p>
        </div>
    </div>

    @if($note)
        <div class="m-6 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-4 text-sm">
            <p class="font-semibold mb-1">⚠️ Catatan</p>
            <p class="text-xs">{{ $note }}</p>
        </div>
    @endif

    {{-- Historical 14d --}}
    <div class="p-6 border-b border-slate-100">
        <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">14 hari terakhir (historis)</p>
        <div class="flex items-end gap-1 h-24">
            @foreach($historical as $h)
                @php $col = $h['occ'] >= 80 ? 'emerald' : ($h['occ'] >= 50 ? 'indigo' : 'amber'); $hgt = max(4, (int) round($h['occ'] * 0.85)); @endphp
                <div class="flex-1 flex flex-col items-center gap-1 group">
                    <div class="text-[9px] font-mono text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity">{{ $h['occ'] }}%</div>
                    <div class="w-full rounded-t-md bg-{{ $col }}-400/70" style="height: {{ $hgt }}px;"></div>
                    <div class="text-[9px] text-slate-400">{{ \Carbon\Carbon::parse($h['date'])->format('d') }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Forecast 30d --}}
    <div class="p-6">
        <p class="text-xs font-bold text-violet-600 uppercase tracking-wide mb-3">30 hari ke depan (AI forecast)</p>
        @if(empty($days))
            <p class="text-sm text-slate-400 italic py-8 text-center">AI provider belum dikonfigurasi atau model tidak return data. Konfigurasi di Settings → Integrations.</p>
        @else
            <div class="flex items-end gap-1 h-32 mb-3">
                @foreach($days as $d)
                    @php $occ = (float)($d['occupancy_pct'] ?? $d['occ'] ?? 0); $col = $occ >= 80 ? 'violet' : ($occ >= 50 ? 'fuchsia' : 'rose'); $hgt = max(4, (int) round($occ * 1.1)); @endphp
                    <div class="flex-1 flex flex-col items-center gap-1 group min-w-[16px]">
                        <div class="text-[9px] font-mono text-slate-500 opacity-0 group-hover:opacity-100 transition-opacity">{{ round($occ) }}%</div>
                        <div class="w-full rounded-t-md bg-gradient-to-t from-{{ $col }}-500 to-{{ $col }}-300" style="height: {{ $hgt }}px;"></div>
                    </div>
                @endforeach
            </div>
            <p class="text-[11px] text-slate-400 text-center">{{ count($days) }} hari · ditenagai AI ({{ config('scout.driver') === 'database' ? 'tanpa external service' : 'dengan provider eksternal' }})</p>
        @endif
    </div>
</div>

@endsection
