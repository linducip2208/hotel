@extends('panel.layout')
@section('title', 'Group Block '.$block->block_code)
@section('content')

@php
    $statusBadge = match ($block->status) {
        'tentative' => 'bg-yellow-100 text-yellow-700',
        'definite' => 'bg-emerald-100 text-emerald-700',
        'cancelled' => 'bg-red-100 text-red-700',
        'completed' => 'bg-gray-100 text-gray-600',
        default => 'bg-gray-100 text-gray-500',
    };
    $statusLabel = match ($block->status) {
        'tentative' => 'Tentative',
        'definite' => 'Definite',
        'cancelled' => 'Dibatalkan',
        'completed' => 'Selesai',
        default => ucfirst($block->status),
    };
@endphp

<div class="flex items-start justify-between gap-4 mb-6">
    <div class="flex items-start gap-4">
        <a href="{{ route('panel.fo.group-blocks.index') }}"
           class="mt-0.5 p-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-500 transition flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-bold text-gray-900 font-mono">{{ $block->block_code }}</h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusBadge }}">{{ $statusLabel }}</span>
            </div>
            <p class="text-sm text-gray-500 mt-1">{{ $block->group_name }}</p>
        </div>
    </div>

    @if ($block->status === 'tentative')
    <form method="POST" action="{{ route('panel.fo.group-blocks.confirm', $block->id) }}" class="flex-shrink-0">
        @csrf
        <button class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Konfirmasi Block
        </button>
    </form>
    @endif
</div>

<div class="grid md:grid-cols-2 gap-4 mb-6">

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Detail Menginap</h2>
        </div>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Check-in</span>
                <span class="text-sm font-semibold text-gray-900">{{ $block->check_in->format('d M Y') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Check-out</span>
                <span class="text-sm font-semibold text-gray-900">{{ $block->check_out->format('d M Y') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Durasi</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">{{ $block->check_in->diffInDays($block->check_out) }} malam</span>
            </div>
            @if ($block->cutoff_date)
            <div class="flex items-center justify-between pt-1 border-t border-gray-100">
                <span class="text-sm text-gray-500">Batas Cutoff</span>
                <span class="text-sm font-semibold text-rose-600">{{ $block->cutoff_date->format('d M Y') }}</span>
            </div>
            @endif
            @if ($block->negotiated_rate)
            <div class="flex items-center justify-between pt-1 border-t border-gray-100">
                <span class="text-sm text-gray-500">Rate Negosiasi</span>
                <span class="text-sm font-semibold text-gray-800">Rp {{ number_format($block->negotiated_rate, 0, ',', '.') }} /malam</span>
            </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-xl bg-primary-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Ringkasan</h2>
        </div>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Total Kamar</span>
                <span class="text-lg font-bold text-gray-900">{{ $block->rooms_count }}</span>
            </div>
            @if ($block->company)
            <div class="flex items-center justify-between pt-1 border-t border-gray-100">
                <span class="text-sm text-gray-500">Perusahaan</span>
                <span class="text-sm font-medium text-gray-800">{{ $block->company->name }}</span>
            </div>
            @endif
            @if ($block->notes)
            <div class="pt-2 border-t border-gray-100">
                <p class="text-xs text-gray-400 mb-1">Catatan</p>
                <p class="text-sm text-gray-700">{{ $block->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Room Types List --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
        <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Alokasi Kamar</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe Kamar</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Dipickup</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Rate</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @foreach ($block->rooms as $room)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 font-medium text-gray-900">{{ $room->roomType?->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-center font-semibold text-gray-800">{{ $room->rooms_count }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="text-xs font-medium {{ $room->rooms_picked_up > 0 ? 'text-emerald-600 bg-emerald-50' : 'text-gray-400 bg-gray-50' }} px-2 py-0.5 rounded-full">{{ $room->rooms_picked_up }}</span>
                    </td>
                    <td class="px-5 py-3 text-right font-mono text-gray-800">{{ $room->rate ? 'Rp '.number_format($room->rate, 0, ',', '.') : '—' }}</td>
                    <td class="px-5 py-3 text-right">
                        @if ($block->status === 'tentative')
                        <form method="POST" action="{{ route('panel.fo.group-blocks.remove-room', [$block->id, $room->id]) }}" class="inline"
                              onsubmit="return confirm('Hapus alokasi kamar ini?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700 text-xs font-medium transition">Hapus</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($block->rooms->isEmpty())
    <div class="px-5 py-8 text-center text-sm text-gray-400">Belum ada alokasi kamar</div>
    @endif
</div>

{{-- Add Room Type --}}
@if ($block->status === 'tentative')
<div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 mb-6">
    <div class="px-5 py-4">
        <h3 class="text-sm font-semibold text-gray-700">Tambah Tipe Kamar</h3>
    </div>
    <form method="POST" action="{{ route('panel.fo.group-blocks.add-room', $block->id) }}" class="p-5">
        @csrf
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tipe Kamar <span class="text-red-500">*</span></label>
                <select name="room_type_id" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="">— pilih —</option>
                    @foreach ($roomTypes as $rt)
                    <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah <span class="text-red-500">*</span></label>
                <input type="number" name="rooms_count" value="1" required min="1"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rate (Rp/malam)</label>
                <input type="number" name="rate" step="0.01" min="0" placeholder="Rp"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
        </div>
        <button class="mt-4 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            Tambah Kamar
        </button>
    </form>
</div>
@endif

{{-- Available Rooms --}}
@if ($block->status === 'tentative')
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
        <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center">
            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
            </svg>
        </div>
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Kamar Tersedia (Clean)</h2>
        <span class="text-xs text-gray-400 ml-auto">Periode: {{ $block->check_in->format('d/m') }}–{{ $block->check_out->format('d/m') }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">No Kamar</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Lantai</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse ($availableRooms as $room)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 font-semibold text-gray-900">{{ $room->number }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $room->roomType?->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $room->floor }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-5 py-8 text-center text-sm text-gray-400">Tidak ada kamar tersedia untuk periode ini</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
