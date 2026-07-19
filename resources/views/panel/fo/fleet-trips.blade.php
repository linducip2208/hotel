@extends('panel.layout')
@section('title', 'Trip Transport')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Trip Transport</h1>
        <p class="text-sm text-gray-500 mt-0.5">Jadwalkan, kelola, dan lacak perjalanan</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.fo.fleet.dashboard') }}"
           class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Dashboard
        </a>
        <button onclick="document.getElementById('tripModal').classList.remove('hidden')"
                class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Trip Baru
        </button>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Daftar Trip</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kendaraan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Driver</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tamu</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Biaya</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($trips as $trip)
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
                    <td class="px-4 py-3 text-sm text-gray-800">{{ $trip->scheduled_at->format('d M H:i') }}</td>
                    <td class="px-4 py-3"><span class="text-xs font-medium bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full">{{ str_replace('_', ' ', $trip->trip_type) }}</span></td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $trip->vehicle?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $trip->driver?->employee?->full_name ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $trip->guest?->full_name ?? $trip->reservation?->primaryGuest?->full_name ?? '-' }}</td>
                    <td class="px-4 py-3"><span class="text-xs font-medium px-2 py-0.5 rounded-full border {{ $stClr }}">{{ $stLabel }}</span></td>
                    <td class="px-4 py-3 text-right text-sm text-gray-800 font-mono">Rp {{ number_format($trip->charge_amount, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-1">
                            @if($trip->status === 'scheduled')
                            <form method="POST" action="{{ route('panel.fo.fleet.trips.start', $trip->id) }}" class="inline">
                                @csrf
                                <button class="text-xs bg-blue-100 text-blue-700 hover:bg-blue-200 px-2 py-1 rounded-lg font-medium transition-colors">Mulai</button>
                            </form>
                            <form method="POST" action="{{ route('panel.fo.fleet.trips.cancel', $trip->id) }}" class="inline">
                                @csrf
                                <button class="text-xs bg-rose-100 text-rose-700 hover:bg-rose-200 px-2 py-1 rounded-lg font-medium transition-colors">Batal</button>
                            </form>
                            @endif
                            @if($trip->status === 'in_progress')
                            <form method="POST" action="{{ route('panel.fo.fleet.trips.complete', $trip->id) }}" class="inline">
                                @csrf
                                <button class="text-xs bg-emerald-100 text-emerald-700 hover:bg-emerald-200 px-2 py-1 rounded-lg font-medium transition-colors">Selesai</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-10 text-center text-sm text-gray-400">Belum ada trip.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($trips->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $trips->links() }}</div>
    @endif
</div>

<div id="tripModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="document.getElementById('tripModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900">Trip Baru</h3>
                <button onclick="document.getElementById('tripModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" action="{{ route('panel.fo.fleet.trips.store') }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tipe Trip <span class="text-red-500">*</span></label>
                        <select name="trip_type" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            <option value="airport_pickup">Airport Pickup</option>
                            <option value="airport_dropoff">Airport Dropoff</option>
                            <option value="city_tour">City Tour</option>
                            <option value="shuttle">Shuttle</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Waktu <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="scheduled_at" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kendaraan</label>
                        <select name="vehicle_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            <option value="">-- Pilih --</option>
                            @foreach($vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->plate_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Driver</label>
                        <select name="driver_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            <option value="">-- Pilih --</option>
                            @foreach($drivers as $d)
                            <option value="{{ $d->id }}">{{ $d->employee?->full_name ?? 'Driver #'.$d->id }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Penumpang</label>
                        <input type="number" name="passenger_count" value="1" min="1" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Biaya (Rp)</label>
                        <input type="number" name="charge_amount" value="0" min="0" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Lokasi Jemput</label>
                        <input type="text" name="pickup_location" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all" placeholder="Bandara, Hotel, dsb">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Lokasi Tujuan</label>
                        <input type="text" name="dropoff_location" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all" placeholder="Bandara, Hotel, dsb">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tamu (opsional)</label>
                    <select name="guest_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        <option value="">-- Pilih --</option>
                        @foreach($guests as $g)
                        <option value="{{ $g->id }}">{{ $g->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('tripModal').classList.add('hidden')" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
