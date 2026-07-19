@extends('panel.layout')
@section('title', 'In-house Guests')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">In-house Guests</h1>
        <p class="text-sm text-gray-500 mt-0.5">Currently staying at the property</p>
    </div>
    <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-sm font-semibold px-3 py-1.5 rounded-full">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        {{ count($list) }} in-house
    </span>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    @forelse ($list as $r)
    @php
        $name = $r->primaryGuest?->full_name ?? 'Guest';
        $initials = collect(explode(' ', $name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
        $checkOut = $r->check_out;
        $isToday = $checkOut->isToday();
        $isTomorrow = $checkOut->isTomorrow();
        $roomNo = $r->rooms->first()?->room?->number ?? null;
    @endphp
    <div class="flex items-center gap-4 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">

        {{-- Avatar --}}
        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold shrink-0">
            {{ $initials }}
        </div>

        {{-- Guest info --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-gray-900 truncate">{{ $name }}</span>
                @if ($isToday)
                <span class="text-xs font-medium bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">Departing Today</span>
                @elseif ($isTomorrow)
                <span class="text-xs font-medium bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Departing Tomorrow</span>
                @endif
            </div>
            <div class="flex items-center gap-3 mt-0.5">
                <span class="text-xs font-mono text-gray-400">{{ $r->ref }}</span>
                @if ($roomNo)
                <span class="inline-flex items-center gap-1 text-xs text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md font-medium">
                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V10z"/></svg>
                    Rm {{ $roomNo }}
                </span>
                @endif
            </div>
        </div>

        {{-- Check-out --}}
        <div class="text-right shrink-0">
            <div class="text-sm font-semibold {{ $isToday ? 'text-orange-600' : 'text-gray-700' }}">
                {{ $checkOut->format('d M Y') }}
            </div>
            <div class="text-xs text-gray-400">Check-out</div>
        </div>

        {{-- Action --}}
        <a href="{{ route('panel.fo.reservations.show', $r->id) }}"
           class="shrink-0 inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg hover:bg-emerald-100 transition-colors">
            View
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>

    </div>
    @empty
    <div class="flex flex-col items-center justify-center py-16 text-gray-400">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            </svg>
        </div>
        <p class="text-base font-medium text-gray-500">No in-house guests</p>
        <p class="text-sm text-gray-400 mt-1">Hotel is currently unoccupied.</p>
    </div>
    @endforelse
</div>

@endsection
