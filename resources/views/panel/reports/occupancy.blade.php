@extends('panel.layout')
@section('title', 'Occupancy Report')
@section('content')

<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Occupancy Report</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $from->isoFormat('D MMM Y') }} — {{ $to->isoFormat('D MMM Y') }}</p>
    </div>
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <input type="date" name="from" value="{{ $from->toDateString() }}"
               class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-card focus:border-primary-400 outline-none">
        <span class="text-sm text-gray-400">→</span>
        <input type="date" name="to" value="{{ $to->toDateString() }}"
               class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-card focus:border-primary-400 outline-none">
        <button type="submit"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-primary-600 px-4 py-2 rounded-xl hover:bg-primary-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Apply
        </button>
        <a href="{{ route('panel.reports.export-pdf', ['type' => 'occupancy']) . '?' . http_build_query(request()->only(['from','to'])) }}"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-rose-700 bg-rose-50 px-4 py-2 rounded-xl hover:bg-rose-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Export PDF
        </a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700 bg-emerald-50 px-4 py-2 rounded-xl hover:bg-emerald-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
    </form>
</div>

{{-- Summary KPIs --}}
@php
    $totalSold  = $rows->sum('sold');
    $totalAvail = $rows->sum('available');
    $totalRev   = $rows->sum('total_rev');
    $avgOcc     = $rows->count() ? round($rows->avg('occ_pct'), 1) : 0;
    $avgAdr     = $totalSold > 0 ? round($rows->sum(fn($r) => $r['adr'] * $r['sold']) / $totalSold, 0) : 0;
    $avgRevpar  = $rows->count() ? round($rows->avg('revpar'), 0) : 0;
@endphp
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    @foreach ([
        ['Avg Occupancy', $avgOcc.'%',                                   'primary'],
        ['Rooms Sold',    number_format($totalSold),                     'blue'],
        ['Avg ADR',       'Rp '.number_format($avgAdr, 0, ',', '.'),    'violet'],
        ['Avg RevPAR',    'Rp '.number_format($avgRevpar, 0, ',', '.'), 'rose'],
        ['Total Revenue', 'Rp '.number_format($totalRev, 0, ',', '.'),  'emerald'],
    ] as [$lbl, $val, $col])
    <div class="bg-white rounded-2xl p-4 shadow-card border border-gray-100">
        <div class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">{{ $lbl }}</div>
        <div class="text-lg font-bold text-{{ $col }}-700 font-mono tabular-nums truncate">{{ $val }}</div>
    </div>
    @endforeach
</div>

{{-- Chart.js bar chart --}}
@if ($rows->isNotEmpty())
<div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100 mb-6">
    <div class="text-sm font-semibold text-gray-700 mb-4">Occupancy % per Day</div>
    <div class="w-full" style="height: 300px;">
        <canvas id="occupancyChart"></canvas>
    </div>
</div>
@endif

{{-- Detail table --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    @foreach (['Date','Available','Sold','OCC %','ADR','RevPAR','Revenue'] as $h)
                    <th class="px-4 py-3 {{ $loop->first ? 'text-left' : 'text-right' }} text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($rows as $r)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap">{{ \Carbon\Carbon::parse($r['date'])->isoFormat('ddd, D MMM') }}</td>
                    <td class="px-4 py-3 text-right text-gray-500 tabular-nums">{{ $r['available'] }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-900 tabular-nums">{{ $r['sold'] }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">
                        <span class="font-bold @if($r['occ_pct'] >= 80) text-emerald-600 @elseif($r['occ_pct'] >= 50) text-primary-600 @else text-amber-600 @endif">
                            {{ $r['occ_pct'] }}%
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-gray-600 tabular-nums">{{ number_format($r['adr'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-mono text-gray-600 tabular-nums">{{ number_format($r['revpar'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-mono font-semibold text-emerald-700 tabular-nums">Rp {{ number_format($r['total_rev'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-400">
                        Tidak ada data untuk rentang tanggal ini.<br>
                        <span class="text-xs">Jalankan Night Audit terlebih dahulu untuk menghasilkan laporan.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
            <tfoot class="border-t-2 border-gray-200 bg-gray-50/80">
                <tr>
                    <td class="px-4 py-3 text-xs font-bold text-gray-600 uppercase">Total / Avg</td>
                    <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ number_format($totalAvail) }}</td>
                    <td class="px-4 py-3 text-right font-bold tabular-nums">{{ number_format($totalSold) }}</td>
                    <td class="px-4 py-3 text-right font-bold tabular-nums @if($avgOcc >= 80) text-emerald-600 @elseif($avgOcc >= 50) text-primary-600 @else text-amber-600 @endif">{{ $avgOcc }}%</td>
                    <td class="px-4 py-3 text-right font-mono font-semibold tabular-nums">{{ number_format($avgAdr, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-mono font-semibold tabular-nums">{{ number_format($avgRevpar, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-mono font-bold text-emerald-700 tabular-nums">Rp {{ number_format($totalRev, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@if ($rows->isNotEmpty())
@push('scripts')
<script>
(function() {
    const ctx = document.getElementById('occupancyChart');
    if (!ctx) return;
    const data = {!! $rows->map(fn($r) => [
        'date'  => $r['date'],
        'occ'   => $r['occ_pct'],
        'sold'  => $r['sold'],
        'rev'   => $r['total_rev'],
    ])->values()->toJson(JSON_UNESCAPED_UNICODE) !!};
    const getColor = v => v >= 80 ? 'rgba(16,185,129,0.85)' : v >= 50 ? 'rgba(99,102,241,0.85)' : 'rgba(245,158,11,0.85)';
    const getBorder = v => v >= 80 ? 'rgb(16,185,129)' : v >= 50 ? 'rgb(99,102,241)' : 'rgb(245,158,11)';
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.date),
            datasets: [{
                label: 'Occupancy %',
                data: data.map(d => d.occ),
                backgroundColor: data.map(d => getColor(d.occ)),
                borderColor: data.map(d => getBorder(d.occ)),
                borderWidth: 1,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.raw + '% occupancy',
                        afterLabel: ctx => 'Sold: ' + data[ctx.dataIndex].sold + ' rooms'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: { callback: v => v + '%' },
                    grid: { color: 'rgba(0,0,0,0.04)' }
                },
                x: {
                    ticks: { maxRotation: 45, font: { size: 11 } },
                    grid: { display: false }
                }
            }
        }
    });
})();
</script>
@endpush
@endif
@endsection
