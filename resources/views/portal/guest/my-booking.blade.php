@extends('portal.guest-app-layout')
@section('title', 'Pesanan Saya')
@section('content')

<h1 class="text-xl font-bold text-stone-900 mb-4">Pesanan Saya</h1>

@if ($activeStay)
<div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl p-5 text-white shadow-lg shadow-emerald-500/30 mb-6">
    <div class="flex items-center justify-between mb-3">
        <span class="text-emerald-200 text-xs uppercase tracking-wide font-semibold">Sedang Menginap</span>
        <span class="bg-white/20 px-2.5 py-1 rounded-full text-xs font-semibold">{{ $activeStay->roomType?->name }}</span>
    </div>
    <p class="text-lg font-bold">Kamar {{ $activeStay->room?->number ?? '—' }}</p>
    <div class="flex items-center gap-4 mt-3 text-emerald-100 text-sm">
        <span>Check-in: {{ $activeStay->check_in?->format('d M Y') }}</span>
        <span>→</span>
        <span>Check-out: {{ $activeStay->check_out?->format('d M Y') }}</span>
    </div>
    @if ($activeStay->folios->isNotEmpty())
        @php $folio = $activeStay->folios->first(); $balance = $folio->balance ?? 0; @endphp
        <div class="mt-4 bg-white/10 rounded-xl p-3 flex items-center justify-between">
            <span class="text-emerald-100 text-sm">Total Tagihan</span>
            <span class="font-bold text-lg">Rp {{ number_format($balance, 0, ',', '.') }}</span>
        </div>
    @endif
</div>
@endif

{{-- Booking History --}}
<h2 class="text-sm font-semibold text-stone-500 uppercase tracking-wide mb-3">Riwayat Reservasi</h2>
<div class="space-y-3">
    @forelse ($bookings as $booking)
        @php
            $stBadge = match($booking->status) {
                'confirmed'   => 'bg-blue-100 text-blue-700',
                'checked_in'  => 'bg-emerald-100 text-emerald-700',
                'checked_out' => 'bg-stone-100 text-stone-500',
                'cancelled'   => 'bg-red-100 text-red-700',
                default       => 'bg-amber-100 text-amber-700',
            };
            $stLabel = match($booking->status) {
                'confirmed'   => 'Dikonfirmasi',
                'checked_in'  => 'Sedang Menginap',
                'checked_out' => 'Selesai',
                'cancelled'   => 'Dibatalkan',
                default       => ucfirst($booking->status),
            };
        @endphp
        <div class="bg-white rounded-2xl p-4 border border-stone-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="font-mono text-xs text-indigo-600 font-medium">{{ $booking->ref }}</span>
                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium {{ $stBadge }}">{{ $stLabel }}</span>
            </div>
            <p class="font-semibold text-stone-900">{{ $booking->roomType?->name }}</p>
            <div class="flex items-center gap-3 mt-2 text-sm text-stone-500">
                <span>{{ $booking->check_in?->format('d M Y') }}</span>
                <span>→</span>
                <span>{{ $booking->check_out?->format('d M Y') }}</span>
            </div>
            <div class="mt-2 text-sm font-semibold text-stone-700">
                Rp {{ number_format($booking->grand_total ?? 0, 0, ',', '.') }}
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl p-8 text-center border border-stone-100">
            <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-stone-100 flex items-center justify-center">
                <svg class="w-7 h-7 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <p class="text-sm font-medium text-stone-700">Belum ada reservasi</p>
            <p class="text-xs text-stone-400 mt-1">Reservasi Anda akan muncul di sini</p>
        </div>
    @endforelse
</div>

@endsection
