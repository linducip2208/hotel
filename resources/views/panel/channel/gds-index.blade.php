@extends('panel.layout')
@section('title', 'GDS Booking')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">GDS Booking</h1>
        <p class="text-sm text-gray-500 mt-0.5">Booking masuk dari sistem distribusi global (Sabre, Amadeus, Travelport)</p>
    </div>
    <a href="{{ route('panel.channel.index') }}"
       class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 px-3.5 py-2 rounded-xl hover:bg-gray-50 shadow-card transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Channel Manager
    </a>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    @if(($bookings ?? collect())->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">GDS</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Booking Locator</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Reservasi</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Diterima</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($bookings as $booking)
                @php
                    $gdsColors = [
                        'sabre'      => 'blue',
                        'amadeus'    => 'emerald',
                        'travelport' => 'violet',
                    ];
                    $gdsKey = strtolower($booking->gds ?? '');
                    $gc = $gdsColors[$gdsKey] ?? 'gray';
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 text-sm text-gray-700 tabular-nums">#{{ $booking->id }}</td>
                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-{{ $gc }}-50 text-{{ $gc }}-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-{{ $gc }}-500"></span>
                            {{ \Illuminate\Support\Str::title($booking->gds ?? '—') }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-sm font-mono font-semibold text-gray-800 uppercase tracking-wide">
                        {{ $booking->booking_locator ?? '—' }}
                    </td>
                    <td class="px-4 py-3.5">
                        @if($booking->reservation)
                        <a href="{{ route('panel.fo.reservations.show', $booking->reservation_id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 hover:underline">
                            {{ $booking->reservation->ref ?? '#' . $booking->reservation_id }}
                        </a>
                        @else
                        <span class="text-sm text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">
                        {{ $booking->received_at ? $booking->received_at->format('d M Y, H:i') : ($booking->created_at?->format('d M Y, H:i') ?? '—') }}
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <a href="{{ route('panel.channel.gds.show', $booking->id) }}"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(($bookings ?? collect())->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
        {{ $bookings->links() }}
    </div>
    @endif
    @else
    <div class="flex flex-col items-center justify-center py-20">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mb-5">
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
            </svg>
        </div>
        <p class="text-base font-semibold text-gray-600">Belum ada GDS booking</p>
        <p class="text-sm text-gray-400 mt-1.5">Booking dari sistem GDS akan muncul di sini setelah diterima.</p>
    </div>
    @endif
</div>

@endsection
