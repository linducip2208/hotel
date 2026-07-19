@extends('panel.layout')
@section('title', 'Departures Today')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Departures Today</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
    </div>
    <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 text-sm font-semibold px-3 py-1.5 rounded-full">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12H3M11 18l-6-6 6-6"/></svg>
        {{ count($list) }} checking out
    </span>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    @forelse ($list as $r)
    @php
        $name = $r->primaryGuest?->full_name ?? 'Guest';
        $initials = collect(explode(' ', $name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
        $balance = (float) $r->balance;
        $hasBalance = $balance > 0;
        $isCheckedOut = $r->status === 'checked_out';
    @endphp
    <div class="flex items-center gap-4 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">

        {{-- Avatar --}}
        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold shrink-0">
            {{ $initials }}
        </div>

        {{-- Guest info --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-gray-900 truncate">{{ $name }}</span>
                @if ($isCheckedOut)
                <span class="text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">Checked Out</span>
                @else
                <span class="text-xs font-medium bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">In-house</span>
                @endif
            </div>
            <div class="flex items-center gap-3 mt-0.5">
                <span class="text-xs font-mono text-gray-400">{{ $r->ref }}</span>
                @if ($r->rooms->count())
                <span class="text-xs text-gray-500">Room {{ $r->rooms->first()?->room?->number }}</span>
                @endif
                <span class="text-xs text-gray-400">{{ $r->nights }} night{{ $r->nights != 1 ? 's' : '' }}</span>
            </div>
        </div>

        {{-- Balance --}}
        <div class="text-right shrink-0">
            <div class="text-sm font-bold {{ $hasBalance ? 'text-red-600' : 'text-emerald-600' }} tabular-nums">
                Rp {{ number_format($balance, 0, ',', '.') }}
            </div>
            <div class="text-xs {{ $hasBalance ? 'text-red-400' : 'text-emerald-400' }}">
                {{ $hasBalance ? 'Outstanding' : 'Settled' }}
            </div>
        </div>

        {{-- Action --}}
        <a href="{{ route('panel.fo.reservations.show', $r->id) }}"
           class="shrink-0 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition-colors">
            View
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>

    </div>
    @empty
    <div class="flex flex-col items-center justify-center py-16 text-gray-400">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12H3M11 18l-6-6 6-6"/>
            </svg>
        </div>
        <p class="text-base font-medium text-gray-500">No departures today</p>
        <p class="text-sm text-gray-400 mt-1">All rooms continuing stay.</p>
    </div>
    @endforelse
</div>

@endsection
