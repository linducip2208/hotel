@extends('panel.layout')
@section('title', 'Invoice')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.accounting.ar.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900">{{ $invoice->invoice_no }}</h1>
            @php
                $sc = match($invoice->status ?? 'unpaid') { 'paid' => 'emerald', 'partial' => 'blue', 'overdue' => 'red', default => 'amber' };
            @endphp
            <span class="text-xs font-semibold bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize shrink-0">
                {{ $invoice->status ?? 'unpaid' }}
            </span>
        </div>
        @if ($invoice->guest)
        <p class="text-sm text-gray-500 mt-0.5">{{ $invoice->guest->full_name }}</p>
        @endif
    </div>
</div>

<div class="max-w-3xl space-y-5">

    {{-- Balance summary cards --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Total</div>
            <div class="text-xl font-bold text-gray-900 font-mono">Rp {{ number_format($invoice->grand_total ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Paid</div>
            <div class="text-xl font-bold text-emerald-700 font-mono">Rp {{ number_format(($invoice->grand_total ?? 0) - ($invoice->balance ?? 0), 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Balance Due</div>
            <div class="text-xl font-bold {{ ($invoice->balance ?? 0) > 0 ? 'text-red-600' : 'text-emerald-700' }} font-mono">Rp {{ number_format($invoice->balance ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Line items --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Line Items</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Description</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Qty</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Unit Price</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($invoice->lines as $l)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-3.5 text-sm text-gray-800">{{ $l->description }}</td>
                        <td class="px-4 py-3.5 text-center text-sm text-gray-600 tabular-nums">{{ $l->qty }}</td>
                        <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">Rp {{ number_format($l->unit_price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3.5 text-right font-mono text-sm font-medium text-gray-900">Rp {{ number_format($l->amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50/80 border-t border-gray-200">
                        <td colspan="3" class="px-5 py-3.5 text-sm font-bold text-gray-700 text-right">Grand Total</td>
                        <td class="px-4 py-3.5 text-right font-mono text-sm font-bold text-gray-900">
                            Rp {{ number_format($invoice->grand_total ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>

@endsection
