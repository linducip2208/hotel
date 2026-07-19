@extends('public.layout', ['noPadding' => true])
@section('title', 'Registration Receipt')
@section('content')

<div class="max-w-md mx-auto p-6 print:p-2 bg-white">
    <div class="text-center mb-6">
        <h1 class="text-xl font-bold text-gray-900">{{ config('app.name') }}</h1>
        <p class="text-sm text-gray-500">Self Check-In Receipt</p>
    </div>

    <div class="border-t border-b border-gray-200 py-4 space-y-3 text-sm mb-6">
        <div class="flex justify-between"><span class="text-gray-500">Guest</span><span class="font-semibold text-gray-900">{{ $reservation->primaryGuest?->full_name }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Reservation</span><span class="font-mono text-gray-700">{{ $reservation->ref }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Check-in</span><span class="font-semibold">{{ $reservation->check_in?->format('d M Y') }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Check-out</span><span class="font-semibold">{{ $reservation->check_out?->format('d M Y') }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Room(s)</span><span class="font-semibold">{{ $reservation->rooms->pluck('room.room_number')->join(', ') }}</span></div>
    </div>

    <p class="text-xs text-gray-400 text-center">Generated: {{ now()->format('d M Y H:i') }}</p>
    <div class="text-center mt-6 print:hidden">
        <button onclick="window.print()" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-sm">Print</button>
    </div>
</div>

@endsection
