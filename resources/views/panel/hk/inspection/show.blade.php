@extends('panel.layout')
@section('title', 'Inspection Detail')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.hk.inspection.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Inspection Detail</h1>
        <p class="text-sm text-gray-500">Room {{ $inspection->room?->number ?? '—' }} · {{ $inspection->created_at->format('d M Y, H:i') }}</p>
    </div>
</div>

{{-- Summary card --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-card">
        <div class="text-xs text-gray-500 font-medium">Inspector</div>
        <div class="text-base font-semibold text-gray-900 mt-0.5">{{ $inspection->inspector?->name ?? '—' }}</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-card">
        <div class="text-xs text-gray-500 font-medium">Room</div>
        <div class="text-base font-semibold text-gray-900 mt-0.5">Room {{ $inspection->room?->number ?? '—' }} — {{ $inspection->room?->roomType?->name }}</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-card">
        <div class="text-xs text-gray-500 font-medium">Overall Status</div>
        @php
            $statusClass = match($inspection->overall_status) {
                'pass' => 'text-emerald-600 bg-emerald-50',
                'fail' => 'text-red-600 bg-red-50',
                default => 'text-amber-600 bg-amber-50',
            };
        @endphp
        <span class="inline-block mt-0.5 text-sm font-semibold px-2.5 py-0.5 rounded-full capitalize {{ $statusClass }}">{{ $inspection->overall_status }}</span>
    </div>
</div>

{{-- Checklist items --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-4">
    <div class="px-5 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-900">Checklist Items</h2>
    </div>
    <div class="divide-y divide-gray-50">
        @foreach ($inspection->items as $item)
        <div class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50/50">
            {{-- Status icon --}}
            <div class="shrink-0">
                @if ($item['status'] === 'pass')
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                @else
                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900">{{ $item['name'] }}</p>
                @if (!empty($item['comment']))
                <p class="text-xs text-gray-500 mt-0.5">{{ $item['comment'] }}</p>
                @endif
            </div>
            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $item['status'] === 'pass' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                {{ ucfirst($item['status']) }}
            </span>
        </div>
        @endforeach
    </div>
</div>

@if ($inspection->notes)
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
    <h3 class="text-sm font-semibold text-gray-900 mb-1">Notes</h3>
    <p class="text-sm text-gray-600">{{ $inspection->notes }}</p>
</div>
@endif

@endsection
