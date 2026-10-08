@extends('admin.layout')
@section('title', 'Failed Payments')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Failed Payments</h1>
    <p class="text-sm text-gray-500 mt-1">Langganan past-due dan faktur belum dibayar</p>
</div>

<h2 class="text-sm font-semibold text-gray-700 mb-2">Langganan Past Due</h2>
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tenant</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Plan</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Price (IDR)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($failed as $s)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-medium text-gray-900">{{ $s->tenant?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $s->plan?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-right font-mono">Rp {{ number_format($s->price_paid_idr, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="py-10 text-center text-sm text-gray-400">Tidak ada langganan past-due. 🎉</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<h2 class="text-sm font-semibold text-gray-700 mb-2">Faktur Belum Dibayar</h2>
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Invoice</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tenant</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Balance</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Due</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($unpaidInvoices as $inv)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-mono text-xs text-gray-800">{{ $inv->invoice_no }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $inv->tenant?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-right font-mono text-red-600">Rp {{ number_format($inv->balance, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-right text-gray-500">{{ $inv->due_at?->format('d M Y') ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="py-10 text-center text-sm text-gray-400">Semua faktur lunas. 🎉</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
