@extends('panel.layout')
@section('title', 'BEO Sheet')
@section('content')

<div class="flex items-center justify-between mb-6 print:hidden">
    <div class="flex items-center gap-3">
        <a href="{{ route('panel.banquet.events.show', $event->id) }}"
           class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <h1 class="text-xl font-bold text-gray-900">BEO Sheet</h1>
    </div>
    <button onclick="window.print()"
            class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Print
    </button>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-8 max-w-3xl mx-auto print:shadow-none print:border-none print:rounded-none">

    {{-- BEO Header --}}
    <div class="text-center mb-8 pb-6 border-b-2 border-gray-200">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Banquet Event Order</p>
        <h1 class="text-3xl font-bold text-gray-900">{{ $event->title }}</h1>
        <p class="text-sm text-gray-500 mt-1.5 font-mono">{{ $event->event_no }}</p>
    </div>

    {{-- Event info grid --}}
    <div class="grid grid-cols-2 gap-x-8 gap-y-3 mb-8 text-sm">
        <div class="flex gap-3">
            <span class="font-semibold text-gray-500 w-28 shrink-0">Event Type</span>
            <span class="text-gray-800 capitalize">{{ str_replace('_', ' ', $event->event_type) }}</span>
        </div>
        <div class="flex gap-3">
            <span class="font-semibold text-gray-500 w-28 shrink-0">Status</span>
            <span class="text-gray-800 capitalize">{{ $event->status }}</span>
        </div>
        <div class="flex gap-3">
            <span class="font-semibold text-gray-500 w-28 shrink-0">Date</span>
            <span class="text-gray-800">{{ $event->event_date->isoFormat('dddd, D MMMM Y') }}</span>
        </div>
        <div class="flex gap-3">
            <span class="font-semibold text-gray-500 w-28 shrink-0">Time</span>
            <span class="font-mono text-gray-800">{{ $event->start_time?->format('H:i') }} – {{ $event->end_time?->format('H:i') }}</span>
        </div>
        <div class="flex gap-3">
            <span class="font-semibold text-gray-500 w-28 shrink-0">Function Room</span>
            <span class="text-gray-800">{{ $event->functionRoom?->name }}</span>
        </div>
        <div class="flex gap-3">
            <span class="font-semibold text-gray-500 w-28 shrink-0">Setup</span>
            <span class="text-gray-800 capitalize">{{ $event->setup }}</span>
        </div>
        <div class="flex gap-3">
            <span class="font-semibold text-gray-500 w-28 shrink-0">Attendees</span>
            <span class="text-gray-800 font-semibold">{{ $event->expected_attendees }} pax</span>
        </div>
    </div>

    {{-- F&B Menu --}}
    @if ($event->menuItems->count())
    <div class="mb-8">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 pb-2 border-b border-gray-200">F&B Menu</h2>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="pb-2 text-left">Item</th>
                    <th class="pb-2 text-center">Qty</th>
                    <th class="pb-2 text-right">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($event->menuItems as $m)
                <tr>
                    <td class="py-2 text-gray-800">{{ $m->name }}</td>
                    <td class="py-2 text-center text-gray-600">{{ $m->qty }}</td>
                    <td class="py-2 text-right font-mono text-gray-900">Rp {{ number_format($m->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Financial Summary --}}
    <div class="mb-8">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 pb-2 border-b border-gray-200">Financial Summary</h2>
        <div class="space-y-2 text-sm max-w-xs ml-auto">
            <div class="flex justify-between">
                <span class="text-gray-600">Venue Rate</span>
                <span class="font-mono">Rp {{ number_format($beo['totals']['venue'], 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">F&B Total</span>
                <span class="font-mono">Rp {{ number_format($beo['totals']['fnb'], 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Add-ons</span>
                <span class="font-mono">Rp {{ number_format($beo['totals']['addons'], 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between pt-2 mt-2 border-t-2 border-gray-900">
                <span class="font-bold text-gray-900 text-base">GRAND TOTAL</span>
                <span class="font-mono font-bold text-base text-gray-900">Rp {{ number_format($beo['totals']['grand_total'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="text-center text-xs text-gray-400 pt-6 border-t border-gray-200">
        Generated {{ now()->isoFormat('D MMMM Y, HH:mm') }}
    </div>

</div>

@endsection
