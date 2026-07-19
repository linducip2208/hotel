@extends('panel.layout')
@section('title', 'Intelijen Kompetitor')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Intelijen Kompetitor</h1>
        <p class="text-sm text-gray-500 mt-0.5">Pantau posisi harga Anda relatif terhadap kompetitor secara real-time</p>
    </div>
    <form method="POST" action="{{ route('panel.rms.rate-shopper.trigger') }}">
        @csrf
        <button type="submit"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Shop Now
        </button>
    </form>
</div>

{{-- Position Badge --}}
<div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Posisi Harga</p>
        <div class="flex items-center gap-3">
            @php
                $posColor = $position === 'Premium' ? 'emerald' : ($position === 'Value' ? 'blue' : 'amber');
            @endphp
            <span class="inline-flex items-center gap-1.5 text-sm font-bold bg-{{ $posColor }}-50 text-{{ $posColor }}-700 px-3 py-1.5 rounded-xl">
                <span class="w-2 h-2 rounded-full bg-{{ $posColor }}-500"></span>
                {{ $position }}
            </span>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Rate Index</p>
        <p class="text-3xl font-bold text-gray-900">
            {{ $latestIndex ? number_format($latestIndex, 3) : '—' }}
        </p>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Data Tersedia</p>
        <p class="text-3xl font-bold text-gray-900">{{ count($trend) }}<span class="text-lg text-gray-400 font-normal"> hari</span></p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Competitor Comparison --}}
    <div class="lg:col-span-1 bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden h-fit">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Perbandingan Kompetitor</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Rate Rata²</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Delta</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php
                        $ourAvg = collect($trend)->avg('our_rate') ?? 0;
                    @endphp
                    @forelse ($competitorSummary as $comp)
                    @php
                        $delta = $ourAvg > 0 ? round((($ourAvg - $comp['avg_rate']) / $comp['avg_rate']) * 100, 1) : 0;
                        $deltaColor = $delta > 0 ? 'red' : ($delta < 0 ? 'emerald' : 'gray');
                    @endphp
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $comp['name'] }}</td>
                        <td class="px-4 py-3 text-right font-mono text-sm text-gray-700">Rp {{ number_format($comp['avg_rate'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">
                            <span class="text-xs font-semibold text-{{ $deltaColor }}-600">
                                {{ $delta > 0 ? '+' : '' }}{{ $delta }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-10 text-center text-sm text-gray-400">Belum ada data kompetitor</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Rate Trend Chart --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Tren Rate (30 Hari)</h2>
        @if (empty($trend))
        <div class="flex flex-col items-center justify-center py-12">
            <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <p class="text-sm font-medium text-gray-600">Belum ada data tren</p>
            <p class="text-xs text-gray-400 mt-1">Jalankan rate shopper untuk mengisi data</p>
        </div>
        @else
        <canvas id="trendChart" height="200"></canvas>
        @endif
    </div>
</div>

{{-- Alerts --}}
<div class="mt-6 bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-700">Peringatan Harga (14 Hari Terakhir)</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Pesan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($alerts as $alert)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 text-sm text-gray-600 whitespace-nowrap">{{ $alert['date'] }}</td>
                    <td class="px-4 py-3.5 text-center">
                        @if ($alert['type'] === 'overpriced')
                        <span class="inline-flex items-center gap-1 text-xs font-semibold bg-red-50 text-red-700 px-2.5 py-1 rounded-full">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                            Overpriced
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs font-semibold bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            Underpriced
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-700">{{ $alert['message'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="py-10 text-center text-sm text-gray-400">Tidak ada peringatan — harga dalam rentang wajar</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
@if (!empty($trend))
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('trendChart');
    if (!ctx) return;
    var trend = {!! json_encode($trend) !!};
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trend.map(function(t) { return t.date; }),
            datasets: [
                {
                    label: 'Rate Kita',
                    data: trend.map(function(t) { return t.our_rate; }),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 0,
                    fill: true,
                },
                {
                    label: 'Rata² Kompetitor',
                    data: trend.map(function(t) { return t.avg_competitor; }),
                    borderColor: '#f43f5e',
                    backgroundColor: 'rgba(244,63,94,0.05)',
                    borderWidth: 2,
                    borderDash: [5,5],
                    tension: 0.3,
                    pointRadius: 0,
                    fill: true,
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { intersect: false, mode: 'index' },
            plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: { size: 11 } } } },
            scales: {
                y: {
                    ticks: {
                        callback: function(v) { return 'Rp ' + (v/1000).toFixed(0) + 'k'; },
                        font: { size: 10 }
                    }
                },
                x: { ticks: { font: { size: 10 }, maxTicksLimit: 15 } }
            }
        }
    });
});
</script>
@endif
@endpush

@endsection
