@extends('panel.layout')
@section('title', 'Key Card Types')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Tipe Key Card</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola tipe kartu kunci</p>
    </div>
    <a href="{{ route('panel.fo.keycards') }}" class="inline-flex items-center gap-1.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
</div>

@if (session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-5 text-sm font-medium">
    {{ session('success') }}
</div>
@endif

{{-- Add New Type --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 mb-6">
    <h2 class="text-sm font-semibold text-gray-700 mb-4">Tambah Tipe Baru</h2>
    <form method="POST" action="{{ route('panel.fo.keycard-types') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
        @csrf
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Nama</label>
            <input type="text" name="name" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Contoh: Standard RFID">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Encoding</label>
            <select name="encoding_type" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="rfid">RFID</option>
                <option value="nfc">NFC</option>
                <option value="magnetic">Magnetic Stripe</option>
                <option value="ble">Bluetooth BLE</option>
                <option value="qr">QR Code</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Warna</label>
            <input type="text" name="color" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Contoh: Biru">
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium py-2.5 rounded-xl transition-colors shadow-sm">
                Tambah
            </button>
        </div>
    </form>
</div>

{{-- Types List --}}
@if ($types->isEmpty())
<div class="bg-white rounded-2xl shadow-card border border-gray-100 px-5 py-16 text-center">
    <p class="text-sm font-medium text-gray-700">Belum ada tipe kartu</p>
</div>
@else
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @foreach ($types as $type)
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-start justify-between mb-3">
            <div>
                <h3 class="text-base font-semibold text-gray-900">{{ $type->name }}</h3>
                <span class="text-xs font-medium text-gray-500 mt-0.5 block uppercase">{{ $type->encoding_type }}</span>
            </div>
            @if ($type->is_active)
            <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Aktif</span>
            @else
            <span class="text-xs font-medium text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">Nonaktif</span>
            @endif
        </div>
        @if ($type->color)
        <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
            <span class="w-4 h-4 rounded-full border" style="background-color: {{ $type->color }}"></span>
            {{ $type->color }}
        </div>
        @endif
        <div class="text-xs text-gray-500">
            <span class="font-semibold">{{ $type->inventory_count }}</span> kartu di inventory
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
