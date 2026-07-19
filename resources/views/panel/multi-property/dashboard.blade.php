@extends('panel.layout')
@section('title', 'HQ Multi-Properti')
@section('content')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const labels = {!! json_encode(array_column($perProperty, 'name')) !!};

    new Chart(document.getElementById('occChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Okupansi (%)',
                data: {!! json_encode(array_column($perProperty, 'occupancy')) !!},
                backgroundColor: '#6366f1',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } }
        }
    });

    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: {!! json_encode(array_column($perProperty, 'revenue')) !!},
                backgroundColor: '#06b6d4',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + (v/1000000).toFixed(0) + 'jt' } } }
        }
    });

    new Chart(document.getElementById('revparChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'ADR (Rp)',
                    data: {!! json_encode(array_column($perProperty, 'adr')) !!},
                    backgroundColor: '#8b5cf6',
                    borderRadius: 6,
                },
                {
                    label: 'RevPAR (Rp)',
                    data: {!! json_encode(array_column($perProperty, 'revpar')) !!},
                    backgroundColor: '#f59e0b',
                    borderRadius: 6,
                }
            ]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
});
</script>
@endpush

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">HQ Multi-Properti</h1>
        <p class="text-sm text-gray-500 mt-0.5">Dashboard agregat seluruh properti — {{ $properties->count() }} properti aktif</p>
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
        <div class="flex items-center gap-2 text-indigo-500 mb-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Properti</span>
        </div>
        <p class="text-3xl font-bold text-gray-900">{{ $properties->count() }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-card border border-gray-100 p-4">
        <div class="flex items-center gap-2 text-emerald-500 mb-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Kamar</span>
        </div>
        <p class="text-3xl font-bold text-gray-900">{{ number_format($totalRooms, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-card border border-gray-100 p-4">
        <div class="flex items-center gap-2 text-blue-500 mb-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Pendapatan</span>
        </div>
        <p class="text-3xl font-bold text-gray-900">Rp {{ number_format($totalRevenue / 1000000, 1, ',', '.') }}<span class="text-sm font-normal text-gray-400">jt</span></p>
    </div>
    <div class="bg-white rounded-xl shadow-card border border-gray-100 p-4">
        <div class="flex items-center gap-2 text-violet-500 mb-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Tamu</span>
        </div>
        <p class="text-3xl font-bold text-gray-900">{{ number_format($totalGuests, 0, ',', '.') }}</p>
    </div>
</div>

{{-- Charts --}}
<div class="grid lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-card border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Okupansi per Properti</h3>
        <canvas id="occChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-card border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Pendapatan per Properti</h3>
        <canvas id="revenueChart" height="200"></canvas>
    </div>
</div>
<div class="bg-white rounded-xl shadow-card border border-gray-100 p-5 mb-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-4">ADR vs RevPAR per Properti</h3>
    <canvas id="revparChart" height="180"></canvas>
</div>

{{-- Comparison Table --}}
<div class="bg-white rounded-xl shadow-card border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">Properti</th>
                <th class="px-5 py-3">Kota</th>
                <th class="px-5 py-3 text-right">Kamar</th>
                <th class="px-5 py-3 text-right">Okupansi</th>
                <th class="px-5 py-3 text-right">ADR</th>
                <th class="px-5 py-3 text-right">RevPAR</th>
                <th class="px-5 py-3 text-right">Pendapatan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($perProperty as $pp)
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3 font-medium text-gray-900">{{ $pp['name'] }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $pp['city'] ?? '—' }}</td>
                <td class="px-5 py-3 text-right text-gray-900">{{ number_format($pp['rooms'], 0, ',', '.') }}</td>
                <td class="px-5 py-3 text-right">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $pp['occupancy'] >= 70 ? 'bg-emerald-50 text-emerald-700' : ($pp['occupancy'] >= 40 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                        {{ $pp['occupancy'] }}%
                    </span>
                </td>
                <td class="px-5 py-3 text-right font-mono text-gray-900 text-xs">Rp {{ number_format($pp['adr'], 0, ',', '.') }}</td>
                <td class="px-5 py-3 text-right font-mono text-gray-900 text-xs">Rp {{ number_format($pp['revpar'], 0, ',', '.') }}</td>
                <td class="px-5 py-3 text-right font-mono text-gray-900 text-xs">Rp {{ number_format($pp['revenue'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
