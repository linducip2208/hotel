@extends('panel.layout')
@section('title', 'Guest Journey Funnel')
@section('content')

<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Guest Journey Funnel</h1>
        <p class="text-sm text-gray-500 mt-0.5">Analisis perjalanan tamu dari pencarian hingga repeat booking</p>
    </div>
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <input type="date" name="from" value="{{ \Illuminate\Support\Arr::get($funnel, 'period.from', now()->subMonths(3)->toDateString()) }}"
               class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-card focus:border-primary-400 outline-none">
        <span class="text-sm text-gray-400">→</span>
        <input type="date" name="to" value="{{ \Illuminate\Support\Arr::get($funnel, 'period.to', now()->toDateString()) }}"
               class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-card focus:border-primary-400 outline-none">
        <button type="submit"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-primary-600 px-4 py-2 rounded-xl hover:bg-primary-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Terapkan
        </button>
    </form>
</div>

@php
    $stages  = $funnel['stages'] ?? [];
    $dropoffs = $funnel['dropoffs'] ?? [];
    $stageColors = ['#4f46e5','#6366f1','#818cf8','#a5b4fc','#c7d2fe','#e0e7ff'];
@endphp

{{-- Funnel Visualization --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 mb-6">
    <h2 class="text-sm font-semibold text-gray-700 mb-5">Visualisasi Funnel</h2>
    <div class="space-y-3">
        @foreach ($stages as $i => $stage)
        @php
            $width = max($stage['pct'], 8);
            $color = $stageColors[$i] ?? '#e0e7ff';
        @endphp
        <div class="flex items-center gap-4">
            <div class="w-32 text-right shrink-0">
                <span class="text-sm font-semibold text-gray-800">{{ $stage['name'] }}</span>
                <span class="text-[11px] text-gray-400 block">{{ $stage['count'] }} tamu</span>
            </div>
            <div class="flex-1">
                <div class="h-10 rounded-xl flex items-center px-4 justify-between"
                     style="width: {{ $width }}%; background: {{ $color }}; min-width: 80px;">
                    <span class="text-xs font-bold text-white drop-shadow">{{ $stage['pct'] }}%</span>
                    @if ($stage['pct'] > 30)
                    <span class="text-xs text-white/80 font-semibold">{{ number_format($stage['count'], 0, ',', '.') }}</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Key Metrics --}}
@php
    $totalSearched = $stages[0]['count'] ?? 0;
    $totalBooked   = $stages[1]['count'] ?? 0;
    $totalReviewed = $stages[4]['count'] ?? 0;
    $totalRepeat   = $stages[5]['count'] ?? 0;
    $overallConv   = $totalSearched > 0 ? round(($totalBooked / $totalSearched) * 100, 1) : 0;
    $reviewRate    = $stages[3]['count'] > 0 ? round(($totalReviewed / $stages[3]['count']) * 100, 1) : 0;
    $repeatRate    = $stages[3]['count'] > 0 ? round(($totalRepeat / $stages[3]['count']) * 100, 1) : 0;
    $cancelCount   = \App\Models\Reservation::where('property_id', app('current_property')->id)
        ->where('status', 'cancelled')
        ->whereBetween('created_at', [\Illuminate\Support\Arr::get($funnel, 'period.from', now()->subMonths(3)->toDateString()), \Illuminate\Support\Arr::get($funnel, 'period.to', now()->toDateString())])
        ->count();
    $cancelRate    = $totalBooked > 0 ? round(($cancelCount / $totalBooked) * 100, 1) : 0;
    $avgScore      = \App\Models\Review::where('property_id', app('current_property')->id)
        ->whereBetween('created_at', [\Illuminate\Support\Arr::get($funnel, 'period.from', now()->subMonths(3)->toDateString()), \Illuminate\Support\Arr::get($funnel, 'period.to', now()->toDateString())])
        ->avg('rating') ?? 0;
@endphp

<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-4 shadow-card border border-gray-100">
        <div class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Konversi Keseluruhan</div>
        <div class="text-lg font-bold text-primary-700 font-mono">{{ $overallConv }}%</div>
    </div>
    <div class="bg-white rounded-2xl p-4 shadow-card border border-gray-100">
        <div class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Tingkat Pembatalan</div>
        <div class="text-lg font-bold text-rose-700 font-mono">{{ $cancelRate }}%</div>
    </div>
    <div class="bg-white rounded-2xl p-4 shadow-card border border-gray-100">
        <div class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Tingkat Repeat</div>
        <div class="text-lg font-bold text-emerald-700 font-mono">{{ $repeatRate }}%</div>
    </div>
    <div class="bg-white rounded-2xl p-4 shadow-card border border-gray-100">
        <div class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Review Rate</div>
        <div class="text-lg font-bold text-amber-700 font-mono">{{ $reviewRate }}%</div>
    </div>
    <div class="bg-white rounded-2xl p-4 shadow-card border border-gray-100">
        <div class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Rata-rata Rating</div>
        <div class="text-lg font-bold text-violet-700 font-mono">{{ round($avgScore, 1) }} / 5</div>
    </div>
</div>

{{-- Drop-off Analysis --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 mb-6">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Analisis Drop-off</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Transisi</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tamu Hilang</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Drop-off Rate</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Saran</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($dropoffs as $do)
                @php
                    $rate = $do['rate'];
                    $badgeColor = $rate > 50 ? 'text-rose-600 bg-rose-50' : ($rate > 30 ? 'text-amber-600 bg-amber-50' : 'text-emerald-600 bg-emerald-50');
                    $suggestion = match ($do['stage']) {
                        'Pencarian → Booking' => 'Perbaiki SEO, harga kompetitif, foto berkualitas tinggi.',
                        'Booking → Check-in' => 'Kirim reminder, komunikasi WhatsApp, tawarkan early check-in.',
                        'Check-in → Ulasan' => 'Minta review via WhatsApp/email, beri insentif kecil.',
                        'Ulasan → Repeat' => 'Program loyalitas, diskon repeat guest, email remarketing.',
                        default => 'Analisis lebih lanjut diperlukan.',
                    };
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5 font-semibold text-gray-800">{{ $do['stage'] }}</td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $badgeColor }}">
                            {{ number_format($do['lost'], 0, ',', '.') }} tamu
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-24 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-rose-400 rounded-full" style="width: {{ min((int)$rate, 100) }}%"></div>
                            </div>
                            <span class="text-xs font-semibold text-gray-600">{{ $rate }}%</span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-500 max-w-[280px]">{{ $suggestion }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Conversion Trend Chart --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-700 mb-4">Tren Konversi Harian (30 Hari)</h2>
    <canvas id="conversionTrendChart" height="80"></canvas>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var trendData = {!! json_encode($trend) !!};
    new Chart(document.getElementById('conversionTrendChart'), {
        type: 'line',
        data: {
            labels: trendData.map(function(d) { return d.date.substring(5); }),
            datasets: [{
                label: 'Total Reservasi',
                data: trendData.map(function(d) { return d.total; }),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.08)',
                fill: true,
                tension: 0.3,
                yAxisID: 'y',
            }, {
                label: 'Konversi (%)',
                data: trendData.map(function(d) { return d.conversion_rate; }),
                borderColor: '#f59e0b',
                backgroundColor: 'transparent',
                borderWidth: 2,
                tension: 0.3,
                yAxisID: 'y1',
            }]
        },
        options: {
            responsive: true,
            interaction: { intersect: false, mode: 'index' },
            plugins: { legend: { position: 'top', labels: { usePointStyle: true, padding: 20 } } },
            scales: {
                y: { beginAtZero: true, position: 'left', title: { display: true, text: 'Total Reservasi' } },
                y1: { beginAtZero: true, position: 'right', max: 105, grid: { drawOnChartArea: false }, title: { display: true, text: 'Konversi (%)' } },
            }
        }
    });
});
</script>
@endpush

@endsection
