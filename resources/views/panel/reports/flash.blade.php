@extends('panel.layout')
@section('title', 'Flash Report')
@section('content')

<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Daily Flash Report</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($from)->isoFormat('D MMM Y') }} — {{ \Carbon\Carbon::parse($to)->isoFormat('D MMM Y') }}</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="date" name="from" value="{{ $from }}"
                   class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-card focus:border-primary-400 outline-none">
            <span class="text-sm text-gray-400">→</span>
            <input type="date" name="to" value="{{ $to }}"
                   class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-card focus:border-primary-400 outline-none">
            <button type="submit"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-primary-600 px-4 py-2 rounded-xl hover:bg-primary-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Apply
            </button>
        </form>
        <a href="{{ route('panel.reports.export-pdf', ['type' => 'flash']) . '?' . http_build_query(request()->only(['from','to'])) }}"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-rose-700 bg-rose-50 px-4 py-2 rounded-xl hover:bg-rose-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Export PDF
        </a>
    </div>
</div>

{{-- Summary KPI cards --}}
@php
    $roomsKpi    = $report->rooms_kpi ?? [];
    $revBreak    = $report->revenue_breakdown ?? [];
    $occPct      = $roomsKpi['occupancy_pct'] ?? 0;
    $roomRev     = $revBreak['Rooms'] ?? $revBreak['Room Revenue'] ?? $revBreak['rooms'] ?? 0;
    $fnbRev      = $revBreak['F&B'] ?? $revBreak['Food & Beverage'] ?? $revBreak['fnb'] ?? 0;
    $totalRev    = $report->total_revenue ?? 0;
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach ([
        ['Occupancy %',    $occPct . '%',                               'primary'],
        ['Room Revenue',   'Rp '.number_format((float)$roomRev, 0, ',', '.'),  'emerald'],
        ['F&B Revenue',    'Rp '.number_format((float)$fnbRev, 0, ',', '.'),   'amber'],
        ['Total Revenue',  'Rp '.number_format((float)$totalRev, 0, ',', '.'), 'indigo'],
    ] as [$lbl, $val, $col])
    <div class="bg-white rounded-2xl p-4 shadow-card border border-gray-100">
        <div class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">{{ $lbl }}</div>
        <div class="text-lg font-bold text-{{ $col }}-700 font-mono tabular-nums truncate">{{ $val }}</div>
    </div>
    @endforeach
</div>

{{-- Revenue trend chart (7 days) --}}
@if (!empty($trendData))
<div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100 mb-6">
    <div class="text-sm font-semibold text-gray-700 mb-4">Revenue Trend (Last 7 Days)</div>
    <div style="height: 300px;">
        <canvas id="revenueTrendChart"></canvas>
    </div>
</div>
@endif

<div class="grid md:grid-cols-2 gap-4">

    {{-- Rooms KPI --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V10z"/></svg>
            </div>
            <h2 class="text-sm font-semibold text-gray-700">Rooms KPI</h2>
        </div>
        <div class="p-5 space-y-2.5">
            @forelse ($report->rooms_kpi ?? [] as $k => $v)
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">{{ $k }}</span>
                <span class="font-mono text-sm font-semibold text-gray-900">{{ is_numeric($v) ? number_format((float) $v, 2, ',', '.') : $v }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-400">No data for this date.</p>
            @endforelse
        </div>
    </div>

    {{-- Revenue Breakdown --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h2 class="text-sm font-semibold text-gray-700">Revenue Breakdown</h2>
        </div>
        <div class="p-5 space-y-2.5">
            @foreach ($report->revenue_breakdown ?? [] as $k => $v)
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">{{ $k }}</span>
                <span class="font-mono text-sm text-gray-900">Rp {{ number_format((float) $v, 0, ',', '.') }}</span>
            </div>
            @endforeach
            @if (isset($report->total_revenue))
            <div class="flex justify-between items-center pt-2.5 mt-0.5 border-t border-gray-100">
                <span class="text-sm font-bold text-gray-900">TOTAL</span>
                <span class="font-mono text-sm font-bold text-emerald-700">Rp {{ number_format($report->total_revenue, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Tax --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/></svg>
            </div>
            <h2 class="text-sm font-semibold text-gray-700">Tax</h2>
        </div>
        <div class="p-5 space-y-2.5">
            @forelse ($report->tax_breakdown ?? [] as $k => $v)
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">{{ $k }}</span>
                <span class="font-mono text-sm text-gray-900">Rp {{ number_format((float) $v, 0, ',', '.') }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-400">No tax data.</p>
            @endforelse
        </div>
    </div>

    {{-- Payment Methods --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <h2 class="text-sm font-semibold text-gray-700">Payment Methods</h2>
        </div>
        <div class="p-5 space-y-2.5">
            @forelse ($report->payment_breakdown ?? [] as $k => $v)
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600 capitalize">{{ $k }}</span>
                <span class="font-mono text-sm text-gray-900">Rp {{ number_format((float) $v, 0, ',', '.') }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-400">No payments data.</p>
            @endforelse
        </div>
    </div>

    {{-- Source Mix --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden md:col-span-2">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
            </div>
            <h2 class="text-sm font-semibold text-gray-700">Source Mix</h2>
        </div>
        <div class="p-5 grid md:grid-cols-3 gap-3">
            @forelse ($report->source_mix ?? [] as $k => $v)
            <div class="bg-gray-50 rounded-xl px-4 py-3">
                <div class="text-xs font-medium text-gray-500 mb-1 capitalize">{{ $k }}</div>
                <div class="text-sm font-semibold text-gray-900">{{ $v['count'] ?? 0 }} bookings</div>
                <div class="text-xs font-mono text-gray-600 mt-0.5">Rp {{ number_format((float) ($v['revenue'] ?? 0), 0, ',', '.') }}</div>
            </div>
            @empty
            <p class="text-sm text-gray-400 md:col-span-3">No source mix data.</p>
            @endforelse
        </div>
    </div>

</div>

@if (!empty($trendData))
@push('scripts')
<script>
(function() {
    const ctx = document.getElementById('revenueTrendChart');
    if (!ctx) return;
    const data = {!! json_encode(array_values($trendData)) !!};
    const labels = {!! json_encode(array_keys($trendData)) !!};
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Revenue',
                data: data,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.1)',
                borderWidth: 2.5,
                pointBackgroundColor: '#6366f1',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.35,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => 'Rp ' + ctx.raw.toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: { callback: v => (v / 1000000).toFixed(1) + 'M' },
                    grid: { color: 'rgba(0,0,0,0.04)' }
                },
                x: {
                    grid: { display: false },
                    ticks: { maxRotation: 45, font: { size: 11 } }
                }
            }
        }
    });
})();
</script>
@endpush
@endif
@endsection
