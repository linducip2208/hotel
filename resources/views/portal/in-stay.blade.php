@extends('public.layout')
@section('content')
<div class="max-w-xl mx-auto bg-white border rounded p-6">
    <h1 class="text-2xl font-bold mb-3">In-Stay Companion</h1>
    <p>Welcome, {{ $reservation->primaryGuest?->first_name }}.</p>
    <ul class="mt-3 space-y-2 text-sm">
        <li>🛎 Concierge — request via WhatsApp</li>
        <li>🍽 Room service menu (link)</li>
        <li>🗺 Local guide</li>
        <li>💵 Folio & balance</li>
    </ul>
</div>
@endsection
