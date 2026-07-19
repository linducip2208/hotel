@extends('panel.layout')
@section('title', 'AP Bill ' . $bill->bill_no)
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.accounting.ap.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900 font-mono">{{ $bill->bill_no }}</h1>
            @php
                $sc = match($bill->status ?? 'unpaid') { 'paid' => 'emerald', 'partial' => 'amber', 'overdue' => 'red', default => 'gray' };
            @endphp
            <span class="text-xs font-semibold bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">{{ $bill->status ?? 'unpaid' }}</span>
        </div>
        <p class="text-sm text-gray-500 mt-0.5">
            Supplier: <span class="font-medium text-gray-700">{{ $bill->supplier?->name ?? '—' }}</span>
            @if ($bill->due_date)
            · Due: {{ $bill->due_date->format('d M Y') }}
            @endif
        </p>
    </div>
</div>

<div class="max-w-3xl">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Bill Lines</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Description</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($bill->lines as $l)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-3.5 text-sm text-gray-700">{{ $l->description }}</td>
                        <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-800">Rp {{ number_format($l->amount, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="py-10 text-center text-sm text-gray-400">No line items.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if ($bill->lines->count())
                <tfoot>
                    <tr class="bg-gray-50/80 border-t border-gray-200">
                        <td class="px-5 py-3.5 text-sm font-bold text-gray-700">Total</td>
                        <td class="px-4 py-3.5 text-right font-mono text-sm font-bold text-gray-900">
                            Rp {{ number_format($bill->lines->sum('amount'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@endsection
