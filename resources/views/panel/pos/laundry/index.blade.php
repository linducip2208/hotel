@extends('panel.layout')
@section('title', 'Laundry Orders')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Laundry POS</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage guest laundry orders and track status</p>
    </div>
    <a href="{{ route('panel.pos.laundry.create') }}" class="inline-flex items-center gap-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Order
    </a>
</div>

{{-- Status summary --}}
<div class="flex items-center gap-2 mb-4 flex-wrap">
    @foreach (['received' => 'blue', 'washing' => 'indigo', 'drying' => 'purple', 'folding' => 'amber', 'ready' => 'emerald', 'delivered' => 'gray'] as $status => $color)
    <a href="?status={{ $status }}" class="text-xs font-medium px-2.5 py-1 rounded-full border capitalize
        {{ request('status') === $status ? 'bg-'.$color.'-100 text-'.$color.'-700 border-'.$color.'-300' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50' }}">
        {{ $status }}
        @if (isset($statusCounts[$status]))
        <span class="ml-1">({{ $statusCounts[$status] }})</span>
        @endif
    </a>
    @endforeach
    <a href="?" class="text-xs text-gray-400 hover:text-gray-600 {{ !request('status') ? 'font-bold text-gray-700' : '' }}">Clear</a>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Order #</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Room</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Guest</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600">Items</th>
                    <th class="text-right px-5 py-3 font-semibold text-gray-600">Total</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600">Payment</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($orders as $order)
                @php
                    $statusBadge = match($order->status) {
                        'received' => 'bg-blue-50 text-blue-700',
                        'washing' => 'bg-indigo-50 text-indigo-700',
                        'drying' => 'bg-purple-50 text-purple-700',
                        'folding' => 'bg-amber-50 text-amber-700',
                        'ready' => 'bg-emerald-50 text-emerald-700',
                        'delivered' => 'bg-gray-50 text-gray-600',
                        default => 'bg-gray-50 text-gray-600',
                    };
                    $payBadge = match($order->payment_status) {
                        'paid' => 'bg-emerald-50 text-emerald-700',
                        'charged_to_room' => 'bg-blue-50 text-blue-700',
                        default => 'bg-amber-50 text-amber-700',
                    };
                    $itemCount = count($order->items ?? []);
                @endphp
                <tr class="hover:bg-gray-50/50 cursor-pointer" onclick="window.location='{{ route('panel.pos.laundry.show', $order->id) }}'">
                    <td class="px-5 py-3 font-mono text-xs text-primary-700 font-semibold">{{ $order->order_number }}</td>
                    <td class="px-5 py-3 font-medium text-gray-900">Room {{ $order->room?->number ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $order->guest?->full_name ?? $order->guest?->first_name ?? '—' }}</td>
                    <td class="px-5 py-3 text-center text-gray-600">{{ $itemCount }} items</td>
                    <td class="px-5 py-3 text-right font-medium text-gray-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize {{ $statusBadge }}">{{ $order->status }}</span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $payBadge }}">{{ str_replace('_', ' ', $order->payment_status) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="py-16 text-center text-gray-400">No laundry orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($orders->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $orders->links() }}</div>
    @endif
</div>

@endsection
