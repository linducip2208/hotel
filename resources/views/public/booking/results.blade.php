@extends('public.layout')
@section('title', 'Pilih Kamar')
@section('content')
<h1 class="text-2xl font-bold mb-1">Hasil Pencarian</h1>
<p class="text-sm text-gray-600 mb-4">{{ $data['check_in'] }} → {{ $data['check_out'] }} ({{ $nights }} malam, {{ $data['adults'] }} dewasa)</p>
<div class="grid md:grid-cols-2 gap-4">
@forelse ($roomTypes as $rt)
    <div class="bg-white border rounded p-4">
        <h2 class="font-semibold">{{ $rt->name }}</h2>
        <p class="text-sm text-gray-600">Max {{ $rt->max_occupancy }} orang</p>
        <p class="font-bold text-primary-700 mt-2">Total: Rp {{ number_format($rt->total_price, 0, ',', '.') }}</p>
        <form method="GET" action="{{ route('booking.checkout') }}" class="mt-3">
            <input type="hidden" name="check_in" value="{{ $data['check_in'] }}">
            <input type="hidden" name="check_out" value="{{ $data['check_out'] }}">
            <input type="hidden" name="room_type_id" value="{{ $rt->id }}">
            <input type="hidden" name="adults" value="{{ $data['adults'] }}">
            <input type="hidden" name="children" value="{{ $data['children'] ?? 0 }}">
            <button class="bg-primary-600 text-white px-4 py-2 rounded">Pilih</button>
        </form>
    </div>
@empty
    <p>Tidak ada kamar yang tersedia untuk tanggal ini.</p>
@endforelse
</div>
@endsection
