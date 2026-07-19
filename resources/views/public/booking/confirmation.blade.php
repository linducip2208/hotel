@extends('public.layout')
@section('title', 'Booking Confirmed')
@section('content')
<div class="max-w-lg mx-auto bg-white border rounded p-6 text-center">
    <h1 class="text-2xl font-bold text-green-700 mb-2">🎉 Booking Confirmed</h1>
    <p>Reference: <code class="bg-gray-100 px-2">{{ $reservation->ref }}</code></p>
    <p class="mt-3">{{ $reservation->check_in->format('d M Y') }} → {{ $reservation->check_out->format('d M Y') }}</p>
    <p class="mt-1">{{ $reservation->primaryGuest->first_name }} {{ $reservation->primaryGuest->last_name }}</p>
    <p class="mt-3 text-lg font-bold">Total: Rp {{ number_format($reservation->grand_total, 0, ',', '.') }}</p>
</div>
@endsection
