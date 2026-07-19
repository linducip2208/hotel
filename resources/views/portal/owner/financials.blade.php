@extends('portal.owner.layout')
@section('title', 'Laporan Keuangan')
@section('content')

@php
    $fmtRupiah = function($val) { return 'Rp ' . number_format(abs($val), 0, ',', '.'); };
@endphp

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 font-display">Laporan Keuangan</h1>
        <p class="text-sm text-slate-500 mt-1">{{ $property->name }} &mdash; Profit & Loss</p>
    </div>
    <form method="GET" class="inline-flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-2 shadow-sm">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <input type="month" name="period" value="{{ \Carbon\Carbon::parse($period)->format('Y-m') }}" onchange="this.form.submit()"
               class="text-sm font-medium text-slate-700 bg-transparent border-none outline-none cursor-pointer">
    </form>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    {{-- P&L Summary --}}
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
        <div class="bg-emerald-50 border-b border-emerald-100 px-5 py-3">
            <h2 class="text-sm font-semibold text-emerald-800">Ringkasan Laba Rugi — {{ \Carbon\Carbon::parse($period)->isoFormat('MMMM Y') }}</h2>
        </div>
        <div class="divide-y divide-slate-50">
            <div class="px-5 py-3 flex justify-between items-center text-sm bg-slate-50/50">
                <span class="font-semibold text-slate-700">PENDAPATAN</span>
                <span class="font-mono font-semibold text-slate-700">{{ $fmtRupiah($pnl['gross_revenue']) }}</span>
            </div>
            <div class="px-5 py-2.5 flex justify-between items-center text-sm pl-8">
                <span class="text-slate-600">Pendapatan Kamar</span>
                <span class="font-mono text-slate-700">{{ $fmtRupiah($pnl['room_revenue']) }}</span>
            </div>
            <div class="px-5 py-2.5 flex justify-between items-center text-sm pl-8">
                <span class="text-slate-600">Pendapatan F&B</span>
                <span class="font-mono text-slate-700">{{ $fmtRupiah($pnl['fnb_revenue']) }}</span>
            </div>
            <div class="px-5 py-2.5 flex justify-between items-center text-sm pl-8">
                <span class="text-slate-600">Pendapatan Lainnya</span>
                <span class="font-mono text-slate-700">{{ $fmtRupiah($pnl['other_revenue']) }}</span>
            </div>
            @if($pnl['discounts'] != 0)
            <div class="px-5 py-2.5 flex justify-between items-center text-sm pl-8">
                <span class="text-rose-600">Diskon</span>
                <span class="font-mono text-rose-600">({{ $fmtRupiah(abs($pnl['discounts'])) }})</span>
            </div>
            @endif

            <div class="px-5 py-3 flex justify-between items-center text-sm bg-slate-50/50">
                <span class="font-semibold text-slate-700">BIAYA</span>
                <span class="font-mono font-semibold text-rose-700">({{ $fmtRupiah($pnl['total_expenses']) }})</span>
            </div>
            <div class="px-5 py-2.5 flex justify-between items-center text-sm pl-8">
                <span class="text-slate-600">Gaji & Tunjangan</span>
                <span class="font-mono text-slate-700">{{ $fmtRupiah($pnl['payroll_expense']) }}</span>
            </div>
            <div class="px-5 py-2.5 flex justify-between items-center text-sm pl-8">
                <span class="text-slate-600">Utilitas</span>
                <span class="font-mono text-slate-700">{{ $fmtRupiah($pnl['utility_expense']) }}</span>
            </div>
            <div class="px-5 py-2.5 flex justify-between items-center text-sm pl-8">
                <span class="text-slate-600">Perawatan</span>
                <span class="font-mono text-slate-700">{{ $fmtRupiah($pnl['maintenance_expense']) }}</span>
            </div>
            <div class="px-5 py-2.5 flex justify-between items-center text-sm pl-8">
                <span class="text-slate-600">Biaya Lainnya</span>
                <span class="font-mono text-slate-700">{{ $fmtRupiah($pnl['other_expense']) }}</span>
            </div>

            <div class="px-5 py-4 flex justify-between items-center {{ $pnl['noi'] >= 0 ? 'bg-emerald-50' : 'bg-rose-50' }}">
                <span class="font-bold text-sm {{ $pnl['noi'] >= 0 ? 'text-emerald-800' : 'text-rose-800' }}">NOI (Net Operating Income)</span>
                <span class="font-mono font-bold text-lg {{ $pnl['noi'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ $pnl['noi'] >= 0 ? '' : '-' }}{{ $fmtRupiah($pnl['noi']) }}
                </span>
            </div>

            @if($ownershipPct > 0)
            <div class="px-5 py-4 flex justify-between items-center bg-indigo-50">
                <span class="font-bold text-sm text-indigo-800">
                    Bagian Anda ({{ number_format($ownershipPct, 1) }}%)
                </span>
                <span class="font-mono font-bold text-lg text-indigo-700">
                    {{ $fmtRupiah($pnl['noi'] * ($ownershipPct / 100)) }}
                </span>
            </div>
            @endif
        </div>
    </div>

    {{-- Key Metrics --}}
    <div class="space-y-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-5">
            <h2 class="font-semibold text-slate-800 mb-4">Metrik Kinerja</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Okupansi</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $pnl['occupancy'] }}%</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">ADR</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $fmtRupiah($pnl['adr']) }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">RevPAR</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $fmtRupiah($pnl['revpar']) }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Kamar Terjual</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $pnl['sold_room_nights'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5">
            <h2 class="font-semibold text-slate-800 mb-4">Ringkasan Bulanan</h2>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Total Kamar</span>
                    <span class="font-semibold text-slate-800">{{ $pnl['total_rooms'] }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Malam Kamar Tersedia</span>
                    <span class="font-semibold text-slate-800">{{ number_format($pnl['available_room_nights'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Malam Kamar Terjual</span>
                    <span class="font-semibold text-slate-800">{{ number_format($pnl['sold_room_nights'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Pajak Dipungut</span>
                    <span class="font-semibold text-slate-800">{{ $fmtRupiah($pnl['tax_collected']) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
