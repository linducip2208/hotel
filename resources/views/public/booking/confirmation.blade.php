@extends('public.layout')
@section('title', 'Booking Confirmed')
@section('content')
<div class="max-w-lg mx-auto bg-white border rounded p-6 text-center">
    <h1 class="text-2xl font-bold text-green-700 mb-2">🎉 Booking Confirmed</h1>
    <p>Reference: <code class="bg-gray-100 px-2">{{ $reservation->ref }}</code></p>
    <p class="mt-3">{{ $reservation->check_in->format('d M Y') }} → {{ $reservation->check_out->format('d M Y') }}</p>
    <p class="mt-1">{{ $reservation->primaryGuest->first_name }} {{ $reservation->primaryGuest->last_name }}</p>
    <p class="mt-3 text-lg font-bold">Total: Rp {{ number_format($reservation->grand_total, 0, ',', '.') }}</p>
    <a href="{{ route('booking.ical', $reservation->ref) }}"
       class="mt-5 inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Tambahkan ke Kalender (iCal)
    </a>
</div>
@endsection
