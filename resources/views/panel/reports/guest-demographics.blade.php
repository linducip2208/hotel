@extends('panel.layout')
@section('title', 'Demografi Tamu')
@section('content')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Age Group Chart
    new Chart(document.getElementById('ageGroupChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($ageGroups)) !!},
            datasets: [{
                label: 'Jumlah Tamu',
                data: {!! json_encode(array_values($ageGroups)) !!},
                backgroundColor: ['#818cf8','#6366f1','#4f46e5','#4338ca','#3730a3','#312e81','#94a3b8'],
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // Segment Chart
    new Chart(document.getElementById('segmentChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($segment)) !!},
            datasets: [{
                data: {!! json_encode(array_values($segment)) !!},
                backgroundColor: ['#4f46e5','#06b6d4','#f59e0b','#10b981','#94a3b8'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyleWidth: 8 } }
            }
        }
    });

    // Top Nationality Chart
    const natEntries = Object.entries({!! json_encode($nationality) !!}).slice(0, 10);
    new Chart(document.getElementById('nationalityChart'), {
        type: 'bar',
        data: {
            labels: natEntries.map(e => e[0]),
            datasets: [{
                label: 'Jumlah Tamu',
                data: natEntries.map(e => e[1]),
                backgroundColor: '#6366f1',
                borderRadius: 6,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // Top City Chart
    const cityEntries = Object.entries({!! json_encode($city) !!}).slice(0, 10);
    new Chart(document.getElementById('cityChart'), {
        type: 'bar',
        data: {
            labels: cityEntries.map(e => e[0]),
            datasets: [{
                label: 'Jumlah Tamu',
                data: cityEntries.map(e => e[1]),
                backgroundColor: '#06b6d4',
                borderRadius: 6,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
});
</script>
@endpush

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('panel.reports.occupancy') }}"
           class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Demografi Tamu</h1>
            <p class="text-sm text-gray-500 mt-0.5">Analisis profil tamu: usia, asal, segmen, dan perilaku</p>
        </div>
    </div>

    <form method="GET" class="flex items-center gap-2">
        <input type="date" name="from" value="{{ $from }}"
               class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        <span class="text-gray-400 text-xs">s.d.</span>
        <input type="date" name="to" value="{{ $to }}"
               class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-3 py-1.5 rounded-lg transition-colors">Filter</button>
    </form>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-card border border-gray-100 p-4">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Tamu Unik</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalGuests, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-card border border-gray-100 p-4">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Tamu Baru</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($newCount, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400">{{ $totalGuests > 0 ? round(($newCount / $totalGuests) * 100) : 0 }}% dari total</p>
    </div>
    <div class="bg-white rounded-xl shadow-card border border-gray-100 p-4">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Rata-Rata Lama Inap</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($avgStay, 1, ',', '.') }}</p>
        <p class="text-xs text-gray-400">malam</p>
    </div>
    <div class="bg-white rounded-xl shadow-card border border-gray-100 p-4">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Rata-Rata Belanja</p>
        <p class="text-2xl font-bold text-violet-600 mt-1">Rp {{ number_format($avgSpend, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400">per reservasi</p>
    </div>
</div>

{{-- Returning vs New --}}
<div class="bg-white rounded-xl shadow-card border border-gray-100 p-4 mb-6">
    <div class="flex items-center gap-6">
        <div>
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-2">Tamu Kembali</p>
            <p class="text-xl font-bold text-amber-600">{{ number_format($returningCount, 0, ',', '.') }}</p>
        </div>
        <div class="flex-1 bg-gray-100 rounded-full h-4 overflow-hidden">
            @php $pct = $totalGuests > 0 ? ($returningCount / $totalGuests) * 100 : 0; @endphp
            <div class="h-full bg-amber-500 rounded-full" style="width: {{ $pct }}%"></div>
        </div>
        <span class="text-sm font-semibold text-gray-600">{{ round($pct) }}%</span>
    </div>
</div>

{{-- Charts Grid --}}
<div class="grid lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-card border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Kelompok Usia</h3>
        <canvas id="ageGroupChart" height="220"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-card border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Segmen Tamu</h3>
        <canvas id="segmentChart" height="220"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-card border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Top 10 Kewarganegaraan</h3>
        <canvas id="nationalityChart" height="260"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-card border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Top 10 Kota Asal</h3>
        <canvas id="cityChart" height="260"></canvas>
    </div>
</div>

@endsection
