@extends('public.layout')
@section('title', 'Cari Kamar')
@section('content')
<h1 class="text-2xl font-bold mb-4">Cari Kamar</h1>
<form method="POST" action="{{ route('booking.results') }}" class="bg-white border rounded p-4 max-w-lg space-y-3">
    @csrf
    <div class="grid grid-cols-2 gap-3">
        <div><label class="text-sm">Check-in</label><input type="date" name="check_in" required class="w-full border rounded p-2"></div>
        <div><label class="text-sm">Check-out</label><input type="date" name="check_out" required class="w-full border rounded p-2"></div>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div><label class="text-sm">Dewasa</label><input type="number" name="adults" min="1" value="2" required class="w-full border rounded p-2"></div>
        <div><label class="text-sm">Anak</label><input type="number" name="children" min="0" value="0" class="w-full border rounded p-2"></div>
    </div>
    <button class="bg-primary-600 text-white px-4 py-2 rounded">Cari Kamar</button>
</form>
@endsection
