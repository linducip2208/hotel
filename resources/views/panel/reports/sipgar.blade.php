@extends('panel.layout')
@section('title', 'SIPGAR Report')
@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">SIPGAR — Kemenparekraf</h1>
        <p class="text-sm text-gray-500 mt-0.5">Sistem Informasi Pelaporan Kegiatan Usaha Pariwisata</p>
    </div>
    <div class="flex items-center gap-3">
        <form method="GET" class="flex items-center gap-2">
            <input type="month" name="month" value="{{ $month->format('Y-m') }}"
                   class="rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100">
            <button type="submit" class="bg-white border border-gray-200 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-xl transition-colors">
                View
            </button>
        </form>
    </div>
</div>

@if(isset($data))
<div class="grid md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Hotel Information</h2>
        </div>
        <div class="px-5 py-4 space-y-2.5">
            <div class="flex justify-between text-sm"><span class="text-gray-500">Hotel</span><span class="font-medium text-gray-800">{{ $data['property_name'] }}</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Rating</span><span class="font-medium text-gray-800">{{ $data['star_rating'] }} Star</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Address</span><span class="font-medium text-gray-800">{{ $data['address'] }}</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">City</span><span class="font-medium text-gray-800">{{ $data['city'] }}</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Province</span><span class="font-medium text-gray-800">{{ $data['province'] }}</span></div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Report Summary — {{ $month->format('F Y') }}</h2>
        </div>
        <div class="px-5 py-4 space-y-2.5">
            <div class="flex justify-between text-sm"><span class="text-gray-500">Total Rooms</span><span class="font-semibold text-gray-800">{{ $data['total_rooms'] }}</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Room Nights Available</span><span class="font-semibold text-gray-800">{{ $data['total_room_nights'] }}</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Room Nights Occupied</span><span class="font-semibold text-gray-800">{{ $data['occupied_room_nights'] }}</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Occupancy</span>
                <span class="font-semibold text-gray-800">{{ $data['occupancy_pct'] }}%</span>
            </div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Domestic Guests</span><span class="font-semibold text-gray-800">{{ $data['domestic_guests'] }}</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Foreign Guests</span><span class="font-semibold text-gray-800">{{ $data['foreign_guests'] }}</span></div>
            <div class="flex justify-between text-sm border-t border-gray-100 pt-2"><span class="text-gray-500">Total Revenue</span><span class="font-bold text-gray-900">Rp {{ number_format($data['total_revenue'], 0, ',', '.') }}</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Employees</span><span class="font-semibold text-gray-800">{{ $data['employee_count'] }}</span></div>
        </div>
    </div>
</div>

<div class="flex items-center gap-3">
    <form method="POST" action="{{ route('panel.reports.sipgar.export') }}">
        @csrf
        <input type="hidden" name="month" value="{{ $month->format('Y-m-d') }}">
        <input type="hidden" name="format" value="excel">
        <button type="submit" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export Excel
        </button>
    </form>
    <form method="POST" action="{{ route('panel.reports.sipgar.export') }}">
        @csrf
        <input type="hidden" name="month" value="{{ $month->format('Y-m-d') }}">
        <input type="hidden" name="format" value="csv">
        <button type="submit" class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
            Export CSV
        </button>
    </form>
</div>
@endif

@endsection
