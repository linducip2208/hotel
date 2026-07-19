@extends('panel.layout')
@section('title', 'Fleet & Shuttle')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Fleet &amp; Shuttle</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manajemen transportasi, antar-jemput bandara, dan jadwal shuttle</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.fo.fleet.vehicles') }}"
           class="inline-flex items-center gap-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M4 17h12m0 0l-4 4m4-4l-4-4"/></svg>
            Kendaraan
        </a>
        <a href="{{ route('panel.fo.fleet.trips') }}"
           class="inline-flex items-center gap-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Semua Trip
        </a>
        <a href="{{ route('panel.fo.fleet.shuttle') }}"
           class="inline-flex items-center gap-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Shuttle
        </a>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Total Kendaraan</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_vehicles'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-emerald-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Aktif</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['active_vehicles'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-blue-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Sedang Trip</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['on_trip'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Tersedia</p>
        <p class="text-2xl font-bold text-gray-600 mt-1">{{ $stats['available_vehicles'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-indigo-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Total Driver</p>
        <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $stats['total_drivers'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-emerald-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Driver Tersedia</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['available_drivers'] }}</p>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">Trip Hari Ini</h2>
            <span class="text-xs text-gray-400 font-medium">{{ now()->format('d M Y') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kendaraan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Driver</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($todaysTrips as $trip)
                    @php
                        $stClr = match($trip->status) {
                            'scheduled' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'in_progress' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
                            default => 'bg-gray-50 text-gray-600 border-gray-200',
                        };
                        $stLabel = match($trip->status) {
                            'scheduled' => 'Terjadwal', 'in_progress' => 'Berjalan',
                            'completed' => 'Selesai', 'cancelled' => 'Batal',
                            default => $trip->status,
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $trip->scheduled_at->format('H:i') }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full">{{ str_replace('_', ' ', $trip->trip_type) }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $trip->vehicle?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $trip->driver?->employee?->full_name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full border {{ $stClr }}">{{ $stLabel }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-10 text-center text-sm text-gray-400">Belum ada trip hari ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Status Kendaraan</h2>
        </div>
        <div class="p-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach($vehicles as $v)
            @php
                $onTrip = $v->trips->where('status', 'in_progress')->isNotEmpty();
                $borderCls = $v->is_active ? ($onTrip ? 'border-blue-200 bg-blue-50' : 'border-emerald-200 bg-emerald-50') : 'border-gray-200 bg-gray-100';
                $dotCls = $v->is_active ? ($onTrip ? 'bg-blue-500' : 'bg-emerald-500') : 'bg-gray-400';
                $labelCls = $v->is_active ? ($onTrip ? 'text-blue-700' : 'text-emerald-700') : 'text-gray-500';
            @endphp
            <div class="rounded-xl border {{ $borderCls }} p-3 text-center">
                <div class="flex justify-center mb-1">
                    <span class="w-2.5 h-2.5 rounded-full {{ $dotCls }}"></span>
                </div>
                <p class="text-sm font-bold text-gray-900">{{ $v->name }}</p>
                <p class="text-xs {{ $labelCls }} font-medium">{{ $v->plate_number }}</p>
                <p class="text-[11px] text-gray-400 capitalize">{{ $v->type }} · {{ $v->capacity }} seats</p>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mt-6">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Driver</h2>
    </div>
    <div class="p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
        @foreach($drivers as $d)
        @php
            $available = $availableDrivers->contains('id', $d->id);
            $cardCls = $available ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50';
            $txtCls = $available ? 'text-emerald-700' : 'text-amber-700';
        @endphp
        <div class="rounded-xl border {{ $cardCls }} p-3 text-center">
            <div class="w-9 h-9 rounded-full bg-white border border-gray-200 flex items-center justify-center mx-auto mb-1.5">
                <span class="text-sm font-bold text-gray-700">{{ strtoupper(substr($d->employee?->full_name ?? 'D', 0, 1)) }}</span>
            </div>
            <p class="text-sm font-bold text-gray-900 truncate">{{ $d->employee?->full_name ?? 'Driver #'.$d->id }}</p>
            <p class="text-xs {{ $txtCls }} font-medium">{{ $available ? 'Tersedia' : 'Bertugas' }}</p>
            <p class="text-[11px] text-gray-400">{{ $d->license_number ? 'SIM: '.$d->license_number : '-' }}</p>
        </div>
        @endforeach
    </div>
</div>

@endsection
