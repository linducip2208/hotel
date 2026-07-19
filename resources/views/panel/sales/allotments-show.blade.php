@extends('panel.layout')
@section('title', 'Alotmen #' . $allotment->id)
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.sales.allotments.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Alotmen #{{ $allotment->id }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">Detail room block &amp; pickup</p>
    </div>
</div>

@php $remaining = $allotment->remaining ?? ($allotment->rooms_blocked - $allotment->rooms_picked_up); @endphp

{{-- Header Card --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white text-lg font-bold shadow-md shadow-violet-500/25">
                {{ strtoupper(substr($allotment->travelAgent?->name ?? $allotment->company?->name ?? 'AL', 0, 2)) }}
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ $allotment->travelAgent?->name ?? $allotment->company?->name ?? 'Agent #' . $allotment->id }}
                    </h2>
                    @if($allotment->travelAgent)
                    <span class="text-xs font-semibold text-violet-600 bg-violet-50 px-2 py-0.5 rounded-full">Agen Travel</span>
                    @elseif($allotment->company)
                    <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">Perusahaan</span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-0.5">{{ $allotment->roomType?->name ?? 'Unknown Room Type' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold px-3 py-1.5 rounded-full {{ $remaining <= 0 ? 'bg-rose-50 text-rose-700' : ($remaining <= 2 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700') }}">
                {{ $remaining <= 0 ? 'Habis' : ($remaining <= 2 ? 'Hampir Habis' : 'Tersedia') }}
            </span>
        </div>
    </div>
</div>

{{-- Info Cards Grid --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Rooms Blocked --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ $allotment->rooms_blocked }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Kamar Diblok</p>
    </div>

    {{-- Rooms Picked Up --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-indigo-700 tabular-nums">{{ $allotment->rooms_picked_up }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Terpakai</p>
    </div>

    {{-- Remaining --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl {{ $remaining <= 0 ? 'bg-rose-50' : ($remaining <= 2 ? 'bg-amber-50' : 'bg-emerald-50') }} flex items-center justify-center">
                <svg class="w-5 h-5 {{ $remaining <= 0 ? 'text-rose-600' : ($remaining <= 2 ? 'text-amber-600' : 'text-emerald-600') }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold tabular-nums {{ $remaining <= 0 ? 'text-rose-600' : ($remaining <= 2 ? 'text-amber-600' : 'text-emerald-700') }}">
            {{ $remaining }}
        </p>
        <p class="text-xs text-gray-500 mt-0.5">Sisa Kamar</p>
    </div>

    {{-- Negotiated Rate --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900 tabular-nums">
            {{ $allotment->negotiated_rate ? 'Rp ' . number_format($allotment->negotiated_rate, 0, ',', '.') : '—' }}
        </p>
        <p class="text-xs text-gray-500 mt-0.5">Rate Negosiasi</p>
    </div>
</div>

{{-- Detail Info + Edit Form --}}
<div class="grid lg:grid-cols-3 gap-6 mb-6">
    {{-- Info Panel --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h2 class="text-sm font-bold text-gray-900">Detail Alotmen</h2>
        </div>
        <div class="p-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Dari Tanggal</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $allotment->from_date?->format('d M Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Sampai Tanggal</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $allotment->to_date?->format('d M Y') ?? '—' }}</p>
                </div>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Release Date</p>
                <p class="text-sm font-semibold text-gray-800">{{ $allotment->release_date?->format('d M Y') ?? 'Belum di-set' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Rate Plan</p>
                <p class="text-sm font-semibold text-gray-800">{{ $allotment->ratePlan?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Status</p>
                @php
                    $now = now()->startOfDay();
                    $from = $allotment->from_date?->startOfDay();
                    $to = $allotment->to_date?->startOfDay();
                    $statusLabel = 'Draft';
                    $statusColor = 'gray';
                    if ($from && $to) {
                        if ($now->lt($from)) { $statusLabel = 'Mendatang'; $statusColor = 'blue'; }
                        elseif ($now->lte($to)) { $statusLabel = 'Aktif'; $statusColor = 'emerald'; }
                        else { $statusLabel = 'Selesai'; $statusColor = 'gray'; }
                    }
                @endphp
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-{{ $statusColor }}-50 text-{{ $statusColor }}-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-{{ $statusColor }}-500"></span>
                    {{ $statusLabel }}
                </span>
            </div>
        </div>
    </div>

    {{-- Edit Form --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <h2 class="text-sm font-bold text-gray-900">Edit Alotmen</h2>
        </div>
        <form method="POST" action="{{ route('panel.sales.allotments.update', $allotment->id) }}" class="p-5 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Travel Agent</label>
                    <select name="travel_agent_id"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                        <option value="">Pilih Agen</option>
                        @foreach(\App\Models\TravelAgent::where('property_id', app('current_property')->id)->orderBy('name')->get() as $ta)
                        <option value="{{ $ta->id }}" {{ $allotment->travel_agent_id == $ta->id ? 'selected' : '' }}>{{ $ta->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Company</label>
                    <select name="company_id"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                        <option value="">Pilih Perusahaan</option>
                        @foreach(\App\Models\Company::where('property_id', app('current_property')->id)->orderBy('name')->get() as $comp)
                        <option value="{{ $comp->id }}" {{ $allotment->company_id == $comp->id ? 'selected' : '' }}>{{ $comp->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Room Type <span class="text-red-500">*</span></label>
                <select name="room_type_id" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                    <option value="">Pilih Tipe Kamar</option>
                    @foreach(\App\Models\RoomType::where('property_id', app('current_property')->id)->orderBy('name')->get() as $rt)
                    <option value="{{ $rt->id }}" {{ $allotment->room_type_id == $rt->id ? 'selected' : '' }}>{{ $rt->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Period <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <input type="date" name="from_date" value="{{ $allotment->from_date?->format('Y-m-d') }}" required
                           class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                    <input type="date" name="to_date" value="{{ $allotment->to_date?->format('Y-m-d') }}" required
                           class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Release Date</label>
                <input type="date" name="release_date" value="{{ $allotment->release_date?->format('Y-m-d') }}"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rooms Blocked <span class="text-red-500">*</span></label>
                    <input type="number" name="rooms_blocked" value="{{ $allotment->rooms_blocked }}" required min="1"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Negotiated Rate (Rp)</label>
                    <input type="number" step="1" name="negotiated_rate" value="{{ $allotment->negotiated_rate }}" placeholder="500000"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                </div>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="submit"
                        class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>

        <div class="flex gap-3 pt-1 px-5 pb-5">
            <form method="POST" action="{{ route('panel.sales.allotments.release', $allotment->id) }}" class="inline">
                @csrf
                <button type="submit"
                        class="text-sm font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 px-4 py-2.5 rounded-xl transition-colors">
                    Release Sekarang
                </button>
            </form>
            <form method="POST" action="{{ route('panel.sales.allotments.destroy', $allotment->id) }}" class="inline" onsubmit="return confirm('Hapus allotment ini?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="text-sm font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 px-4 py-2.5 rounded-xl transition-colors">
                    Hapus
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Back --}}
<div class="text-center">
    <a href="{{ route('panel.sales.allotments.index') }}"
       class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Daftar Alotmen
    </a>
</div>

@endsection
