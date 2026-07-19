@extends('panel.layout')
@section('title', 'Linen History — ' . $item->name)
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.hk.linen.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $item->name }}</h1>
        <p class="text-sm text-gray-500">
            <span class="capitalize">{{ str_replace('_', ' ', $item->type) }}</span>
            · Stock: <strong>{{ $item->current_stock }}</strong>
            · Damaged: <strong class="text-red-600">{{ $item->damaged }}</strong>
        </p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Date</th>
                <th class="text-center px-5 py-3 font-semibold text-gray-600">Type</th>
                <th class="text-center px-5 py-3 font-semibold text-gray-600">Qty</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Reference</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Staff</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Notes</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($transactions as $tx)
            @php
                $typeBadge = match($tx->type) {
                    'in' => 'bg-emerald-50 text-emerald-700',
                    'out' => 'bg-red-50 text-red-700',
                    'damaged' => 'bg-orange-50 text-orange-700',
                    'discarded' => 'bg-gray-50 text-gray-600',
                    default => 'bg-gray-50 text-gray-600',
                };
            @endphp
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3 text-gray-600">{{ $tx->created_at->format('d M Y H:i') }}</td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize {{ $typeBadge }}">{{ $tx->type }}</span>
                </td>
                <td class="px-5 py-3 text-center font-medium">{{ $tx->quantity }}</td>
                <td class="px-5 py-3 text-gray-600">{{ $tx->reference ?? '—' }}</td>
                <td class="px-5 py-3 text-gray-600">{{ $tx->staff?->name ?? '—' }}</td>
                <td class="px-5 py-3 text-gray-500 text-xs max-w-xs truncate">{{ $tx->notes ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="py-16 text-center text-gray-400">No transactions recorded.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if ($transactions->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">
        {{ $transactions->links() }}
    </div>
    @endif
</div>

@endsection
