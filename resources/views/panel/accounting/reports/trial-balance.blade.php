@extends('panel.layout')
@section('title', 'Trial Balance')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Trial Balance</h1>
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

@php
    $totalDebit = $accounts->sum('total_debit');
    $totalCredit = $accounts->sum('total_credit');
    $balanced = abs($totalDebit - $totalCredit) < 0.01;
@endphp

@if (!$balanced)
<div class="mb-5 bg-red-50 border border-red-100 rounded-2xl px-5 py-3.5 flex items-center gap-3">
    <svg class="w-5 h-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
    <span class="text-sm font-medium text-red-700">Trial balance is out of balance — total debits do not equal total credits</span>
</div>
@endif

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Account</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Debit</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Credit</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($accounts as $a)
                @php $isNegative = $a->balance < 0; @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="font-mono text-xs text-gray-500 mr-2">{{ $a->code }}</span>
                        <span class="text-sm text-gray-800">{{ $a->name }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">
                        {{ $a->total_debit > 0 ? 'Rp '.number_format($a->total_debit, 0, ',', '.') : '—' }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">
                        {{ $a->total_credit > 0 ? 'Rp '.number_format($a->total_credit, 0, ',', '.') : '—' }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm font-semibold {{ $isNegative ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $isNegative ? '(' : '' }}Rp {{ number_format(abs($a->balance), 0, ',', '.') }}{{ $isNegative ? ')' : '' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-12 text-center text-sm text-gray-400">No account activity for this period.</td>
                </tr>
                @endforelse
            </tbody>
            @if ($accounts->count())
            <tfoot>
                <tr class="bg-gray-50/80 border-t-2 border-gray-200">
                    <td class="px-5 py-3.5 text-sm font-bold text-gray-700">Total</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm font-bold text-gray-900">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm font-bold text-gray-900">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
                    <td class="px-4 py-3.5 text-right">
                        @if ($balanced)
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Balanced
                        </span>
                        @else
                        <span class="text-xs font-semibold text-red-600">Out of balance</span>
                        @endif
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection
