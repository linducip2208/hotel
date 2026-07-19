@extends('panel.layout')
@section('title', 'AP Bills')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">AP Bills</h1>
    <p class="text-sm text-gray-500 mt-0.5">Accounts payable — vendor bills and outstanding balances</p>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Bill No</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Issued</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Due</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Balance</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($bills as $b)
                @php
                    $isPastDue = $b->status !== 'paid' && $b->due_at->isPast();
                    $statusColors = ['paid' => 'emerald', 'partial' => 'blue', 'unpaid' => 'amber', 'overdue' => 'red'];
                    $displayStatus = $isPastDue && $b->status === 'unpaid' ? 'overdue' : $b->status;
                    $sc = $statusColors[$displayStatus] ?? 'gray';
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors {{ $isPastDue ? 'bg-red-50/20' : '' }}">
                    <td class="px-5 py-3.5">
                        <span class="font-mono text-sm font-semibold text-gray-900">{{ $b->bill_no }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">{{ $b->issued_at->format('d M Y') }}</td>
                    <td class="px-4 py-3.5 text-sm {{ $isPastDue ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                        {{ $b->due_at->format('d M Y') }}
                        @if ($isPastDue)
                        <span class="text-xs ml-1 text-red-500">({{ $b->due_at->diffForHumans() }})</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-800">
                        Rp {{ number_format($b->grand_total, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm {{ $b->balance > 0 ? 'text-red-600 font-semibold' : 'text-emerald-600' }}">
                        Rp {{ number_format($b->balance, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">{{ $displayStatus }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="flex flex-col items-center justify-center py-12">
                            <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500">No AP bills yet</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($bills->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
        {{ $bills->links() }}
    </div>
    @endif
</div>

@endsection
