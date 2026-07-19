@extends('panel.layout')
@section('title', 'AR Invoices')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Accounts Receivable</h1>
        <p class="text-sm text-gray-500 mt-0.5">City ledger, OTA & corporate invoices</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Invoice</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Issued</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Due</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Balance</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($invoices as $i)
                @php
                    $isOverdue = $i->due_at->isPast() && in_array($i->status, ['unpaid', 'partial']);
                    $statusColors = ['paid' => 'emerald', 'partial' => 'amber', 'unpaid' => 'red', 'cancelled' => 'gray'];
                    $sc = $statusColors[$i->status] ?? 'gray';
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors {{ $isOverdue ? 'bg-red-50/30' : '' }}">
                    <td class="px-5 py-3.5">
                        <a href="{{ route('panel.accounting.ar.show', $i->id) }}"
                           class="font-mono text-sm font-medium text-primary-600 hover:text-primary-800">{{ $i->invoice_no }}</a>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">{{ $i->issued_at->format('d M Y') }}</td>
                    <td class="px-4 py-3.5">
                        <span class="text-sm {{ $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                            {{ $i->due_at->format('d M Y') }}
                        </span>
                        @if ($isOverdue)
                        <span class="ml-1 text-xs text-red-500">overdue</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-900">
                        Rp {{ number_format($i->grand_total, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm {{ (float)$i->balance > 0 ? 'text-red-600 font-semibold' : 'text-emerald-600' }}">
                        Rp {{ number_format($i->balance, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-0.5 rounded-full capitalize">{{ $i->status }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <a href="{{ route('panel.accounting.ar.show', $i->id) }}"
                           class="text-xs font-medium text-primary-600 bg-primary-50 px-2.5 py-1 rounded-lg hover:bg-primary-100 transition-colors">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-sm text-gray-400">No AR invoices found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($invoices->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
        {{ $invoices->links() }}
    </div>
    @endif
</div>

@endsection
