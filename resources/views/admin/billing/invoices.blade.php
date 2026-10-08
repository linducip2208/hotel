@extends('admin.layout')
@section('title', 'Invoices')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
    <p class="text-sm text-gray-500 mt-1">Faktur langganan tenant</p>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Invoice No</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tenant</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Balance</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Due</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($invoices as $inv)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-xs font-semibold text-gray-800">{{ $inv->invoice_no }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $inv->tenant?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-mono text-gray-800">Rp {{ number_format($inv->grand_total, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-mono {{ $inv->balance > 0 ? 'text-red-600' : 'text-emerald-600' }}">Rp {{ number_format($inv->balance, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $inv->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : ($inv->status === 'past_due' ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-500') }}">
                            {{ $inv->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-500">{{ $inv->due_at?->format('d M Y') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-12 text-center text-sm text-gray-400">Belum ada faktur.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($invoices->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
