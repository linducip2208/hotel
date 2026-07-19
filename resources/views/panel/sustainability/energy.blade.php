@extends('panel.layout')
@section('title', 'Energi & Karbon')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Energi & Karbon</h1>
        <p class="text-sm text-gray-500 mt-0.5">Pantau konsumsi energi dan jejak karbon properti Anda</p>
    </div>
    <div class="flex items-center gap-2">
        <form method="GET" class="flex items-center gap-2">
            <select name="year" onchange="this.form.submit()"
                    class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                @for($y = now()->year; $y >= now()->year - 3; $y--)
                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
    </div>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <span class="text-xs font-medium {{ $change >= 0 ? 'text-rose-600 bg-rose-50' : 'text-emerald-600 bg-emerald-50' }} px-2 py-0.5 rounded-full">{{ $change >= 0 ? '+' : '' }}{{ $change }}%</span>
        </div>
        <p class="text-xs text-gray-500">Bulan Ini</p>
        <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ number_format($currentEnergy, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400 mt-0.5">kWh</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Estimasi</span>
        </div>
        <p class="text-xs text-gray-500">Biaya Harian</p>
        <p class="text-2xl font-bold text-gray-900 tabular-nums">Rp {{ number_format($dailyCost, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400 mt-0.5">rata-rata {{ number_format($dailyAvg, 1, ',', '.') }} kWh/hari</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
            </div>
        </div>
        <p class="text-xs text-gray-500">Jejak Karbon</p>
        <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ number_format($carbonKg, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400 mt-0.5">kg CO₂</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-cyan-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </div>
        </div>
        <p class="text-xs text-gray-500">Offset Karbon</p>
        <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ number_format($carbonOffset, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400 mt-0.5">kg CO₂ (10% offset)</p>
    </div>
</div>

{{-- Carbon Footprint Section --}}
<div class="grid md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
        <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Jejak Karbon</h2>
        </div>
        <div class="p-5 space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Total Emisi Bulan Ini</span>
                <span class="text-lg font-bold text-gray-900">{{ number_format($carbonKg, 0, ',', '.') }} kg CO₂</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Offset Hijau</span>
                <span class="text-lg font-bold text-emerald-600">{{ number_format($carbonOffset, 0, ',', '.') }} kg CO₂</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Setara Pohon</span>
                <span class="text-lg font-bold text-emerald-700">≈ {{ round($carbonOffset / 21, 0) }} pohon/tahun</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2.5">
                <div class="bg-gradient-to-r from-emerald-400 to-cyan-500 h-2.5 rounded-full" style="width: {{ min(($carbonOffset / max($carbonKg, 1)) * 100, 100) }}%"></div>
            </div>
            <p class="text-xs text-gray-400">Offset {{ round(($carbonOffset / max($carbonKg, 1)) * 100, 0) }}% dari total emisi · target: 30%</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
        <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Saran Penghematan</h2>
        </div>
        <div class="p-5 space-y-3">
            @foreach($suggestions as $s)
            <div class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-100 rounded-xl">
                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                <p class="text-sm text-amber-800">{{ $s }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Top 10 Rooms --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden mb-6">
    <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-700">Top 10 Kamar — Konsumsi Tertinggi</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kamar</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">kWh</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Biaya</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Bar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($byRoom as $i => $r)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3 text-gray-400 text-xs font-mono">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $r->room?->room_number ?? ('Room #'.$r->room_id) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-gray-900">{{ number_format($r->total_kwh, 1, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-mono text-gray-600">Rp {{ number_format($r->total_cost, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">
                        @php $pct = $currentEnergy > 0 ? ($r->total_kwh / $currentEnergy) * 100 : 0; @endphp
                        <div class="w-24 bg-gray-100 rounded-full h-1.5">
                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width:{{ min($pct, 100) }}%"></div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-10 text-center text-sm text-gray-400">Belum ada data konsumsi per kamar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Annual Report --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
    <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-700">Laporan Tahunan {{ $selectedYear }}</h2>
        <span class="text-xs text-gray-400">Total: {{ number_format($annualReport['totalKwh'], 0, ',', '.') }} kWh · Rp {{ number_format($annualReport['totalCost'], 0, ',', '.') }} · {{ $annualReport['carbonTons'] }} ton CO₂</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Bulan</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">kWh</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Biaya (Rp)</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Bar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($annualReport['monthly'] as $m)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3 font-medium text-gray-900">{{ $m['month'] }}</td>
                    <td class="px-4 py-3 text-right font-mono text-gray-900">{{ number_format($m['kwh'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-mono text-gray-600">{{ number_format($m['cost'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3">
                        @php $maxKwh = max(array_column($annualReport['monthly'], 'kwh'), 1); $pct = ($m['kwh'] / $maxKwh) * 100; @endphp
                        <div class="w-32 bg-gray-100 rounded-full h-1.5">
                            <div class="bg-gradient-to-r from-indigo-400 to-violet-500 h-1.5 rounded-full" style="width:{{ $pct }}%"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
