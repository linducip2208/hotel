@extends('panel.layout')
@section('title', 'Jadwal Shuttle')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Jadwal Shuttle</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola jadwal shuttle rutin antar lokasi</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.fo.fleet.dashboard') }}" class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Dashboard
        </a>
        <button onclick="document.getElementById('shuttleModal').classList.remove('hidden')"
                class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Jadwal
        </button>
    </div>
</div>

@php
$dayNames = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
@endphp

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($schedules as $s)
    <div class="bg-white rounded-2xl border {{ $s->is_active ? 'border-gray-100' : 'border-gray-200 opacity-60' }} shadow-card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">{{ $s->route_name }}</h3>
                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($s->departure_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->arrival_time)->format('H:i') }}</p>
                </div>
            </div>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $s->is_active ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                {{ $s->is_active ? 'Aktif' : 'Non-Aktif' }}
            </span>
        </div>
        <div class="px-5 py-3 space-y-2">
            <div class="flex justify-between text-xs">
                <span class="text-gray-500">Dari</span>
                <span class="font-semibold text-gray-800">{{ $s->from_location }}</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-gray-500">Ke</span>
                <span class="font-semibold text-gray-800">{{ $s->to_location }}</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-gray-500">Kapasitas</span>
                <span class="font-semibold text-gray-800">{{ $s->capacity }} orang</span>
            </div>
            @if($s->days_of_week)
            <div class="flex flex-wrap gap-1 pt-1">
                @foreach($s->days_of_week as $d)
                <span class="text-[10px] font-medium bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded">{{ $dayNames[$d] ?? $d }}</span>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white rounded-2xl shadow-card border border-gray-100 p-12 flex flex-col items-center text-center">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <p class="text-base font-semibold text-gray-600">Belum ada jadwal shuttle</p>
        <p class="text-sm text-gray-400 mt-1">Tambahkan rute shuttle pertama Anda.</p>
    </div>
    @endforelse
</div>

<div id="shuttleModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('shuttleModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900">Tambah Jadwal Shuttle</h3>
                <button onclick="document.getElementById('shuttleModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" action="{{ route('panel.fo.fleet.shuttle.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Rute <span class="text-red-500">*</span></label>
                    <input type="text" name="route_name" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all" placeholder="Hotel - Bandara">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Dari <span class="text-red-500">*</span></label>
                        <input type="text" name="from_location" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Ke <span class="text-red-500">*</span></label>
                        <input type="text" name="to_location" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Berangkat <span class="text-red-500">*</span></label>
                        <input type="time" name="departure_time" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tiba <span class="text-red-500">*</span></label>
                        <input type="time" name="arrival_time" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kapasitas</label>
                    <input type="number" name="capacity" value="12" min="1" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Hari Operasi</label>
                    <div class="grid grid-cols-7 gap-1 mt-1">
                        @foreach($dayNames as $idx => $name)
                        <label class="flex flex-col items-center gap-0.5 text-[10px] font-medium text-gray-500 cursor-pointer">
                            <input type="checkbox" name="days_of_week[]" value="{{ $idx }}" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            {{ substr($name, 0, 3) }}
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('shuttleModal').classList.add('hidden')" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
