@extends('public.layout')
@section('content')
<div class="max-w-xl mx-auto bg-white border rounded p-6">
    <h1 class="text-2xl font-bold mb-3">Pre Check-in</h1>
    @if (session('status')) <div class="bg-green-50 text-green-800 p-2 rounded mb-3">{{ session('status') }}</div> @endif
    <form method="POST">
        @csrf
        <p class="mb-3">{{ $reservation->primaryGuest?->full_name }} · {{ $reservation->ref }}</p>
        <label class="text-sm">Estimated arrival time</label>
        <input type="datetime-local" name="arrival_time" required class="w-full border rounded p-2 mb-3">
        <button class="bg-primary-600 text-white px-4 py-2 rounded">Submit</button>
    </form>
</div>
@endsection
