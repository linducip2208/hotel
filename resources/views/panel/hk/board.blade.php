@extends('panel.layout')
@section('title', 'Housekeeping')
@section('content')

{{-- Page Header --}}
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Room Status Board</h1>
            <p class="text-sm text-gray-500 mt-1">Status kebersihan dan kondisi kamar real-time</p>
        </div>
        {{-- Legend --}}
        <div class="hidden md:flex items-center gap-3 flex-wrap justify-end">
            <div class="flex items-center gap-1.5 text-xs text-gray-600">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> Clean
            </div>
            <div class="flex items-center gap-1.5 text-xs text-gray-600">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span> Dirty
            </div>
            <div class="flex items-center gap-1.5 text-xs text-gray-600">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span> Inspected
            </div>
            <div class="flex items-center gap-1.5 text-xs text-gray-600">
                <span class="w-2.5 h-2.5 rounded-full bg-yellow-500 inline-block"></span> Cleaning
            </div>
            <div class="flex items-center gap-1.5 text-xs text-gray-600">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-400 inline-block"></span> OOO
            </div>
        </div>
    </div>
</div>

{{-- Stats Row --}}
@php
    $total     = $rooms->count();
    $clean     = $rooms->where('hk_status', 'clean')->count();
    $dirty     = $rooms->where('hk_status', 'dirty')->count();
    $inspected = $rooms->where('hk_status', 'inspected')->count();
    $cleaning  = $rooms->where('hk_status', 'cleaning')->count();
    $ooo       = $rooms->where('hk_status', 'out_of_order')->count();
@endphp
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-card text-center">
        <div class="text-2xl font-bold text-gray-900">{{ $total }}</div>
        <div class="text-xs text-gray-500 mt-0.5 font-medium">Total Kamar</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-card text-center">
        <div class="text-2xl font-bold text-emerald-600">{{ $clean }}</div>
        <div class="text-xs text-emerald-600 mt-0.5 font-medium">Clean</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-red-100 shadow-card text-center">
        <div class="text-2xl font-bold text-red-600">{{ $dirty }}</div>
        <div class="text-xs text-red-600 mt-0.5 font-medium">Dirty</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-blue-100 shadow-card text-center">
        <div class="text-2xl font-bold text-blue-600">{{ $inspected }}</div>
        <div class="text-xs text-blue-600 mt-0.5 font-medium">Inspected</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-card text-center">
        <div class="text-2xl font-bold text-gray-500">{{ $ooo }}</div>
        <div class="text-xs text-gray-500 mt-0.5 font-medium">Out of Order</div>
    </div>
</div>

{{-- Room Grid --}}
@if ($rooms->isEmpty())
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 px-5 py-16 text-center">
        <div class="flex flex-col items-center gap-3">
            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-700">Belum ada data kamar</p>
        </div>
    </div>
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
    @foreach ($rooms as $room)
        @php
            $hkStatus = $room->hk_status;
            $cardBorder = match ($hkStatus) {
                'clean'        => 'border-emerald-200 bg-emerald-50/40',
                'dirty'        => 'border-red-200 bg-red-50/40',
                'inspected'    => 'border-blue-200 bg-blue-50/40',
                'cleaning'     => 'border-yellow-200 bg-yellow-50/40',
                'out_of_order' => 'border-gray-200 bg-gray-50/60',
                default        => 'border-gray-200 bg-white',
            };
            $dotColor = match ($hkStatus) {
                'clean'        => 'bg-emerald-500',
                'dirty'        => 'bg-red-500',
                'inspected'    => 'bg-blue-500',
                'cleaning'     => 'bg-yellow-500',
                'out_of_order' => 'bg-gray-400',
                default        => 'bg-gray-300',
            };
            $hkBadge = match ($hkStatus) {
                'clean'        => 'bg-emerald-100 text-emerald-700',
                'dirty'        => 'bg-red-100 text-red-700',
                'inspected'    => 'bg-blue-100 text-blue-700',
                'cleaning'     => 'bg-yellow-100 text-yellow-700',
                'out_of_order' => 'bg-gray-100 text-gray-500',
                default        => 'bg-gray-100 text-gray-500',
            };
            $foLabel = match ($room->fo_status ?? '') {
                'occupied'  => ['Occupied', 'bg-blue-100 text-blue-700'],
                'vacant'    => ['Vacant',   'bg-gray-100 text-gray-500'],
                'departure' => ['Departure','bg-orange-100 text-orange-700'],
                'arrival'   => ['Arrival',  'bg-emerald-100 text-emerald-700'],
                default     => [ucfirst($room->fo_status ?? 'Vacant'), 'bg-gray-100 text-gray-500'],
            };
        @endphp
        <div class="rounded-2xl border shadow-card p-4 flex flex-col gap-3 {{ $cardBorder }}">
            {{-- Room number & type --}}
            <div class="flex items-start justify-between gap-1">
                <div>
                    <div class="text-xl font-bold text-gray-900 leading-none">#{{ $room->number }}</div>
                    <div class="text-xs text-gray-500 mt-1 truncate">{{ $room->roomType?->name ?? '—' }}</div>
                </div>
                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0 mt-1 {{ $dotColor }}"></span>
            </div>

            {{-- Badges --}}
            <div class="flex flex-col gap-1.5">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium self-start {{ $hkBadge }}">
                    {{ ucwords(str_replace('_', ' ', $hkStatus)) }}
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium self-start {{ $foLabel[1] }}">
                    {{ $foLabel[0] }}
                </span>
            </div>

            {{-- Status change --}}
            <form method="POST" action="{{ route('panel.hk.rooms.status', $room->id) }}">
                @csrf @method('PATCH')
                <select name="status" onchange="this.form.submit()"
                    class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white text-gray-700 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 cursor-pointer">
                    <option value="">Ubah status...</option>
                    <option value="clean"         {{ $hkStatus === 'clean'         ? 'selected' : '' }}>Clean</option>
                    <option value="dirty"         {{ $hkStatus === 'dirty'         ? 'selected' : '' }}>Dirty</option>
                    <option value="inspected"     {{ $hkStatus === 'inspected'     ? 'selected' : '' }}>Inspected</option>
                    <option value="cleaning"      {{ $hkStatus === 'cleaning'      ? 'selected' : '' }}>Cleaning</option>
                    <option value="out_of_order"  {{ $hkStatus === 'out_of_order'  ? 'selected' : '' }}>Out of Order</option>
                </select>
            </form>
        </div>
    @endforeach
    </div>
@endif

@endsection
