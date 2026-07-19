@extends('panel.layout')
@section('title', 'IoT Dashboard')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">IoT & Smart Room</h1>
        <p class="text-sm text-gray-500 mt-0.5">Monitor & kontrol perangkat IoT seluruh kamar</p>
    </div>
    <div class="flex items-center gap-3 text-xs font-medium">
        <span class="bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-full">{{ $deviceCounts['online'] }} online</span>
        <span class="bg-gray-50 text-gray-600 px-3 py-1.5 rounded-full">{{ $deviceCounts['total'] }} total</span>
        @if ($deviceCounts['offline'] > 0)
        <span class="bg-rose-50 text-rose-700 px-3 py-1.5 rounded-full">{{ $deviceCounts['offline'] }} offline</span>
        @endif
    </div>
</div>

{{-- Stats cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Kamar Terhubung</p>
                <p class="text-xl font-bold text-gray-900">{{ $rooms->filter(fn($r) => $r->iotDevices->isNotEmpty())->count() }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Perangkat Aktif</p>
                <p class="text-xl font-bold text-gray-900">{{ $deviceCounts['online'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Offline</p>
                <p class="text-xl font-bold text-gray-900">{{ $deviceCounts['offline'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Total Perangkat</p>
                <p class="text-xl font-bold text-gray-900">{{ $deviceCounts['total'] }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Room grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    @forelse ($rooms as $room)
    <a href="{{ route('panel.iot.room', $room->id) }}"
       class="bg-white rounded-2xl shadow-card border border-gray-100 p-4 hover:shadow-md hover:border-indigo-200 transition-all group">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="text-sm font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors">Room {{ $room->number }}</p>
                <p class="text-xs text-gray-400">Floor {{ $room->floor ?? '-' }}</p>
            </div>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </div>
        @if ($room->iotDevices->isNotEmpty())
        <div class="flex flex-wrap gap-1.5">
            @foreach ($room->iotDevices as $device)
            @php
                $types = [
                    'thermostat' => ['🌡️', 'bg-orange-50 text-orange-700'],
                    'lighting' => ['💡', 'bg-yellow-50 text-yellow-700'],
                    'blinds' => ['🪟', 'bg-sky-50 text-sky-700'],
                    'tv' => ['📺', 'bg-slate-100 text-slate-700'],
                    'door_sensor' => ['🚪', 'bg-emerald-50 text-emerald-700'],
                    'motion_sensor' => ['👁️', 'bg-blue-50 text-blue-700'],
                    'energy_meter' => ['⚡', 'bg-violet-50 text-violet-700'],
                ];
                $info = $types[$device->device_type] ?? ['🔌', 'bg-gray-50 text-gray-600'];
            @endphp
            <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-1 rounded-lg {{ $info[1] }}">
                {{ $info[0] }} {{ $device->name }}
            </span>
            @endforeach
        </div>
        @else
        <p class="text-xs text-gray-400 italic">No IoT devices</p>
        @endif
    </a>
    @empty
    <div class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <p class="text-base font-medium text-gray-500">Belum ada perangkat IoT</p>
        <p class="text-sm text-gray-400 mt-1">Tambahkan perangkat IoT untuk kamar melalui database.</p>
    </div>
    @endforelse
</div>

@endsection
