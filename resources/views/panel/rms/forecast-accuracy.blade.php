@extends('panel.layout')
@section('title', 'Akurasi Forecast')
@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Akurasi Forecast</h1>
        <p class="text-sm text-gray-500 mt-0.5">Evaluasi akurasi peramalan okupansi terhadap data aktual</p>
    </div>
    <div class="flex items-center gap-2">
        @foreach([7, 14, 30, 60, 90] as $d)
        <a href="?days={{ $d }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $days == $d ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            {{ $d }}H
        </a>
        @endforeach
    </div>
</div>

{{-- Accuracy KPI cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- Overall Accuracy --}}
    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">{{ $days }} Hari</span>
        </div>
        <div class="text-3xl font-bold text-gray-900 tabular-nums">{{ $accuracy }}%</div>
        <div class="text-sm text-gray-500 mt-0.5 mb-3">Akurasi</div>
        <div class="w-full bg-gray-100 rounded-full h-2">
            <div class="h-2 rounded-full {{ $accuracy >= 90 ? 'bg-emerald-500' : ($accuracy >= 75 ? 'bg-indigo-500' : ($accuracy >= 50 ? 'bg-amber-400' : 'bg-rose-500')) }}"
                 style="width: {{ min(max($accuracy, 0), 100) }}%"></div>
        </div>
    </div>

    {{-- MAPE --}}
    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900 tabular-nums">{{ $mape }}%</div>
        <div class="text-sm text-gray-500 mt-0.5">MAPE (Mean Absolute % Error)</div>
    </div>

    {{-- Bias --}}
    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl {{ $bias >= 0 ? 'bg-sky-50' : 'bg-rose-50' }} flex items-center justify-center">
                <svg class="w-5 h-5 {{ $bias >= 0 ? 'text-sky-600' : 'text-rose-600' }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </div>
        </div>
        <div class="text-2xl font-bold tabular-nums {{ $bias >= 0 ? 'text-sky-600' : 'text-rose-600' }}">
            {{ $bias >= 0 ? '+' : '' }}{{ $bias }}%
        </div>
        <div class="text-sm text-gray-500 mt-0.5">Bias ({{ $bias >= 0 ? 'Over' : 'Under' }}-Forecast)</div>
    </div>

    {{-- Avg Absolute Error --}}
    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900 tabular-nums">{{ $avgAbsError }}%</div>
        <div class="text-sm text-gray-500 mt-0.5">Rata-rata Error Absolut</div>
    </div>

</div>

{{-- Trend table --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="p-5 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Tren Akurasi Harian ({{ $days }} hari terakhir)</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-5 py-3 text-right">#</th>
                    <th class="px-5 py-3">Tanggal</th>
                    <th class="px-5 py-3 text-right">Forecast (%)</th>
                    <th class="px-5 py-3 text-right">Aktual (%)</th>
                    <th class="px-5 py-3 text-right">Error (%)</th>
                    <th class="px-5 py-3 text-center">Bias</th>
                    <th class="px-5 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($results as $i => $r)
                @php
                    $errorAbs = abs($r['error']);
                    $status = $errorAbs <= 5 ? 'Sangat Baik' : ($errorAbs <= 10 ? 'Baik' : ($errorAbs <= 20 ? 'Cukup' : 'Buruk'));
                    $statusColor = $errorAbs <= 5 ? 'emerald' : ($errorAbs <= 10 ? 'sky' : ($errorAbs <= 20 ? 'amber' : 'rose'));
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3 text-right text-gray-400 text-xs">{{ $i + 1 }}</td>
                    <td class="px-5 py-3 font-medium text-gray-900">{{ \Carbon\Carbon::parse($r['date'])->translatedFormat('d M Y') }}</td>
                    <td class="px-5 py-3 text-right font-mono text-xs text-gray-700">{{ $r['forecastPct'] }}%</td>
                    <td class="px-5 py-3 text-right font-mono text-xs font-semibold text-gray-900">{{ $r['actualPct'] }}%</td>
                    <td class="px-5 py-3 text-right font-mono text-xs {{ $r['error'] >= 0 ? 'text-sky-600' : 'text-rose-600' }}">
                        {{ $r['error'] >= 0 ? '+' : '' }}{{ $r['error'] }}%
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $r['error'] >= 0 ? 'bg-sky-100 text-sky-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $r['error'] >= 0 ? '↑ Over' : '↓ Under' }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700">
                            {{ $status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="py-10 text-center text-sm text-gray-400">Belum ada data akurasi yang tersedia.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Summary interpretation --}}
@if(count($results) > 0)
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 mt-6">
    <h2 class="font-semibold text-gray-800 mb-3">Interpretasi</h2>
    @if($accuracy >= 90)
        <p class="text-sm text-emerald-700">Sangat Baik — model forecast Anda sangat akurat dengan tingkat error rendah. Lanjutkan strategi yang ada.</p>
    @elseif($accuracy >= 75)
        <p class="text-sm text-sky-700">Baik — akurasi forecast cukup baik namun masih ada ruang perbaikan. Pertimbangkan untuk menambahkan lebih banyak data historis atau faktor musiman.</p>
    @elseif($accuracy >= 50)
        <p class="text-sm text-amber-700">Cukup — ada penyimpangan yang signifikan antara forecast dan aktual. Disarankan untuk mereview parameter model dan menambah dataset training.</p>
    @else
        <p class="text-sm text-rose-700">Perlu Perbaikan — akurasi forecast rendah. Periksa kualitas data input, pastikan data historis tersedia dalam jumlah cukup, dan review konfigurasi model AI.</p>
    @endif

    @if($bias > 10)
        <p class="text-sm text-sky-600 mt-2">⚠️ Over-forecast terdeteksi: sistem Anda secara konsisten memprediksi lebih tinggi dari realisasi. Ini dapat menyebabkan overbooking tidak diperlukan dan alokasi sumber daya berlebihan.</p>
    @elseif($bias < -10)
        <p class="text-sm text-rose-600 mt-2">⚠️ Under-forecast terdeteksi: sistem Anda secara konsisten memprediksi lebih rendah dari realisasi. Ini dapat menyebabkan kehilangan potensi pendapatan karena harga terlalu rendah atau alokasi kurang.</p>
    @endif
</div>
@endif

@endsection
