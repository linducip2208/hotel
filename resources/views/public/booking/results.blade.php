@extends('public.layout')
@section('title', 'Pilih Kamar')
@section('content')
<h1 class="text-2xl font-bold mb-1">Hasil Pencarian</h1>
<p class="text-sm text-gray-600 mb-4">{{ $data['check_in'] }} → {{ $data['check_out'] }} ({{ $nights }} malam, {{ $data['adults'] }} dewasa{{ ($data['children'] ?? 0) > 0 ? ', '.$data['children'].' anak' : '' }})</p>

@if ($errors->any())
    <div class="bg-red-50 text-red-800 border border-red-200 p-3 rounded mb-4">{{ $errors->first() }}</div>
@endif

<div class="grid md:grid-cols-2 gap-4">
@forelse ($roomTypes as $rt)
    <div class="bg-white border rounded p-4 {{ ! $rt->sellable ? 'opacity-60' : '' }}">
        <div class="flex items-start justify-between gap-2">
            <div>
                <h2 class="font-semibold">{{ $rt->name }}</h2>
                <p class="text-sm text-gray-600">Max {{ $rt->max_occupancy }} orang</p>
            </div>
            <span class="text-xs px-2 py-1 rounded-full {{ $rt->sellable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} whitespace-nowrap">
                {{ $rt->sellable ? 'Tersedia' : 'Penuh / Tidak dijual' }}
            </span>
        </div>

        @forelse ($rt->rate_plans as $plan)
            <div class="border rounded mt-3 p-3 {{ $plan['sellable'] ? '' : 'bg-gray-50' }}">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="font-medium text-sm">{{ $plan['name'] }}</p>
                        <p class="text-xs text-gray-500">
                            @if ($plan['breakfast_included']) Termasuk sarapan · @endif
                            {{ $plan['is_refundable'] ? 'Refundable' : 'Non-refundable' }}
                        </p>
                        @if (! $plan['sellable'])
                            <p class="text-xs text-red-600 mt-1">{{ $plan['block_reason'] }}</p>
                        @endif
                    </div>
                    <div class="text-right shrink-0">
                        <p class="font-bold text-primary-700">Rp {{ number_format($plan['total'], 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500">/ {{ $nights }} malam</p>
                    </div>
                </div>
                @if ($plan['sellable'])
                    <form method="GET" action="{{ route('booking.checkout') }}" class="mt-2">
                        <input type="hidden" name="check_in" value="{{ $data['check_in'] }}">
                        <input type="hidden" name="check_out" value="{{ $data['check_out'] }}">
                        <input type="hidden" name="room_type_id" value="{{ $rt->id }}">
                        <input type="hidden" name="rate_plan_id" value="{{ $plan['id'] }}">
                        <input type="hidden" name="adults" value="{{ $data['adults'] }}">
                        <input type="hidden" name="children" value="{{ $data['children'] ?? 0 }}">
                        <button class="bg-primary-600 hover:bg-primary-700 text-white text-sm px-4 py-2 rounded transition-colors" type="submit">Pilih Paket Ini</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-500 mt-3">Tidak ada rate plan aktif untuk tipe kamar ini.</p>
        @endforelse
    </div>
@empty
    <div class="col-span-2 text-center py-12">
        <p class="text-gray-600">Tidak ada kamar yang cocok untuk tanggal dan jumlah tamu ini.</p>
        <a href="{{ route('booking.search') }}" class="inline-block mt-3 text-primary-600 underline">Ubah pencarian</a>
    </div>
@endforelse
</div>
@endsection
