@extends('panel.layout')
@section('title', 'Kendaraan Fleet')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Kendaraan</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola kendaraan fleet: mobil, van, bus, motor, golf cart</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.fo.fleet.dashboard') }}" class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Dashboard
        </a>
        <button onclick="document.getElementById('vehicleModal').classList.remove('hidden')"
                class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Kendaraan
        </button>
    </div>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($vehicles as $v)
    <div class="bg-white rounded-2xl border {{ $v->is_active ? 'border-gray-100' : 'border-gray-200 opacity-60' }} shadow-card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                    @php
                    $vIcon = match($v->type) {
                        'car' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>',
                        'van' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>',
                        'bus' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>',
                        default => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 10H6m12 2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4a2 2 0 012-2z"/>',
                    };
                    @endphp
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">{!! $vIcon !!}</svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">{{ $v->name }}</h3>
                    <p class="text-xs text-gray-500">{{ $v->plate_number }}</p>
                </div>
            </div>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $v->is_active ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                {{ $v->is_active ? 'Aktif' : 'Non-Aktif' }}
            </span>
        </div>
        <div class="px-5 py-3 space-y-2">
            <div class="flex justify-between text-xs">
                <span class="text-gray-500">Tipe</span>
                <span class="font-semibold text-gray-800 capitalize">{{ str_replace('_', ' ', $v->type) }}</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-gray-500">Kapasitas</span>
                <span class="font-semibold text-gray-800">{{ $v->capacity }} orang</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-gray-500">BBM</span>
                <span class="font-semibold text-gray-800">{{ $v->fuel_type ?? '-' }}</span>
            </div>
            @if($v->next_maintenance_due)
            <div class="flex justify-between text-xs">
                <span class="text-gray-500">Service Berikutnya</span>
                <span class="font-semibold {{ $v->next_maintenance_due->isPast() ? 'text-rose-600' : 'text-gray-800' }}">{{ $v->next_maintenance_due->format('d M Y') }}</span>
            </div>
            @endif
        </div>
        <div class="px-5 py-3 border-t border-gray-50 flex items-center gap-2">
            <button onclick="editVehicle({{ $v->id }}, '{{ $v->name }}', '{{ $v->plate_number }}', '{{ $v->type }}', {{ $v->capacity }}, '{{ $v->fuel_type }}', {{ $v->is_active ? 'true' : 'false' }})"
                    class="flex-1 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 py-2 rounded-lg transition-colors">Edit</button>
        </div>
    </div>
    @endforeach
</div>

<div id="vehicleModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('vehicleModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900">Tambah Kendaraan</h3>
                <button onclick="document.getElementById('vehicleModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" action="{{ route('panel.fo.fleet.vehicles.store') }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">No. Plat <span class="text-red-500">*</span></label>
                        <input type="text" name="plate_number" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tipe <span class="text-red-500">*</span></label>
                        <select name="type" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            <option value="car">Mobil</option>
                            <option value="van">Van</option>
                            <option value="bus">Bus</option>
                            <option value="motorcycle">Motor</option>
                            <option value="golf_cart">Golf Cart</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kapasitas <span class="text-red-500">*</span></label>
                        <input type="number" name="capacity" value="4" min="1" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jenis BBM</label>
                    <input type="text" name="fuel_type" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all" placeholder="Pertamax, Solar, Listrik">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Service Terakhir</label>
                        <input type="date" name="last_maintenance_at" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Service Berikutnya</label>
                        <input type="date" name="next_maintenance_due" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('vehicleModal').classList.add('hidden')" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editVehicle(id, name, plate, type, capacity, fuel, isActive) {
    alert('Edit kendaraan ID: ' + id + ' - ' + name + '\nGunakan form modal yang sama atau redirect ke halaman edit.');
}
</script>

@endsection
