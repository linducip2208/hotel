@extends('panel.layout')
@section('title', 'Arrivals Today')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Arrivals Today</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
    </div>
    <span class="inline-flex items-center gap-1.5 bg-primary-50 text-primary-700 text-sm font-semibold px-3 py-1.5 rounded-full">
        <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
        {{ count($list) }} expected
    </span>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    @forelse ($list as $r)
    @php
        $name = $r->primaryGuest?->full_name ?? 'Guest';
        $initials = collect(explode(' ', $name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
        $eta = optional($r->arrival_time)->format('H:i') ?? null;
        $isCheckedIn = $r->status === 'checked_in';
    @endphp
    <div class="flex items-center gap-4 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">

        {{-- Avatar --}}
        <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-bold shrink-0">
            {{ $initials }}
        </div>

        {{-- Guest info --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-gray-900 truncate">{{ $name }}</span>
                @if ($isCheckedIn)
                <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Checked In
                </span>
                @else
                <span class="text-xs font-medium bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Arriving</span>
                @endif
            </div>
            <div class="flex items-center gap-3 mt-0.5">
                <span class="text-xs font-mono text-gray-400">{{ $r->ref }}</span>
                <span class="text-xs text-gray-400">{{ $r->nights }} night{{ $r->nights != 1 ? 's' : '' }}</span>
                @if ($r->rooms->count())
                <span class="text-xs text-gray-500">Room {{ $r->rooms->first()?->room?->number }}</span>
                @endif
            </div>
        </div>

        {{-- ETA --}}
        <div class="text-right shrink-0">
            @if ($eta)
            <div class="text-sm font-semibold text-gray-800">{{ $eta }}</div>
            <div class="text-xs text-gray-400">ETA</div>
            @else
            <div class="text-xs text-gray-400">No ETA</div>
            @endif
        </div>

        {{-- Action --}}
        <a href="{{ route('panel.fo.reservations.show', $r->id) }}"
           class="shrink-0 inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 bg-primary-50 px-3 py-1.5 rounded-lg hover:bg-primary-100 transition-colors">
            View
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>

    </div>
    @empty
    <div class="flex flex-col items-center justify-center py-16 text-gray-400">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M13 6l6 6-6 6"/>
            </svg>
        </div>
        <p class="text-base font-medium text-gray-500">No arrivals today</p>
        <p class="text-sm text-gray-400 mt-1">All caught up for today.</p>
    </div>
    @endforelse
</div>

@endsection
