@extends('panel.layout')
@section('title', 'Cashier Shift Report')
@section('content')

<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Cashier Shift Report</h1>
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
        <a href="{{ route('panel.reports.export-pdf', ['type' => 'cashier-shift']) . '?' . http_build_query(request()->only(['from','to'])) }}"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-rose-700 bg-rose-50 px-4 py-2 rounded-xl hover:bg-rose-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Export PDF
        </a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700 bg-emerald-50 px-4 py-2 rounded-xl hover:bg-emerald-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
    </div>
</div>

@if ($shifts->isEmpty())
<div class="bg-white rounded-2xl shadow-card border border-gray-100 flex flex-col items-center justify-center py-16">
    <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
    </svg>
    <p class="text-sm font-medium text-gray-500">Tidak ada shift pada rentang tanggal ini</p>
</div>
@else

{{-- Summary row --}}
@php
    $totalExpected = $shifts->sum('expected_cash');
    $totalActual   = $shifts->sum('actual_cash');
    $totalVariance = $shifts->sum('variance');
    $totalOpen     = $shifts->where('is_open', true)->count();
    $totalClosed   = $shifts->where('is_open', false)->count();

    // Aggregate payment method breakdown across all shifts
    $aggPayments = [];
    foreach ($shifts as $s) {
        foreach ($s['breakdown'] as $method => $amount) {
            $aggPayments[$method] = ($aggPayments[$method] ?? 0) + $amount;
        }
    }
@endphp
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    @foreach ([
        ['Total Shifts',    $shifts->count(),                     'indigo',  ''],
        ['Open Shifts',     $totalOpen,                           'amber',   'shifts'],
        ['Closed Shifts',   $totalClosed,                         'emerald', 'shifts'],
        ['Total Variance',  $totalVariance,                       $totalVariance < 0 ? 'rose' : ($totalVariance > 0 ? 'emerald' : 'gray'), ''],
        ['Actual Cash',     $totalActual,                         'blue',    ''],
    ] as [$lbl, $val, $col, $suffix])
    <div class="bg-white rounded-2xl p-4 shadow-card border border-gray-100">
        <div class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">{{ $lbl }}</div>
        <div class="text-lg font-bold text-{{ $col }}-700 font-mono tabular-nums truncate">
            @if (is_numeric($val))
                @if (in_array($lbl, ['Total Shifts','Open Shifts','Closed Shifts']))
                    {{ number_format($val) }} {{ $suffix }}
                @else
                    Rp {{ number_format($val, 0, ',', '.') }}
                @endif
            @else
                {{ $val }}
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- Payment method doughnut chart --}}
@if (!empty($aggPayments))
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="text-sm font-semibold text-gray-700 mb-4">Payment Method Breakdown</div>
        <div class="flex items-center justify-center" style="height: 280px;">
            <canvas id="paymentMethodChart"></canvas>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="text-sm font-semibold text-gray-700 mb-4">Payment Summary</div>
        <div class="space-y-2">
            @php
                $totalPmt = array_sum($aggPayments);
                $colorMap = ['cash' => 'bg-emerald-500', 'card' => 'bg-blue-500', 'qris' => 'bg-violet-500', 'transfer' => 'bg-amber-500', 'credit' => 'bg-indigo-500', 'debit' => 'bg-rose-500'];
            @endphp
            @foreach ($aggPayments as $method => $amount)
            @php $pmtPct = $totalPmt > 0 ? round(($amount / $totalPmt) * 100, 1) : 0; $barColor = $colorMap[$method] ?? 'bg-gray-400'; @endphp
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600 capitalize">{{ str_replace('_', ' ', $method) }}</span>
                    <span class="font-mono font-semibold text-gray-900">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $pmtPct }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Per-shift cards --}}
<div class="space-y-4">
    @foreach ($shifts as $s)
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50 bg-gray-50/50">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-bold shrink-0">
                    {{ strtoupper(substr($s['cashier'], 0, 1)) }}
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-800">{{ $s['cashier'] }}</div>
                    <div class="text-xs text-gray-400 font-mono">
                        {{ \Carbon\Carbon::parse($s['opened_at'])->isoFormat('D MMM Y') }}
                        {{ \Carbon\Carbon::parse($s['opened_at'])->format('H:i') }}
                        @if ($s['closed_at'])
                            — {{ \Carbon\Carbon::parse($s['closed_at'])->format('H:i') }}
                        @else
                            <span class="text-amber-500 font-semibold ml-1">· Masih Open</span>
                        @endif
                    </div>
                </div>
            </div>
            @if ($s['is_open'])
            <span class="text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full">Open</span>
            @else
            <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">Closed</span>
            @endif
        </div>

        <div class="p-5 grid grid-cols-2 md:grid-cols-4 gap-4 border-b border-gray-50">
            @foreach ([
                ['Opening Float', $s['opening_float'], 'gray'],
                ['Expected Cash', $s['expected_cash'], 'gray'],
                ['Actual Cash',   $s['actual_cash'],   'gray'],
                ['Variance',      $s['variance'],       $s['variance'] < 0 ? 'red' : ($s['variance'] > 0 ? 'emerald' : 'gray')],
            ] as [$lbl2, $val2, $col2])
            <div>
                <div class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-0.5">{{ $lbl2 }}</div>
                <div class="font-mono font-semibold text-sm tabular-nums text-{{ $col2 }}-{{ $col2 === 'gray' ? '800' : '600' }}">
                    Rp {{ number_format($val2, 0, ',', '.') }}
                </div>
            </div>
            @endforeach
        </div>

        @if (!empty($s['breakdown']))
        <div class="px-5 py-4">
            <div class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-3">Payment Method Breakdown</div>
            <div class="flex flex-wrap gap-2">
                @foreach ($s['breakdown'] as $method => $amount)
                <div class="flex items-center gap-2 bg-gray-50 rounded-xl px-3 py-2">
                    <span class="text-xs font-medium text-gray-600 capitalize">{{ str_replace('_', ' ', $method) }}</span>
                    <span class="text-xs font-mono font-bold text-gray-900">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endforeach
</div>
@endif

@if (!empty($aggPayments))
@push('scripts')
<script>
(function() {
    const ctx = document.getElementById('paymentMethodChart');
    if (!ctx) return;
    const labels = {!! json_encode(array_keys($aggPayments)) !!}.map(m => m.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));
    const values = {!! json_encode(array_values($aggPayments)) !!};
    const colors = ['#10b981','#3b82f6','#8b5cf6','#f59e0b','#6366f1','#f43f5e','#06b6d4','#84cc16','#ec4899'];
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: values.map((_, i) => colors[i % colors.length]),
                borderColor: '#ffffff',
                borderWidth: 3,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 16, usePointStyle: true, pointStyleWidth: 10, font: { size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ' Rp ' + ctx.raw.toLocaleString('id-ID')
                    }
                }
            }
        }
    });
})();
</script>
@endpush
@endif
@endsection
