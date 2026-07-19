@extends('panel.layout')
@section('title', 'Harga Berbasis Cuaca')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Harga Berbasis Cuaca</h1>
    <p class="text-sm text-gray-500 mt-0.5">Penyesuaian harga otomatis berdasarkan prakiraan cuaca 7 hari ke depan</p>
</div>

@if(!$hasCoordinates)
<div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-5 py-4 text-sm mb-6 flex items-start gap-3">
    <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
    <div>
        <strong>Koordinat properti belum diatur.</strong> Silakan isi latitude & longitude di <a href="{{ route('panel.settings.property') }}" class="underline font-medium">pengaturan properti</a>. Sementara menggunakan data cuaca simulasi.
    </div>
</div>
@endif

@if(!$hasWeatherProvider)
<div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-5 py-4 text-sm mb-6 flex items-start gap-3">
    <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div>
        <strong>Provider cuaca belum dikonfigurasi.</strong> Tambahkan provider dengan <code>integration_type: weather</code> di <a href="{{ route('panel.settings.integrations') }}" class="underline font-medium">pengaturan integrasi</a>. Sementara menggunakan data simulasi.
    </div>
</div>
@endif

{{-- 7-Day Forecast Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-4 mb-6">
    @foreach($forecasts as $fc)
    @php
        $f = $fc['forecast'];
        $condition = $f['condition'] ?? 'cloudy';
        $icon = $f['icon'] ?? '01d';
        $isMock = $f['_mock'] ?? true;
        $conditionLabel = match($condition) {
            'sunny' => 'Cerah',
            'clear' => 'Cerah',
            'partly_cloudy' => 'Berawan Sebagian',
            'cloudy' => 'Berawan',
            'rainy' => 'Hujan',
            'stormy' => 'Badai',
            'extreme' => 'Ekstrem',
            default => $condition,
        };
        $conditionColor = match($condition) {
            'sunny', 'clear' => 'amber',
            'partly_cloudy' => 'sky',
            'cloudy' => 'slate',
            'rainy', 'stormy', 'extreme' => 'blue',
            default => 'slate',
        };
        $multiplier = 1.0;
        $firstAdj = $fc['adjustments'][0] ?? null;
        if ($firstAdj && $firstAdj['adjusted']) {
            $multiplier = $firstAdj['multiplier'];
        }
    @endphp
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4 text-center">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">{{ $fc['day_short'] }}</p>
        <p class="text-lg font-bold text-gray-900 mb-1">{{ \Carbon\Carbon::parse($fc['date'])->translatedFormat('d M') }}</p>

        <div class="text-4xl mb-2">
            @switch($condition)
                @case('sunny') ☀️ @break
                @case('clear') 🌤️ @break
                @case('partly_cloudy') ⛅ @break
                @case('cloudy') ☁️ @break
                @case('rainy') 🌧️ @break
                @case('stormy') ⛈️ @break
                @case('extreme') 🌪️ @break
                @default 🌡️
            @endswitch
        </div>

        <p class="text-sm font-semibold text-gray-800 mb-0.5">{{ $conditionLabel }}</p>
        <p class="text-xs text-gray-500 mb-3">{{ $f['temp'] ?? '—' }}°C &middot; {{ $f['humidity'] ?? '—' }}%</p>

        <div class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold mb-3
            @if($multiplier > 1.05) bg-emerald-100 text-emerald-700
            @elseif($multiplier > 1.0) bg-sky-100 text-sky-700
            @elseif($multiplier == 1.0) bg-gray-100 text-gray-600
            @else bg-rose-100 text-rose-700
            @endif">
            {{ $multiplier > 1.0 ? '+' : '' }}{{ round(($multiplier - 1) * 100, 1) }}%
        </div>

        @if($isMock)
        <span class="text-[10px] text-amber-500 bg-amber-50 px-1.5 py-0.5 rounded">simulasi</span>
        @endif

        <form method="POST" action="{{ route('panel.revenue.weather-pricing.apply') }}" class="mt-2">
            @csrf
            <input type="hidden" name="date" value="{{ $fc['date'] }}">
            <button type="submit"
                    class="w-full text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white py-1.5 px-3 rounded-lg transition-colors shadow-sm">
                Terapkan
            </button>
        </form>
    </div>
    @endforeach
</div>

{{-- Adjustment details per room type --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="p-5 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Detail Penyesuaian Harga per Tipe Kamar</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-5 py-3">Tanggal</th>
                    <th class="px-5 py-3">Cuaca</th>
                    <th class="px-5 py-3">Tipe Kamar</th>
                    <th class="px-5 py-3 text-right">Harga Dasar</th>
                    <th class="px-5 py-3 text-right">Pengali</th>
                    <th class="px-5 py-3 text-right">Harga Rekomendasi</th>
                    <th class="px-5 py-3 text-center">Penyesuaian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($forecasts as $fc)
                    @php $adjCount = count($fc['adjustments']); @endphp
                    @foreach($fc['adjustments'] as $j => $adj)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        @if($j === 0)
                        <td class="px-5 py-3 font-medium text-gray-900" rowspan="{{ $adjCount }}">
                            {{ \Carbon\Carbon::parse($fc['date'])->translatedFormat('d M') }}
                        </td>
                        <td class="px-5 py-3" rowspan="{{ $adjCount }}">
                            <span class="text-sm">{{ match($fc['forecast']['condition'] ?? 'cloudy') {
                                'sunny' => '☀️ Cerah', 'clear' => '🌤️ Cerah',
                                'partly_cloudy' => '⛅ Berawan Sebagian', 'cloudy' => '☁️ Berawan',
                                'rainy' => '🌧️ Hujan', 'stormy' => '⛈️ Badai',
                                'extreme' => '🌪️ Ekstrem', default => '☁️ Berawan'
                            } }}</span>
                        </td>
                        @endif
                        <td class="px-5 py-3 text-gray-700">{{ $roomTypes[$j]->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-right font-mono text-xs text-gray-600">
                            Rp {{ number_format($adj['current_rate'] ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-right font-mono text-xs text-gray-600">
                            {{ number_format($adj['multiplier'] ?? 1.0, 2) }}x
                        </td>
                        <td class="px-5 py-3 text-right font-mono text-xs font-semibold
                            {{ ($adj['adjustment_pct'] ?? 0) > 0 ? 'text-emerald-600' : ((($adj['adjustment_pct'] ?? 0) < 0) ? 'text-rose-600' : 'text-gray-900') }}">
                            Rp {{ number_format($adj['suggested_rate'] ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ ($adj['adjustment_pct'] ?? 0) > 0 ? 'bg-emerald-100 text-emerald-700' : ((($adj['adjustment_pct'] ?? 0) < 0) ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ ($adj['adjustment_pct'] ?? 0) > 0 ? '+' : '' }}{{ $adj['adjustment_pct'] ?? 0 }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
