@extends('panel.layout')
@section('title', 'Folio '.$folio->folio_no)
@section('content')

@php
    $statusColors = ['open' => 'emerald', 'settled' => 'blue', 'voided' => 'gray'];
    $sc = $statusColors[$folio->status] ?? 'gray';
    $hasBalance = $folio->balance > 0;
@endphp

{{-- Header --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.fo.reservations.show', $folio->reservation_id) }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div class="flex-1">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900">Folio {{ $folio->folio_no }}</h1>
            <span class="inline-flex items-center gap-1 text-xs font-semibold bg-{{ $sc }}-100 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">{{ $folio->status }}</span>
        </div>
        <p class="text-sm text-gray-500 mt-0.5">Reservation #{{ $folio->reservation?->ref }}</p>
    </div>
    <div class="flex gap-2">
        <form method="POST" action="{{ route('panel.print.folio', $folio->id) }}" class="inline no-print">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 px-3.5 py-2 rounded-xl hover:bg-gray-50 shadow-card transition-colors"
                    title="Cetak ke printer thermal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak
            </button>
        </form>
        <a href="{{ route('panel.fo.folios.invoice', $folio->id) }}"
           class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 px-3.5 py-2 rounded-xl hover:bg-gray-50 shadow-card transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Invoice
        </a>
        @if ($folio->status === 'open' && $folio->balance == 0)
        <form method="POST" action="{{ route('panel.fo.folios.settle', $folio->id) }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 px-3.5 py-2 rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Settle & Close
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Summary KPIs --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Charges</div>
        <div class="text-2xl font-bold text-gray-900 tabular-nums">Rp {{ number_format($folio->total_charges, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Payments</div>
        <div class="text-2xl font-bold text-emerald-600 tabular-nums">Rp {{ number_format($folio->total_payments, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-card border {{ $hasBalance ? 'border-red-100' : 'border-emerald-100' }}">
        <div class="text-xs font-semibold {{ $hasBalance ? 'text-red-500' : 'text-emerald-500' }} uppercase tracking-wide mb-1">Balance Due</div>
        <div class="text-2xl font-bold {{ $hasBalance ? 'text-red-600' : 'text-emerald-600' }} tabular-nums">Rp {{ number_format($folio->balance, 0, ',', '.') }}</div>
    </div>
</div>

{{-- Charges table --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-4">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Charges</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Description</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Category</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Amount</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Tax</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($folio->charges as $c)
                <tr class="hover:bg-gray-50/60 transition-colors {{ $c->is_void ? 'opacity-40' : '' }}">
                    <td class="px-5 py-3 text-gray-600">{{ $c->charge_date->format('d M') }}</td>
                    <td class="px-4 py-3 text-gray-800 {{ $c->is_void ? 'line-through' : '' }}">{{ $c->description }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $c->category }}</span>
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-gray-900">{{ number_format($c->amount, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-mono text-gray-500">{{ number_format($c->tax_amount, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-8 text-center text-sm text-gray-400">No charges yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Payments table --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Payments</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Method</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Reference</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($folio->payments as $p)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3 text-gray-600">{{ $p->payment_date->format('d M') }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-medium bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full capitalize">{{ $p->method }}</span>
                    </td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $p->reference_no ?: '—' }}</td>
                    <td class="px-4 py-3 text-right font-mono font-semibold text-emerald-700">{{ number_format($p->amount, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="py-8 text-center text-sm text-gray-400">No payments yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Action forms --}}
@if ($folio->status === 'open')
<div class="grid md:grid-cols-3 gap-4">

    {{-- Add Charge --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h3 class="text-sm font-semibold text-gray-700">Add Charge</h3>
        </div>
        <form method="POST" action="{{ route('panel.fo.folios.charges', $folio->id) }}" class="p-5 space-y-3">
            @csrf
            <input type="text" name="description" placeholder="Description" required
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            <select name="category"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                <option value="other">Other</option>
                <option value="fnb">F&B</option>
                <option value="laundry">Laundry</option>
                <option value="minibar">Minibar</option>
                <option value="addon">Add-on</option>
            </select>
            <input type="number" name="amount" placeholder="Amount (Rp)" step="0.01" required
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            <button class="w-full bg-primary-600 hover:bg-primary-700 text-white py-2 rounded-xl text-sm font-semibold transition-colors">Post Charge</button>
        </form>
    </div>

    {{-- Add Payment --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h3 class="text-sm font-semibold text-gray-700">Add Payment</h3>
        </div>
        <form method="POST" action="{{ route('panel.fo.folios.payments', $folio->id) }}" class="p-5 space-y-3">
            @csrf
            <input type="number" name="amount" placeholder="Amount (Rp)" step="0.01" required
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            <select name="method"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="qris">QRIS</option>
                <option value="transfer">Bank Transfer</option>
            </select>
            <input type="text" name="reference_no" placeholder="Reference no. (optional)"
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-xl text-sm font-semibold transition-colors">Post Payment</button>
        </form>
    </div>

    {{-- Apply Discount --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h3 class="text-sm font-semibold text-gray-700">Apply Discount</h3>
        </div>
        <form method="POST" action="{{ route('panel.fo.folios.discount', $folio->id) }}" class="p-5 space-y-3">
            @csrf
            <input type="number" name="amount" placeholder="Discount amount (Rp)" step="0.01" required
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            <input type="text" name="reason" placeholder="Reason" required
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            <button class="w-full bg-amber-500 hover:bg-amber-600 text-white py-2 rounded-xl text-sm font-semibold transition-colors">Apply Discount</button>
        </form>
    </div>

</div>
@endif

@endsection
