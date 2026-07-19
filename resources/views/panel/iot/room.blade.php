@extends('panel.layout')
@section('title', 'Room IoT - ' . $room->number)
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.iot.dashboard') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Room {{ $room->number }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">Floor {{ $room->floor ?? '-' }} · {{ $devices->count() }} perangkat</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    @forelse ($devices as $device)
    @php
        $state = $device->current_state ?? [];
        $isOnline = $device->status === 'online';
        $statusColor = $isOnline ? 'emerald' : 'rose';
    @endphp
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-{{ $statusColor }}-50 flex items-center justify-center">
                    @switch($device->device_type)
                        @case('thermostat') <span class="text-xl">🌡️</span> @break
                        @case('lighting') <span class="text-xl">💡</span> @break
                        @case('blinds') <span class="text-xl">🪟</span> @break
                        @case('tv') <span class="text-xl">📺</span> @break
                        @case('door_sensor') <span class="text-xl">🚪</span> @break
                        @case('motion_sensor') <span class="text-xl">👁️</span> @break
                        @case('energy_meter') <span class="text-xl">⚡</span> @break
                        @default <span class="text-xl">🔌</span>
                    @endswitch
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $device->name }}</p>
                    <p class="text-[11px] text-gray-400 capitalize">{{ str_replace('_', ' ', $device->device_type) }} · {{ $device->device_id }}</p>
                </div>
            </div>
            <span class="text-xs font-semibold bg-{{ $statusColor }}-50 text-{{ $statusColor }}-700 px-2.5 py-1 rounded-full capitalize">{{ $device->status }}</span>
        </div>

        {{-- Current state display --}}
        <div class="bg-gray-50 rounded-xl p-4 mb-4">
            <div class="grid grid-cols-2 gap-3 text-sm">
                @foreach ($state as $key => $val)
                <div>
                    <span class="text-xs text-gray-400 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                    <p class="font-semibold text-gray-800">{{ is_bool($val) ? ($val ? 'On' : 'Off') : $val }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Control buttons based on device type --}}
        <div class="flex flex-wrap gap-2">
            @if ($device->device_type === 'thermostat')
            <form method="POST" action="{{ route('panel.iot.command') }}" class="contents">
                @csrf
                <input type="hidden" name="device_id" value="{{ $device->id }}">
                <input type="hidden" name="command" value="set_temperature">
                <input type="hidden" name="payload[temperature]" value="18">
                <button class="text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg transition-colors">18°C</button>
            </form>
            <form method="POST" action="{{ route('panel.iot.command') }}" class="contents">
                @csrf
                <input type="hidden" name="device_id" value="{{ $device->id }}">
                <input type="hidden" name="command" value="set_temperature">
                <input type="hidden" name="payload[temperature]" value="23">
                <button class="text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-lg transition-colors">23°C</button>
            </form>
            <form method="POST" action="{{ route('panel.iot.command') }}" class="contents">
                @csrf
                <input type="hidden" name="device_id" value="{{ $device->id }}">
                <input type="hidden" name="command" value="set_temperature">
                <input type="hidden" name="payload[temperature]" value="28">
                <button class="text-xs font-semibold bg-orange-600 hover:bg-orange-700 text-white px-3 py-2 rounded-lg transition-colors">28°C</button>
            </form>
            @endif

            @if (in_array($device->device_type, ['lighting', 'tv']))
            <form method="POST" action="{{ route('panel.iot.command') }}" class="contents">
                @csrf
                <input type="hidden" name="device_id" value="{{ $device->id }}">
                <input type="hidden" name="command" value="turn_on">
                <button class="text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-lg transition-colors">Turn On</button>
            </form>
            <form method="POST" action="{{ route('panel.iot.command') }}" class="contents">
                @csrf
                <input type="hidden" name="device_id" value="{{ $device->id }}">
                <input type="hidden" name="command" value="turn_off">
                <button class="text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white px-3 py-2 rounded-lg transition-colors">Turn Off</button>
            </form>
            @endif

            @if ($device->device_type === 'lighting')
            <form method="POST" action="{{ route('panel.iot.command') }}" class="contents">
                @csrf
                <input type="hidden" name="device_id" value="{{ $device->id }}">
                <input type="hidden" name="command" value="set_brightness">
                <input type="hidden" name="payload[brightness]" value="30">
                <button class="text-xs font-semibold bg-slate-600 hover:bg-slate-700 text-white px-3 py-2 rounded-lg transition-colors">30%</button>
            </form>
            <form method="POST" action="{{ route('panel.iot.command') }}" class="contents">
                @csrf
                <input type="hidden" name="device_id" value="{{ $device->id }}">
                <input type="hidden" name="command" value="set_brightness">
                <input type="hidden" name="payload[brightness]" value="70">
                <button class="text-xs font-semibold bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded-lg transition-colors">70%</button>
            </form>
            <form method="POST" action="{{ route('panel.iot.command') }}" class="contents">
                @csrf
                <input type="hidden" name="device_id" value="{{ $device->id }}">
                <input type="hidden" name="command" value="set_brightness">
                <input type="hidden" name="payload[brightness]" value="100">
                <button class="text-xs font-semibold bg-amber-600 hover:bg-amber-700 text-white px-3 py-2 rounded-lg transition-colors">100%</button>
            </form>
            @endif

            @if ($device->device_type === 'blinds')
            <form method="POST" action="{{ route('panel.iot.command') }}" class="contents">
                @csrf
                <input type="hidden" name="device_id" value="{{ $device->id }}">
                <input type="hidden" name="command" value="open">
                <button class="text-xs font-semibold bg-sky-600 hover:bg-sky-700 text-white px-3 py-2 rounded-lg transition-colors">Open</button>
            </form>
            <form method="POST" action="{{ route('panel.iot.command') }}" class="contents">
                @csrf
                <input type="hidden" name="device_id" value="{{ $device->id }}">
                <input type="hidden" name="command" value="close">
                <button class="text-xs font-semibold bg-slate-600 hover:bg-slate-700 text-white px-3 py-2 rounded-lg transition-colors">Close</button>
            </form>
            @endif

            @if ($device->device_type === 'door_sensor')
            <form method="POST" action="{{ route('panel.iot.command') }}" class="contents">
                @csrf
                <input type="hidden" name="device_id" value="{{ $device->id }}">
                <input type="hidden" name="command" value="lock">
                <button class="text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white px-3 py-2 rounded-lg transition-colors">Lock</button>
            </form>
            <form method="POST" action="{{ route('panel.iot.command') }}" class="contents">
                @csrf
                <input type="hidden" name="device_id" value="{{ $device->id }}">
                <input type="hidden" name="command" value="unlock">
                <button class="text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-lg transition-colors">Unlock</button>
            </form>
            @endif
        </div>

        {{-- Last command log --}}
        @if ($device->commands->isNotEmpty())
        <div class="mt-4 pt-3 border-t border-gray-100">
            <p class="text-[11px] text-gray-400 mb-1">Riwayat perintah terakhir:</p>
            @foreach ($device->commands->take(3) as $cmd)
            <div class="text-[11px] text-gray-500 flex items-center gap-2">
                <span class="font-mono text-xs font-medium text-gray-700">{{ $cmd->command }}</span>
                <span class="text-gray-300">·</span>
                <span class="capitalize bg-{{ $cmd->status === 'executed' ? 'emerald' : 'gray' }}-50 text-{{ $cmd->status === 'executed' ? 'emerald' : 'gray' }}-600 px-1.5 py-0.5 rounded">{{ $cmd->status }}</span>
                <span class="text-gray-300">·</span>
                <span>{{ $cmd->created_at->diffForHumans() }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @empty
    <div class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <p class="text-base font-medium text-gray-500">Belum ada perangkat IoT</p>
        <p class="text-sm text-gray-400 mt-1">Tambahkan perangkat IoT untuk kamar ini.</p>
    </div>
    @endforelse
</div>

@endsection
