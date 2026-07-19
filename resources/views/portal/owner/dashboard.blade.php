@extends('portal.owner.layout')
@section('title', 'Dashboard Investor')
@section('content')

@php
    $fmtRupiah = function($val) { return 'Rp ' . number_format(abs($val), 0, ',', '.'); };
@endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900 font-display">Dashboard Investor</h1>
    <p class="text-sm text-slate-500 mt-1">
        {{ $property->name }} &mdash; {{ \Carbon\Carbon::parse($period)->isoFormat('MMMM Y') }}
        @if($ownershipPct)
            <span class="ml-2 inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 text-xs font-semibold px-2 py-0.5 rounded-full">Kepemilikan {{ number_format($ownershipPct, 1) }}%</span>
        @endif
    </p>
</div>

{{-- Period Picker --}}
<div class="mb-6">
    <form method="GET" class="inline-flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-2 shadow-sm">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <input type="month" name="period" value="{{ \Carbon\Carbon::parse($period)->format('Y-m') }}" onchange="this.form.submit()"
               class="text-sm font-medium text-slate-700 bg-transparent border-none outline-none cursor-pointer">
    </form>
</div>

@if(!$summary)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center">
        <p class="text-amber-800 font-semibold">Anda belum terdaftar sebagai pemilik properti ini.</p>
        <p class="text-amber-600 text-sm mt-1">Hubungi admin untuk mendaftarkan kepemilikan Anda.</p>
    </div>
@else

{{-- KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <div class="bg-white border border-slate-200 rounded-2xl p-5 card-lift">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pendapatan</span>
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-900 tracking-tight">{{ $fmtRupiah($summary['total_revenue']) }}</p>
        <p class="text-xs text-slate-400 mt-1">Total pendapatan bulan ini</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-5 card-lift">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Biaya</span>
            <div class="w-9 h-9 rounded-xl bg-rose-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-rose-700 tracking-tight">{{ $fmtRupiah($summary['total_expense']) }}</p>
        <p class="text-xs text-slate-400 mt-1">Total pengeluaran</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-5 card-lift">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">NOI (Laba Operasi Bersih)</span>
            <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold {{ $summary['nop'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }} tracking-tight">
            {{ $summary['nop'] >= 0 ? '' : '-' }}{{ $fmtRupiah($summary['nop']) }}
        </p>
        <p class="text-xs text-slate-400 mt-1">Laba setelah biaya operasional</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-5 card-lift">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Okupansi</span>
            <div class="w-9 h-9 rounded-xl bg-sky-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-900 tracking-tight">{{ $summary['occupancy'] }}%</p>
        <p class="text-xs text-slate-400 mt-1">{{ $summary['sold_room_nights'] }} dari {{ $summary['available_room_nights'] }} malam terjual</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-5 card-lift">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">ADR</span>
            <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-900 tracking-tight">{{ $fmtRupiah($summary['adr']) }}</p>
        <p class="text-xs text-slate-400 mt-1">Rata-rata tarif harian</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-5 card-lift">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">RevPAR</span>
            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-900 tracking-tight">{{ $fmtRupiah($summary['revpar']) }}</p>
        <p class="text-xs text-slate-400 mt-1">Pendapatan per kamar tersedia</p>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-8 mb-8">
    {{-- Monthly Trend Chart --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-5">
        <h2 class="font-semibold text-slate-800 mb-4">Tren Bulanan (6 Bulan)</h2>
        <canvas id="trendChart" height="200"></canvas>
    </div>

    {{-- Distribusi Bulan Ini --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-5">
        <h2 class="font-semibold text-slate-800 mb-4">Distribusi Terbaru</h2>
        @if($distributions->isEmpty())
            <div class="text-center py-8 text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-sm">Belum ada distribusi</p>
            </div>
        @else
            <div class="divide-y divide-slate-50">
                @foreach($distributions->take(6) as $d)
                <div class="flex items-center justify-between py-3">
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ \Carbon\Carbon::parse($d->period_start)->isoFormat('MMMM Y') }}</p>
                        <p class="text-xs text-slate-400">{{ $d->status === 'paid' ? 'Dibayar' : 'Menunggu' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-900">{{ $fmtRupiah($d->distribution_amount) }}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $d->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                            {{ $d->status === 'paid' ? 'Lunas' : 'Pending' }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            <a href="{{ route('owner-portal.distributions') }}" class="block text-center text-sm font-medium text-indigo-600 hover:text-indigo-700 mt-3 transition-colors">Lihat Semua &rarr;</a>
        @endif
    </div>
</div>

{{-- Dokumen --}}
@if($documents->isNotEmpty())
<div class="bg-white border border-slate-200 rounded-2xl p-5">
    <h2 class="font-semibold text-slate-800 mb-4">Dokumen Terbaru</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($documents as $doc)
        <a href="{{ route('owner-portal.documents.download', $doc->id) }}"
           class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/30 transition-colors">
            <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-medium text-slate-800 truncate">{{ $doc->title }}</p>
                <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($doc->uploaded_at)->format('d M Y') }}</p>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const trendData = @json($monthlyTrend);
    const labels = trendData.map(t => t.month);
    const revenues = trendData.map(t => t.revenue);
    const expenses = trendData.map(t => t.expense);
    const shares = trendData.map(t => t.share);

    const ctx = document.getElementById('trendChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Pendapatan',
                        data: revenues,
                        backgroundColor: 'rgba(16,185,129,0.2)',
                        borderColor: 'rgb(16,185,129)',
                        borderWidth: 2,
                        borderRadius: 6,
                    },
                    {
                        label: 'Biaya',
                        data: expenses,
                        backgroundColor: 'rgba(244,63,94,0.15)',
                        borderColor: 'rgb(244,63,94)',
                        borderWidth: 2,
                        borderRadius: 6,
                    },
                    {
                        label: 'Bagian Anda',
                        data: shares,
                        type: 'line',
                        borderColor: 'rgb(99,102,241)',
                        backgroundColor: 'rgba(99,102,241,0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgb(99,102,241)',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(v) { return 'Rp ' + (v/1000000).toFixed(0) + 'M'; }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
