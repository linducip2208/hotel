@extends('panel.layout')
@section('title', 'Reservation '.$reservation->ref)
@section('content')

@php
    $badge = match ($reservation->status) {
        'confirmed'   => 'bg-emerald-100 text-emerald-700',
        'checked_in'  => 'bg-blue-100 text-blue-700',
        'checked_out' => 'bg-gray-100 text-gray-600',
        'cancelled'   => 'bg-red-100 text-red-700',
        'tentative'   => 'bg-yellow-100 text-yellow-700',
        'no_show'     => 'bg-orange-100 text-orange-700',
        default       => 'bg-gray-100 text-gray-500',
    };
    $label = match ($reservation->status) {
        'confirmed'   => 'Confirmed',
        'checked_in'  => 'Checked In',
        'checked_out' => 'Checked Out',
        'cancelled'   => 'Cancelled',
        'tentative'   => 'Tentative',
        'no_show'     => 'No Show',
        default       => ucfirst($reservation->status),
    };
@endphp

{{-- Page Header --}}
<div class="mb-6">
    <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-4">
            <a href="{{ route('panel.fo.reservations.index') }}"
               class="mt-0.5 p-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-500 transition flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-bold text-gray-900 font-mono">{{ $reservation->ref }}</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ $label }}</span>
                </div>
                <p class="text-sm text-gray-500 mt-1">Detail reservasi tamu</p>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            @if ($reservation->status === 'confirmed')
                <form method="POST" action="{{ route('panel.fo.reservations.check-in', $reservation->id) }}" class="inline">
                    @csrf
                    <button class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Check-in
                    </button>
                </form>
            @endif
            @if ($reservation->status === 'checked_in')
                <form method="POST" action="{{ route('panel.fo.reservations.check-out', $reservation->id) }}" class="inline">
                    @csrf
                    <button class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                        </svg>
                        Check-out
                    </button>
                </form>
            @endif
            @if (in_array($reservation->status, ['confirmed','checked_in']))
                <form method="POST" action="{{ route('panel.fo.reservations.cancel', $reservation->id) }}" class="inline"
                      onsubmit="return confirm('Yakin ingin membatalkan reservasi ini?')">
                    @csrf
                    <input type="hidden" name="reason" value="manual">
                    <button class="inline-flex items-center gap-1.5 border border-red-200 text-red-600 hover:bg-red-50 px-4 py-2 rounded-xl text-sm font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancel
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<div class="grid md:grid-cols-2 gap-4">

    {{-- Guest Info --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-xl bg-violet-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Tamu Utama</h2>
        </div>
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                <span class="text-lg font-bold text-primary-600">{{ strtoupper(substr($reservation->primaryGuest?->full_name ?? '?', 0, 1)) }}</span>
            </div>
            <div>
                <p class="font-semibold text-gray-900 text-base">{{ $reservation->primaryGuest?->full_name ?? '—' }}</p>
                <p class="text-sm text-gray-500 mt-0.5">{{ $reservation->primaryGuest?->email ?? '—' }}</p>
                <p class="text-sm text-gray-500">{{ $reservation->primaryGuest?->phone ?? '—' }}</p>
            </div>
        </div>
    </div>

    {{-- Stay Info --}}
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
                <span class="text-sm font-semibold text-gray-900">{{ $reservation->check_in->format('d M Y') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Check-out</span>
                <span class="text-sm font-semibold text-gray-900">{{ $reservation->check_out->format('d M Y') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Durasi</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">{{ $reservation->nights }} malam</span>
            </div>
            <div class="flex items-center justify-between pt-1 border-t border-gray-100">
                <span class="text-sm text-gray-500">Tamu</span>
                <span class="text-sm font-medium text-gray-800">{{ $reservation->adults }} dewasa{{ $reservation->children > 0 ? ', '.$reservation->children.' anak' : '' }}</span>
            </div>
        </div>
    </div>

    {{-- Rooms --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 md:col-span-2 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Kamar</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe Kamar</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Dewasa</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Anak</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @foreach ($reservation->rooms as $rr)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 font-medium text-gray-900">{{ $rr->roomType?->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-center text-gray-600">{{ $rr->adults }}</td>
                    <td class="px-5 py-3 text-center text-gray-600">{{ $rr->children }}</td>
                    <td class="px-5 py-3 text-right font-mono font-semibold text-gray-800">Rp {{ number_format($rr->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- Folios --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-yellow-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Folio</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse ($reservation->folios as $f)
                <a href="{{ route('panel.fo.folios.show', $f->id) }}"
                   class="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-xs font-semibold text-gray-700 bg-gray-100 px-2 py-1 rounded-lg">{{ $f->folio_no }}</span>
                        <span class="text-xs text-gray-500">{{ ucfirst($f->status) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-mono font-semibold text-gray-800">Rp {{ number_format($f->balance, 0, ',', '.') }}</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            @empty
                <div class="px-5 py-8 text-center text-sm text-gray-400">Belum ada folio</div>
            @endforelse
        </div>
    </div>

    {{-- Price Summary --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-xl bg-primary-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Ringkasan Biaya</h2>
        </div>
        <div class="space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Harga Kamar</span>
                <span class="text-sm font-mono text-gray-800">Rp {{ number_format($reservation->total_room, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Service Charge (10%)</span>
                <span class="text-sm font-mono text-gray-800">Rp {{ number_format($reservation->service_charge, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">PB1 Tax</span>
                <span class="text-sm font-mono text-gray-800">Rp {{ number_format($reservation->tax_total, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between pt-3 mt-1 border-t-2 border-gray-900">
                <span class="text-base font-bold text-gray-900">Grand Total</span>
                <span class="text-base font-bold font-mono text-primary-600">Rp {{ number_format($reservation->grand_total, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

</div>

@endsection
