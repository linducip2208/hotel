@extends('panel.layout')
@section('title', 'Detail Channel OTA')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.channel.dashboard') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Detail Channel OTA</h1>
        <p class="text-sm text-gray-500 mt-0.5">Informasi lengkap satu channel OTA</p>
    </div>
</div>

@php
     =  ?? null;
    if (!) {
        echo '<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-12 text-center text-gray-500">Data tidak ditemukan.</div>';
        return;
    }
     = ->is_active;
@endphp

{{-- Header --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{  ? 'bg-gradient-to-br from-indigo-500 to-violet-600' : 'bg-gray-300' }} flex items-center justify-center shadow-md {{  ? 'shadow-indigo-500/25' : '' }}">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-gray-900">{{ ->name }}</h2>
                    <span class="text-xs font-mono font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md uppercase">{{ ->code }}</span>
                </div>
                <div class="mt-1 flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-0.5 rounded-full {{  ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{  ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                        {{  ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    @if(->provider_name ?? null)
                    <span class="text-xs text-gray-400">{{ ->provider_name }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('panel.channel.mapping', ['channel' => ->id]) }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-4 py-2.5 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4"/></svg>
                Mapping
            </a>
        </div>
    </div>
</div>

{{-- Info Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <p class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold mb-1">Format API</p>
        <p class="text-base font-bold text-gray-800">{{ ->api_format ?? '-' }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <p class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold mb-1">Sinkron Terakhir</p>
        <p class="text-base font-bold text-gray-800">{{ ->last_sync_at?->diffForHumans() ?? 'Belum pernah' }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <p class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold mb-1">Mapping Kamar</p>
        <p class="text-base font-bold text-gray-800">{{ ->mappings_count ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <p class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold mb-1">Konflik</p>
        <p class="text-base font-bold {{ (->conflicts_count ?? 0) > 0 ? 'text-red-600' : 'text-gray-800' }}">{{ ->conflicts_count ?? 0 }}</p>
    </div>
</div>

{{-- 1. Mapping Kamar --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
        </div>
        <h2 class="text-sm font-bold text-gray-900">Mapping Kamar</h2>
        <span class="text-xs text-gray-400">({{ (->mappings ?? collect())->count() }})</span>
    </div>
    @php  = ->mappings ?? collect(); @endphp
    @if(->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe Kamar</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Rate Plan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">ID Kamar Channel</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">ID Rate Channel</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach(->take(20) as )
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3 text-sm text-gray-800">{{ ->roomType?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ ->ratePlan?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs font-mono text-gray-600">{{ ->channel_room_id ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs font-mono text-gray-600">{{ ->channel_rate_id ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ (->is_active ?? true) ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ (->is_active ?? true) ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(->count() > 20)
    <div class="px-5 py-3 border-t border-gray-100 text-center">
        <a href="{{ route('panel.channel.mapping', ['channel' => ->id]) }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Lihat semua mapping &rarr;</a>
    </div>
    @endif
    @else
    <div class="flex flex-col items-center justify-center py-12">
        <p class="text-sm text-gray-400">Belum ada mapping kamar</p>
    </div>
    @endif
</div>

{{-- 2. Log Sinkronisasi --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </div>
        <h2 class="text-sm font-bold text-gray-900">Log Sinkronisasi</h2>
        <span class="text-xs text-gray-400">({{ ( ?? collect())->count() }})</span>
    </div>
    @php  =  ?? collect(); @endphp
    @if(->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Waktu</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Operasi</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach(->take(20) as )
                @php  = ->status === 'success'; @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3 text-xs font-mono text-gray-500">{{ ->created_at->format('d M H:i:s') }}</td>
                    <td class="px-4 py-3"><code class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-mono">{{ ->operation }}</code></td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full {{  ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                            {{  ? 'Sukses' : 'Gagal' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="flex flex-col items-center justify-center py-12">
        <p class="text-sm text-gray-400">Belum ada log sinkronisasi</p>
    </div>
    @endif
</div>

{{-- 3. Konflik --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h2 class="text-sm font-bold text-gray-900">Konflik</h2>
        <span class="text-xs text-gray-400">({{ ( ?? collect())->count() }})</span>
    </div>
    @php  =  ?? collect(); @endphp
    @if(->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Detail</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach(->take(10) as )
                @php
                     = ['double_booking' => 'red', 'rate_mismatch' => 'amber', 'inventory_mismatch' => 'orange', 'restriction_conflict' => 'violet'];
                     = [->conflict_type] ?? 'gray';
                     = ->status === 'open';
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors {{  ? 'bg-red-50/20' : '' }}">
                    <td class="px-5 py-3.5">
                        <span class="text-xs font-semibold bg-{{  }}-50 text-{{  }}-700 px-2.5 py-1 rounded-full capitalize">
                            {{ str_replace('_', ' ', ->conflict_type) }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5">
                        <code class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded font-mono block max-w-[250px] truncate">{{ is_string(->details) ? ->details : json_encode(->details) }}</code>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if()
                        <span class="inline-flex items-center gap-1 text-xs font-medium bg-red-50 text-red-700 px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span> Open
                        </span>
                        @else
                        <span class="text-xs font-medium bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full capitalize">{{ ->status }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        @if()
                        <form method="POST" action="{{ route('panel.channel.conflicts.resolve', ->id) }}">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">Resolve</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="flex flex-col items-center justify-center py-12">
        <div class="w-10 h-10 rounded-2xl bg-emerald-50 flex items-center justify-center mb-2">
            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <p class="text-sm text-gray-400">Tidak ada konflik</p>
    </div>
    @endif
</div>

{{-- 4. Virtual Card --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        </div>
        <h2 class="text-sm font-bold text-gray-900">Virtual Card</h2>
        <span class="text-xs text-gray-400">({{ ( ?? collect())->count() }})</span>
    </div>
    @php  =  ?? collect(); @endphp
    @if(->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Reservasi</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Berlaku</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach(->take(10) as )
                @php
                     = ['active' => 'emerald', 'charged' => 'blue', 'expired' => 'gray', 'invalid' => 'red'][->status] ?? 'gray';
                     = ['active' => 'Aktif', 'charged' => 'Tertagih', 'expired' => 'Kedaluwarsa', 'invalid' => 'Tidak Valid'][->status] ?? ->status;
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3 text-sm text-gray-700">#{{ ->id }}</td>
                    <td class="px-4 py-3 text-sm text-indigo-600 font-medium">
                        @if(->reservation)
                        <a href="{{ route('panel.fo.reservations.show', ->reservation_id) }}" class="hover:underline">{{ ->reservation->ref ?? '#' . ->reservation_id }}</a>
                        @else - @endif
                    </td>
                    <td class="px-4 py-3 text-right text-sm text-gray-700 tabular-nums">Rp {{ number_format(->amount ?? 0, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ ->valid_until?->format('d M Y') ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-{{  }}-50 text-{{  }}-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-{{  }}-500"></span> {{  }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(->count() > 10)
    <div class="px-5 py-3 border-t border-gray-100 text-center">
        <a href="{{ route('panel.channel.vcc.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Lihat semua VCC &rarr;</a>
    </div>
    @endif
    @else
    <div class="flex flex-col items-center justify-center py-12">
        <p class="text-sm text-gray-400">Belum ada virtual card</p>
    </div>
    @endif
</div>

{{-- 5. Booking OTA --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-violet-100 flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <h2 class="text-sm font-bold text-gray-900">Booking OTA</h2>
        <span class="text-xs text-gray-400">({{ ( ?? collect())->count() }})</span>
    </div>
    @php  =  ?? collect(); @endphp
    @if(->isNotEmpty())
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
                @foreach(->take(10) as )
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3">
                        <a href="{{ route('panel.fo.reservations.show', ->id) }}" class="text-sm font-mono font-semibold text-indigo-600 hover:text-indigo-700">{{ ->ref ?? '#' . ->id }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-800">{{ ->primaryGuest?->full_name ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ ->check_in?->format('d M Y') ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ ->check_out?->format('d M Y') ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded-full">{{ ->status_label ?? ->status ?? '-' }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="flex flex-col items-center justify-center py-12">
        <p class="text-sm text-gray-400">Belum ada booking OTA</p>
    </div>
    @endif
</div>

{{-- 6. Parity Alerts --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h2 class="text-sm font-bold text-gray-900">Parity Alert</h2>
        <span class="text-xs text-gray-400">({{ ( ?? collect())->count() }})</span>
    </div>
    @php  =  ?? collect(); @endphp
    @if(->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe Kamar</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Harga Kita</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Harga Channel</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Selisih</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach(->take(5) as )
                @php  = (->channel_price ?? 0) - (->our_price ?? 0); @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3 text-sm text-gray-600">{{ ->target_date?->format('d M Y') ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800">{{ ->roomType?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-700 tabular-nums">Rp {{ number_format(->our_price ?? 0, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-700 tabular-nums">Rp {{ number_format(->channel_price ?? 0, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-sm font-semibold tabular-nums {{  > 0 ? 'text-red-600' : ( < 0 ? 'text-emerald-600' : 'text-gray-500') }}">
                            {{  >= 0 ? '+' : '' }}Rp {{ number_format(, 0, ',', '.') }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="flex flex-col items-center justify-center py-12">
        <p class="text-sm text-gray-400">Tidak ada parity alert</p>
    </div>
    @endif
</div>

@endsection
