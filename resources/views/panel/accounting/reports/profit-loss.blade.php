@extends('panel.layout')
@section('title', 'Profit & Loss')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Profit & Loss</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ \Carbon\Carbon::create($year, $month, 1)->isoFormat('MMMM Y') }}
        </p>
    </div>
    <form method="GET" class="flex items-center gap-2">
        <select name="month"
                class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm shadow-card focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition-all">
            @for ($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" @selected($m == $month)>{{ \Carbon\Carbon::create(null, $m, 1)->isoFormat('MMMM') }}</option>
            @endfor
        </select>
        <input type="number" name="year" value="{{ $year }}" min="2020" max="{{ date('Y') + 1 }}"
               class="w-24 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm shadow-card focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition-all">
        <button type="submit"
                class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-sm transition-colors">
            View
        </button>
    </form>
</div>

@php $netProfit = $revenue - $expense; @endphp

{{-- KPI cards --}}
<div class="grid grid-cols-3 gap-5 mb-5">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Revenue</span>
        </div>
        <div class="text-2xl font-bold text-emerald-700 font-mono">Rp {{ number_format($revenue, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Expenses</span>
        </div>
        <div class="text-2xl font-bold text-red-600 font-mono">Rp {{ number_format($expense, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border {{ $netProfit >= 0 ? 'border-emerald-100 bg-emerald-50/30' : 'border-red-100 bg-red-50/30' }} p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl {{ $netProfit >= 0 ? 'bg-emerald-100' : 'bg-red-100' }} flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-red-600' }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}</span>
        </div>
        <div class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-red-600' }} font-mono">
            {{ $netProfit < 0 ? '(' : '' }}Rp {{ number_format(abs($netProfit), 0, ',', '.') }}{{ $netProfit < 0 ? ')' : '' }}
        </div>
    </div>
</div>

{{-- P&L Statement --}}
<div class="max-w-xl">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Income Statement</h2>
        </div>
        <div class="divide-y divide-gray-50">
            <div class="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50/40 transition-colors">
                <span class="text-sm text-gray-700">Total Revenue</span>
                <span class="font-mono text-sm font-semibold text-emerald-700">Rp {{ number_format($revenue, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50/40 transition-colors">
                <span class="text-sm text-gray-700">Total Expenses</span>
                <span class="font-mono text-sm font-semibold text-red-600">(Rp {{ number_format($expense, 0, ',', '.') }})</span>
            </div>
            <div class="flex items-center justify-between px-5 py-4 bg-gray-50/80">
                <span class="text-sm font-bold text-gray-900">Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}</span>
                <span class="font-mono text-base font-bold {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                    {{ $netProfit < 0 ? '(' : '' }}Rp {{ number_format(abs($netProfit), 0, ',', '.') }}{{ $netProfit < 0 ? ')' : '' }}
                </span>
            </div>
        </div>
        @if ($revenue > 0)
        <div class="px-5 py-3 bg-gray-50/50 border-t border-gray-100">
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span>Net margin</span>
                <span class="font-semibold {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                    {{ number_format(($netProfit / $revenue) * 100, 1) }}%
                </span>
            </div>
            <div class="mt-1.5 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                <div class="h-full rounded-full {{ $netProfit >= 0 ? 'bg-emerald-500' : 'bg-red-400' }} transition-all"
                     style="width: {{ min(abs($netProfit / $revenue) * 100, 100) }}%"></div>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
