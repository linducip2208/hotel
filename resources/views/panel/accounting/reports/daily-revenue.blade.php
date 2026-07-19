@extends('panel.layout')
@section('title', 'Daily Revenue Report')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Daily Revenue Report</h1>
        <p class="text-sm text-gray-500 mt-0.5">Night audit summary and revenue breakdown</p>
    </div>
    <form method="GET" class="flex items-center gap-2">
        <input type="date" name="date" value="{{ request('date', today()->toDateString()) }}"
               class="rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm shadow-card focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition-all">
        <button type="submit"
                class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-sm transition-colors">
            View
        </button>
    </form>
</div>

@if ($audit)
@php
    $statusColors = ['completed' => 'emerald', 'running' => 'blue', 'failed' => 'red', 'pending' => 'amber'];
    $sc = $statusColors[$audit->status] ?? 'gray';
@endphp

{{-- Header card --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 mb-5">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-primary-50 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <div class="text-lg font-bold text-gray-900">{{ $audit->audit_date->isoFormat('dddd, D MMMM Y') }}</div>
                <div class="text-sm text-gray-500 mt-0.5">Audit ID #{{ $audit->id }}</div>
            </div>
        </div>
        <span class="inline-flex items-center gap-1.5 text-sm font-semibold bg-{{ $sc }}-50 text-{{ $sc }}-700 px-3.5 py-1.5 rounded-full capitalize">
            @if ($audit->status === 'completed')
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            @elseif ($audit->status === 'running')
            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
            @else
            <span class="w-2 h-2 rounded-full bg-{{ $sc }}-500"></span>
            @endif
            {{ $audit->status }}
        </span>
    </div>
</div>

@if ($audit->summary)
<div class="grid md:grid-cols-2 gap-5">

    {{-- Summary figures --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Revenue Summary</h2>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach ($audit->summary as $k => $v)
            @php
                $isTotal = str_contains(strtolower($k), 'total') || str_contains(strtolower($k), 'net');
                $isNegative = is_numeric($v) && $v < 0;
            @endphp
            <div class="flex items-center justify-between px-5 py-3 {{ $isTotal ? 'bg-gray-50/80' : 'hover:bg-gray-50/40' }} transition-colors">
                <span class="text-sm {{ $isTotal ? 'font-semibold text-gray-900' : 'text-gray-600' }} capitalize">
                    {{ str_replace(['_', '-'], ' ', $k) }}
                </span>
                <span class="font-mono text-sm {{ $isTotal ? 'font-bold text-gray-900' : ($isNegative ? 'text-red-600' : 'text-gray-800') }}">
                    @if (is_numeric($v))
                        {{ $isNegative ? '(' : '' }}Rp {{ number_format(abs($v), 0, ',', '.') }}{{ $isNegative ? ')' : '' }}
                    @else
                        {{ $v }}
                    @endif
                </span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Audit metadata --}}
    <div class="space-y-4">
        @if ($audit->status === 'completed')
        <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-semibold text-emerald-800">Audit Complete</div>
                    <div class="text-xs text-emerald-600 mt-0.5">All figures reconciled for this date</div>
                </div>
            </div>
        </div>
        @elseif ($audit->status === 'running')
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-semibold text-blue-800">Audit In Progress</div>
                    <div class="text-xs text-blue-600 mt-0.5">Night audit is currently running</div>
                </div>
            </div>
        </div>
        @endif

        @if ($audit->ran_by)
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Run By</div>
            <div class="flex items-center gap-3">
                @php $initials = collect(explode(' ', $audit->ranBy?->name ?? 'System'))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode(''); @endphp
                <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-bold shrink-0">
                    {{ $initials }}
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-900">{{ $audit->ranBy?->name ?? 'System' }}</div>
                    @if ($audit->ran_at)
                    <div class="text-xs text-gray-500 mt-0.5">{{ $audit->ran_at->format('H:i, d M Y') }}</div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

</div>
@else
<div class="bg-white rounded-2xl shadow-card border border-gray-100 flex flex-col items-center justify-center py-12">
    <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center mb-3">
        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
    </div>
    <p class="text-sm font-medium text-gray-700">Audit Pending</p>
    <p class="text-xs text-gray-400 mt-1">Night audit has not been run yet for this date</p>
</div>
@endif

@else
<div class="bg-white rounded-2xl shadow-card border border-gray-100 flex flex-col items-center justify-center py-16">
    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
    </div>
    <p class="text-sm font-semibold text-gray-600">No audit for selected date</p>
    <p class="text-xs text-gray-400 mt-1">Select a date and run the night audit to generate a report</p>
</div>
@endif

@endsection
