@extends('panel.layout')
@section('title', 'Manajemen Parkir')
@section('content')

@php
$areaLabels = ['main' => 'Utama', 'basement' => 'Basement', 'vip' => 'VIP', 'outdoor' => 'Outdoor'];
$statusIcons = ['available' => '🟢', 'occupied' => '🔴', 'reserved' => '🟠', 'maintenance' => '⚫'];
@endphp

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Manajemen Parkir</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola slot parkir, check-in/out kendaraan, dan valet</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.fo.parking.valet') }}"
           class="inline-flex items-center gap-1.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            Valet
        </a>
        <button onclick="document.getElementById('checkinModal').classList.remove('hidden')"
                class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Check-in Kendaraan
        </button>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Total Slot</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-emerald-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Tersedia</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['available'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-rose-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Terisi</p>
        <p class="text-2xl font-bold text-rose-600 mt-1">{{ $stats['occupied'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-amber-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Reserved</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['reserved'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Maintenance</p>
        <p class="text-2xl font-bold text-gray-400 mt-1">{{ $stats['maintenance'] }}</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">

    {{-- Left: Parking Grid --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden mb-6">
            <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Slot Parkir</h2>
            </div>
            <div class="p-5">
                @foreach($slots as $area => $areaSlots)
                <div class="mb-5 last:mb-0">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                        {{ $areaLabels[$area] ?? $area }}
                        <span class="text-gray-400 normal-case">({{ count($areaSlots) }} slot)</span>
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($areaSlots as $slot)
                        @php
                        $color = match($slot['status']) {
                            'available' => 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
                            'occupied' => 'border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100',
                            'reserved' => 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100',
                            'maintenance' => 'border-gray-200 bg-gray-100 text-gray-500',
                            default => 'border-gray-200 bg-gray-50 text-gray-600',
                        };
                        @endphp
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-semibold cursor-pointer transition-colors {{ $color }}"
                             title="{{ $slot['slot_number'] }} — {{ $areaLabels[$area] ?? $area }} — {{ $slot['status'] }}">
                            <span class="w-1.5 h-1.5 rounded-full"
                                  style="background-color:{{ $slot['status'] === 'available' ? '#10b981' : ($slot['status'] === 'occupied' ? '#ef4444' : ($slot['status'] === 'reserved' ? '#f59e0b' : '#6b7280')) }}"></span>
                            {{ $slot['slot_number'] }}
                            @if($slot['is_vip'])
                            <span class="text-amber-500">⭐</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                @if(empty($slots))
                <p class="text-sm text-gray-400 text-center py-8">Belum ada slot parkir. Tambahkan melalui database terlebih dahulu.</p>
                @endif
            </div>
        </div>

        {{-- Active Vehicles Table --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
            <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Kendaraan Aktif</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Plat</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Slot</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tamu</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Durasi</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Tarif/Hari</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($activeRecords as $r)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3 font-medium text-gray-900">
                                {{ $r->vehicle_plate }}
                                @if($r->is_valet) <span class="text-xs text-amber-600 ml-1">Valet</span> @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full border border-indigo-100">{{ $r->parkingSlot?->slot_number ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $r->guest?->full_name ?? $r->reservation?->primaryGuest?->full_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600 capitalize">{{ $r->vehicle_type }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                @php
                                $checkIn = \Carbon\Carbon::parse($r->check_in);
                                $hours = $checkIn->diffInHours(now());
                                $days = floor($hours / 24);
                                $remHours = $hours % 24;
                                @endphp
                                @if($days > 0){{ $days }}h @endif{{ $remHours }}j
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-600">Rp {{ number_format($r->daily_rate, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('panel.fo.parking.checkout', $r->id) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 px-2.5 py-1.5 rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        Check-out
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-sm text-gray-400">Tidak ada kendaraan aktif.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right: Quick Check-in Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
            <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Slot Tersedia</h2>
            </div>
            <div class="p-4 max-h-64 overflow-y-auto">
                <div class="space-y-1">
                    @php $availableSlots = collect($slots)->flatten(1)->where('status', 'available'); @endphp
                    @forelse($availableSlots as $s)
                    <div class="flex items-center justify-between text-xs py-1.5 px-2 rounded-lg hover:bg-gray-50">
                        <span class="font-medium text-gray-700">{{ $s['slot_number'] }}</span>
                        <span class="text-gray-400">{{ $areaLabels[$s['area']] ?? $s['area'] }}</span>
                        @if($s['is_vip']) <span class="text-amber-500">⭐</span> @endif
                    </div>
                    @empty
                    <p class="text-xs text-gray-400 py-2">Semua slot terisi.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Check-in Modal --}}
<div id="checkinModal" class="hidden fixed inset-0 z-50 overflow-y-auto" x-data>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('checkinModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Check-in Kendaraan</h3>
                <button onclick="document.getElementById('checkinModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('panel.fo.parking.checkin') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Slot Parkir <span class="text-rose-500">*</span></label>
                    <select name="parking_slot_id" required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        <option value="">-- Pilih Slot --</option>
                        @foreach(collect($slots)->flatten(1)->where('status', 'available') as $s)
                        <option value="{{ $s['id'] }}">{{ $s['slot_number'] }} — {{ $areaLabels[$s['area']] ?? $s['area'] }}@if($s['is_vip']) (VIP)@endif</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">No Plat <span class="text-rose-500">*</span></label>
                    <input type="text" name="vehicle_plate" required
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                           placeholder="B 1234 XYZ">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tipe Kendaraan</label>
                        <select name="vehicle_type"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            <option value="car">Mobil</option>
                            <option value="motorcycle">Motor</option>
                            <option value="bus">Bus</option>
                            <option value="truck">Truk</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tarif/Hari (Rp)</label>
                        <input type="number" name="daily_rate" value="0" step="1000"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Merek</label>
                        <input type="text" name="vehicle_brand"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                               placeholder="Toyota">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Warna</label>
                        <input type="text" name="vehicle_color"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                               placeholder="Hitam">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tamu</label>
                    <select name="guest_id"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        <option value="">-- Tidak terkait tamu --</option>
                        @foreach($guests as $g)
                        <option value="{{ $g->id }}">{{ $g->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="is_valet" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-xs font-semibold text-gray-600">Valet</span>
                    </label>
                    <input type="text" name="valet_key_location"
                           class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                           placeholder="Lokasi kunci valet (cth: Loker A-3)">
                </div>
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Check-in
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
