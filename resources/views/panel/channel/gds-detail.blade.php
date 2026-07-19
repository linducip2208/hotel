@extends('panel.layout')
@section('title', 'GDS Booking Detail')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.channel.gds.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Detail GDS Booking</h1>
        <p class="text-sm text-gray-500 mt-0.5">Informasi lengkap booking dari sistem GDS</p>
    </div>
</div>

@php
    $booking = $booking ?? null;
    if (!$booking) {
        echo '<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-12 text-center text-gray-500">Data tidak ditemukan.</div>';
        return;
    }
    $gdsColors = [
        'sabre'      => 'blue',
        'amadeus'    => 'emerald',
        'travelport' => 'violet',
    ];
    $gdsKey = strtolower($booking->gds ?? '');
    $gc = $gdsColors[$gdsKey] ?? 'gray';
@endphp

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Main Info --}}
    <div class="lg:col-span-2 space-y-5">
        {{-- GDS Header Card --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
            <div class="flex items-start justify-between mb-5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-{{ $gc }}-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-{{ $gc }}-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-{{ $gc }}-50 text-{{ $gc }}-700 mb-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-{{ $gc }}-500"></span>
                            {{ \Illuminate\Support\Str::title($booking->gds ?? '—') }}
                        </span>
                        <h2 class="text-xl font-bold text-gray-900 font-mono tracking-wide uppercase">{{ $booking->booking_locator ?? '—' }}</h2>
                    </div>
                </div>
                <span class="text-xs text-gray-400">
                    ID: #{{ $booking->id }}
                </span>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
                <div>
                    <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold mb-1">Diterima</p>
                    <p class="text-sm font-semibold text-gray-800">
                        {{ $booking->received_at ? $booking->received_at->format('d M Y, H:i') : ($booking->created_at?->format('d M Y, H:i') ?? '—') }}
                    </p>
                </div>
                <div>
                    <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold mb-1">Dibuat</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $booking->created_at?->format('d M Y, H:i') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold mb-1">Diperbarui</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $booking->updated_at?->format('d M Y, H:i') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold mb-1">Status</p>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ ($booking->status ?? '') === 'processed' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ ($booking->status ?? '') === 'processed' ? 'Diproses' : ucfirst($booking->status ?? 'Pending') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Reservation Info --}}
        @if($booking->reservation)
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h2 class="text-sm font-bold text-gray-900">Reservasi Terkait</h2>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold mb-1">Ref</p>
                        <a href="{{ route('panel.fo.reservations.show', $booking->reservation_id) }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">
                            {{ $booking->reservation->ref ?? '#' . $booking->reservation_id }}
                        </a>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold mb-1">Tamu</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $booking->reservation->primaryGuest?->full_name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold mb-1">Check-in</p>
                        <p class="text-sm text-gray-700">{{ $booking->reservation->check_in?->format('d M Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold mb-1">Check-out</p>
                        <p class="text-sm text-gray-700">{{ $booking->reservation->check_out?->format('d M Y') ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Raw Payload --}}
        @if($booking->raw_payload ?? null)
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
                <h2 class="text-sm font-bold text-gray-900">Raw Payload</h2>
            </div>
            <div class="p-5">
                @php
                    $payload = is_string($booking->raw_payload) ? $booking->raw_payload : json_encode($booking->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                @endphp
                <pre class="text-xs text-gray-700 bg-gray-50 rounded-xl p-4 overflow-x-auto font-mono leading-relaxed max-h-96 overflow-y-auto scrollbar-thin">{{ $payload }}</pre>
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar Meta --}}
    <div class="space-y-5">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
            <h2 class="text-sm font-bold text-gray-900 mb-4">Informasi GDS</h2>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Sistem</span>
                    <span class="font-semibold text-gray-800">{{ \Illuminate\Support\Str::title($booking->gds ?? '—') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Locator</span>
                    <span class="font-mono text-xs text-gray-700 font-semibold uppercase">{{ $booking->booking_locator ?? '—' }}</span>
                </div>
                @if($booking->channel)
                <div class="flex justify-between">
                    <span class="text-gray-500">Channel</span>
                    <span class="font-medium text-gray-800">{{ $booking->channel->name }}</span>
                </div>
                @endif
                @if($booking->pseudo_city_code ?? null)
                <div class="flex justify-between">
                    <span class="text-gray-500">PCC</span>
                    <span class="font-mono text-xs text-gray-700">{{ $booking->pseudo_city_code }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
            <h2 class="text-sm font-bold text-gray-900 mb-4">Metadata</h2>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">ID</span>
                    <span class="font-mono text-gray-700">#{{ $booking->id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Dibuat</span>
                    <span class="text-gray-700">{{ $booking->created_at?->format('d M Y, H:i') ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Diperbarui</span>
                    <span class="text-gray-700">{{ $booking->updated_at?->format('d M Y, H:i') ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
