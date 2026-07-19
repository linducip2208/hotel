@extends('portal.guest-app-layout')
@section('title', 'Dashboard Tamu')
@section('content')

{{-- Welcome Card --}}
<div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-800 rounded-2xl p-5 md:p-8 text-white shadow-xl shadow-indigo-500/30 mb-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <p class="text-indigo-200 text-sm mb-1">Selamat datang kembali</p>
            <h1 class="text-xl md:text-2xl font-bold">{{ auth('customer')->user()->name ?? auth('customer')->user()->first_name }}</h1>
        </div>
        <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-xl font-bold">
            {{ strtoupper(substr(auth('customer')->user()->name ?? auth('customer')->user()->first_name ?? 'T', 0, 1)) }}
        </div>
    </div>
    @if ($activeStay)
    <div class="mt-5 bg-white/10 backdrop-blur rounded-xl p-4 border border-white/10">
        <div class="flex items-center gap-3 flex-wrap">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </div>
            <div>
                <p class="text-indigo-200 text-xs">Anda sedang menginap</p>
                <p class="font-bold">{{ $activeStay->roomType?->name }} — Kamar {{ $activeStay->room?->number ?? '—' }}</p>
                <p class="text-xs text-indigo-200">{{ $activeStay->check_in?->format('d M') }} — {{ $activeStay->check_out?->format('d M Y') }}</p>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Quick Actions Grid --}}
<h2 class="text-sm font-semibold text-stone-500 uppercase tracking-wide mb-3">Aksi Cepat</h2>
<div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3 mb-8">
    <a href="{{ route('customer.guest.room-service') }}" class="bg-white rounded-2xl p-4 border border-stone-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all text-center group">
        <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-amber-100 flex items-center justify-center group-hover:bg-amber-200 transition">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <span class="text-xs font-medium text-stone-700">Room Service</span>
    </a>
    <a href="{{ route('customer.guest.requests') }}" class="bg-white rounded-2xl p-4 border border-stone-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all text-center group">
        <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
        </div>
        <span class="text-xs font-medium text-stone-700">Housekeeping</span>
    </a>
    <a href="{{ route('customer.guest.requests') }}" class="bg-white rounded-2xl p-4 border border-stone-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all text-center group">
        <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-orange-100 flex items-center justify-center group-hover:bg-orange-200 transition">
            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
        </div>
        <span class="text-xs font-medium text-stone-700">Perbaikan</span>
    </a>
    <a href="{{ route('customer.bookings') }}" class="bg-white rounded-2xl p-4 border border-stone-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all text-center group">
        <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
        </div>
        <span class="text-xs font-medium text-stone-700">Lihat Tagihan</span>
    </a>
    <a href="{{ route('customer.guest.chat') }}" class="bg-white rounded-2xl p-4 border border-stone-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all text-center group">
        <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        </div>
        <span class="text-xs font-medium text-stone-700">Chat Resepsionis</span>
    </a>
</div>

{{-- Quick Info Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="bg-white rounded-2xl p-5 border border-stone-100 shadow-sm">
        <h3 class="text-sm font-semibold text-stone-700 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Info Hotel
        </h3>
        <div class="text-sm text-stone-600 space-y-2">
            <p>
                <span class="font-medium text-stone-700">Check-in:</span> 14:00 WIB
            </p>
            <p>
                <span class="font-medium text-stone-700">Check-out:</span> 12:00 WIB
            </p>
            <p>
                <span class="font-medium text-stone-700">Resepsionis:</span> 24 Jam · <a href="tel:+62" class="text-indigo-600 hover:underline">Hubungi</a>
            </p>
            <p>
                <span class="font-medium text-stone-700">WiFi:</span> {{ config('app.name') }} Guest · Password: hotel2024
            </p>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-stone-100 shadow-sm">
        <h3 class="text-sm font-semibold text-stone-700 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Sekitar Hotel
        </h3>
        <div class="text-sm text-stone-600 space-y-2">
            <p><span class="font-medium">Restoran:</span> 07:00 — 22:00 WIB</p>
            <p><span class="font-medium">Kolam Renang:</span> 06:00 — 20:00 WIB</p>
            <p><span class="font-medium">Spa:</span> 10:00 — 21:00 WIB (reservasi)</p>
            <p><span class="font-medium">Gym:</span> 24 Jam</p>
        </div>
    </div>
</div>

@endsection
