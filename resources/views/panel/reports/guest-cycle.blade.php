@extends('panel.layout')
@section('title', 'Siklus Tamu')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Siklus Tamu (Guest Cycle)</h1>
    <p class="text-sm text-gray-500 mt-0.5">Visualisasi kedatangan, tamu menginap, dan keberangkatan per tanggal</p>
</div>

{{-- Date controls --}}
<form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Mulai Tanggal</label>
        <input type="date" name="date" value="{{ $date }}" class="rounded-xl border-gray-200 text-sm px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Rentang (hari)</label>
        <select name="range" class="rounded-xl border-gray-200 text-sm px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
            @foreach([3,7,14,30] as $r)
            <option value="{{ $r }}" {{ $days == $r ? 'selected' : '' }}>{{ $r }} hari</option>
            @endforeach
        </select>
    </div>
    <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">Tampilkan</button>
</form>

{{-- Summary cards --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Kedatangan</p>
        <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $arrivals->count() }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">In-House (sekarang)</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $inHouse->count() }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Keberangkatan</p>
        <p class="text-2xl font-bold text-rose-600 mt-1">{{ $departures->count() }}</p>
    </div>
</div>

{{-- Timeline --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5 mb-6">
    <h2 class="text-sm font-semibold text-gray-700 mb-4">Timeline {{ $days }} Hari</h2>
    <div class="space-y-2">
        @foreach($timeline as $day)
        @php
            $max = max(1, $day['arrivals'], $day['departures'], $day['in_house']);
            $aPct = round($day['arrivals'] / $max * 100);
            $dPct = round($day['departures'] / $max * 100);
            $iPct = round($day['in_house'] / $max * 100);
        @endphp
        <div class="grid grid-cols-[90px_1fr] gap-3 items-center">
            <div class="text-xs text-gray-500 font-medium">{{ $day['date']->format('d M') }}</div>
            <div class="flex items-center gap-1 h-6 rounded-lg overflow-hidden bg-gray-50">
                @if($day['arrivals'])
                <div class="h-full bg-indigo-500 flex items-center justify-center text-[9px] text-white font-semibold" style="width: {{ $aPct }}%">{{ $day['arrivals'] }}</div>
                @endif
                @if($day['in_house'])
                <div class="h-full bg-emerald-500 flex items-center justify-center text-[9px] text-white font-semibold" style="width: {{ $iPct }}%">{{ $day['in_house'] }}</div>
                @endif
                @if($day['departures'])
                <div class="h-full bg-rose-500 flex items-center justify-center text-[9px] text-white font-semibold" style="width: {{ $dPct }}%">{{ $day['departures'] }}</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    <div class="flex items-center gap-4 mt-4 text-xs text-gray-500">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-indigo-500"></span>Kedatangan</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-emerald-500"></span>In-house</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-rose-500"></span>Keberangkatan</span>
    </div>
</div>

{{-- Tabs --}}
<div class="mb-4 flex gap-2">
    @foreach(['arrivals' => 'Kedatangan', 'in_house' => 'In-House', 'departures' => 'Keberangkatan'] as $key => $label)
    <a href="{{ request()->fullUrlWithQuery(['status' => $key]) }}"
       class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors {{ $status === $key ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">{{ $label }}</a>
    @endforeach
</div>

{{-- Detail table --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Ref</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tamu</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Check-in</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Check-out</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @php
                    $list = $status === 'departures' ? $departures : ($status === 'in_house' ? $inHouse : $arrivals);
                @endphp
                @forelse ($list as $r)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3 font-mono text-xs font-semibold text-indigo-600">{{ $r->ref }}</td>
                    <td class="px-4 py-3 text-gray-900">{{ $r->primaryGuest?->full_name ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $r->check_in?->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $r->check_out?->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $r->status }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
