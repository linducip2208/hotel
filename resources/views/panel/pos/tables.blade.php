@extends('panel.layout')
@section('title', $outlet->name.' — Tables')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.pos.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-900">{{ $outlet->name }}</h1>
        <p class="text-sm text-gray-500 mt-0.5 capitalize">{{ str_replace('_', ' ', $outlet->outlet_type ?? 'outlet') }} · Table floor plan</p>
    </div>
</div>

{{-- Legend --}}
<div class="flex items-center gap-3 mb-4 text-xs font-medium">
    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-white border-2 border-emerald-300"></span>Available</span>
    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-orange-200 border-2 border-orange-300"></span>Occupied</span>
    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-gray-200 border-2 border-gray-300"></span>Reserved</span>
</div>

@if ($outlet->tables->isNotEmpty())
<div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
    @foreach ($outlet->tables as $t)
    @php
        $occupied = $t->status === 'occupied';
        $reserved = $t->status === 'reserved';
        if ($occupied) {
            $borderClass = 'border-orange-300 bg-orange-50';
            $dotClass = 'bg-orange-400';
            $textClass = 'text-orange-700';
        } elseif ($reserved) {
            $borderClass = 'border-gray-300 bg-gray-50';
            $dotClass = 'bg-gray-400';
            $textClass = 'text-gray-600';
        } else {
            $borderClass = 'border-emerald-200 bg-white';
            $dotClass = 'bg-emerald-500';
            $textClass = 'text-emerald-700';
        }
    @endphp
    <div class="relative rounded-2xl border-2 {{ $borderClass }} p-3 text-center shadow-card hover:shadow-card-hover transition-all cursor-pointer group">
        <div class="absolute top-2 right-2 w-2 h-2 rounded-full {{ $dotClass }}"></div>
        <div class="text-base font-bold text-gray-900 mb-0.5">{{ $t->label }}</div>
        <div class="text-xs {{ $textClass }} font-medium">{{ $t->seats }} seats</div>
        <div class="text-xs text-gray-400 capitalize mt-0.5">{{ $t->status }}</div>
        <div class="mt-2 pt-2 border-t border-gray-100">
            <a href="{{ route('qr-menu', [$outlet->id, $t->id]) }}" target="_blank"
               class="inline-flex items-center gap-1 text-[10px] text-primary-600 hover:text-primary-800 font-medium">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2.48a2.5 2.5 0 00-4.52 0H4m16 0a2.5 2.5 0 00-2.09-2.45V8.5a6 6 0 00-12 0v2.05A2.5 2.5 0 004 15h1"/></svg>
                QR Menu
            </a>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-12 flex flex-col items-center text-center">
    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M10 4v16M14 4v16"/>
        </svg>
    </div>
    <p class="text-base font-semibold text-gray-600">No tables configured</p>
    <p class="text-sm text-gray-400 mt-1">Add tables in Settings → POS outlets.</p>
</div>
@endif

@endsection
