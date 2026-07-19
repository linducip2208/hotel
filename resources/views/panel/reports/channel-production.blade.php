@extends('panel.layout')
@section('title', 'Channel Production')
@section('content')

<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Channel Production</h1>
        <p class="text-sm text-gray-500 mt-0.5">Revenue and booking volume by source channel</p>
    </div>
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
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700 bg-emerald-50 px-4 py-2 rounded-xl hover:bg-emerald-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
    </form>
</div>

@php
    $totalBookings = $rows->sum('bookings');
    $totalRevenue  = $rows->sum('revenue');
    $uniqueChannels = $rows->count();
    $avgBookingValue = $totalBookings > 0 ? round($totalRevenue / $totalBookings, 0) : 0;
@endphp

@if ($totalRevenue > 0)
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach ([
        ['Total Bookings',     number_format($totalBookings),                                   'indigo',  ''],
        ['Total Revenue',      'Rp '.number_format($totalRevenue, 0, ',', '.'),                 'emerald', ''],
        ['Unique Channels',    $uniqueChannels,                                                  'violet', 'channels'],
        ['Avg Booking Value',  'Rp '.number_format($avgBookingValue, 0, ',', '.'),               'blue',    ''],
    ] as [$lbl, $val, $col, $suffix])
    <div class="bg-white rounded-2xl p-4 shadow-card border border-gray-100">
        <div class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">{{ $lbl }}</div>
        <div class="text-lg font-bold text-{{ $col }}-700 font-mono tabular-nums truncate">{{ $val }}</div>
    </div>
    @endforeach
</div>

{{-- Horizontal bar chart --}}
<div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100 mb-6">
    <div class="text-sm font-semibold text-gray-700 mb-4">Bookings by Channel</div>
    <div style="height: {{ max(200, $rows->count() * 50) }}px;">
        <canvas id="channelChart"></canvas>
    </div>
</div>
@else
<div class="bg-white rounded-2xl shadow-card border border-gray-100 flex flex-col items-center justify-center py-16 mb-6">
    <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
    </svg>
    <p class="text-sm font-medium text-gray-500">No channel production data for this period</p>
</div>
@endif

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Source Channel</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Bookings</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Revenue</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Revenue Share</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($rows as $r)
                @php $share = $totalRevenue > 0 ? ($r->revenue / $totalRevenue) * 100 : 0; @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-primary-50 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-800 capitalize">{{ str_replace('_', ' ', $r->source) }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-right text-sm text-gray-700 tabular-nums">{{ number_format($r->bookings) }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-900 font-medium">Rp {{ number_format($r->revenue, 0, ',', '.') }}</td>
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <div class="w-16 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                <div class="h-full rounded-full bg-primary-500" style="width: {{ $share }}%"></div>
                            </div>
                            <span class="text-xs font-medium text-gray-600 tabular-nums w-10 text-right">{{ number_format($share, 1) }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-12 text-center text-sm text-gray-400">No channel production data for this period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($totalRevenue > 0)
@push('scripts')
<script>
(function() {
    const ctx = document.getElementById('channelChart');
    if (!ctx) return;
    const data = {!! $rows->map(fn($r) => [
        'source'   => ucwords(str_replace('_', ' ', $r->source)),
        'bookings' => (int) $r->bookings,
        'revenue'  => (float) $r->revenue,
    ])->values()->toJson(JSON_UNESCAPED_UNICODE) !!};
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.source),
            datasets: [{
                label: 'Bookings',
                data: data.map(d => d.bookings),
                backgroundColor: data.map((_, i) => {
                    const colors = ['#6366f1','#8b5cf6','#a78bfa','#c4b5fd','#ddd6fe','#818cf8','#6d28d9','#7c3aed'];
                    return colors[i % colors.length];
                }),
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        afterLabel: ctx => 'Rp ' + data[ctx.dataIndex].revenue.toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: 'rgba(0,0,0,0.04)' }
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { size: 12 } }
                }
            }
        }
    });
})();
</script>
@endpush
@endif
@endsection
