@extends('panel.layout')
@section('title', 'Demand Forecast')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Demand Forecast</h1>
    <p class="text-sm text-gray-500 mt-0.5">{{ $from->format('d M') }} → {{ $to->format('d M Y') }} · 14-day forward projection</p>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">LY Occ %</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Booked</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Forecast Occ</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Rate Modifier</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Signal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($forecast as $row)
                @php
                    $forecastOcc = (float) $row['forecast_occupancy_pct'];
                    $modifier = (float) $row['suggested_rate_modifier_pct'];
                    $occColor = $forecastOcc >= 80 ? 'text-emerald-600' : ($forecastOcc >= 50 ? 'text-primary-600' : 'text-amber-600');
                    $modColor = $modifier > 0 ? 'text-emerald-700' : ($modifier < 0 ? 'text-red-600' : 'text-gray-500');
                    $signal = $modifier >= 15 ? ['Hot', 'bg-red-50 text-red-700'] : ($modifier >= 5 ? ['High', 'bg-amber-50 text-amber-700'] : ($modifier <= -10 ? ['Soft', 'bg-blue-50 text-blue-600'] : ['Normal', 'bg-gray-100 text-gray-500']));
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-800">
                        {{ \Carbon\Carbon::parse($row['date'])->isoFormat('ddd, D MMM') }}
                    </td>
                    <td class="px-4 py-3.5 text-right text-sm text-gray-500">{{ $row['last_year_occupancy_pct'] }}%</td>
                    <td class="px-4 py-3.5 text-right text-sm text-gray-700 tabular-nums">
                        {{ $row['current_booked'] }}<span class="text-gray-400">/{{ $row['total_rooms'] }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <div class="w-16 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                <div class="h-1.5 rounded-full @if($forecastOcc >= 80) bg-emerald-500 @elseif($forecastOcc >= 50) bg-primary-500 @else bg-amber-400 @endif"
                                     style="width: {{ min($forecastOcc, 100) }}%"></div>
                            </div>
                            <span class="text-sm font-bold {{ $occColor }} tabular-nums w-10 text-right">{{ $forecastOcc }}%</span>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono font-semibold {{ $modColor }}">
                        {{ $modifier > 0 ? '+' : '' }}{{ $modifier }}%
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium px-2.5 py-0.5 rounded-full {{ $signal[1] }}">{{ $signal[0] }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
