@extends('public.layout')
@section('content')
<div class="max-w-xl mx-auto bg-white border rounded p-6">
    <h1 class="text-2xl font-bold mb-2">Manage Booking</h1>
    <p class="font-mono">{{ $reservation->ref }}</p>
    <p>{{ $reservation->primaryGuest?->full_name }}</p>
    <p>{{ $reservation->check_in->format('d M Y') }} → {{ $reservation->check_out->format('d M Y') }}</p>
    <p class="font-bold mt-3">Total: Rp {{ number_format($reservation->grand_total, 0, ',', '.') }}</p>
    <p class="text-sm text-gray-600 mt-3">Untuk pre check-in / cancel / modify, gunakan link di email konfirmasi.</p>
</div>
@endsection
