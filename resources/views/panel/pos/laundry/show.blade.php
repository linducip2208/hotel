@extends('panel.layout')
@section('title', 'Laundry Order — ' . $order->order_number)
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.pos.laundry.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $order->order_number }}</h1>
        <p class="text-sm text-gray-500">
            Room {{ $order->room?->number ?? '—' }}
            · {{ $order->guest?->full_name ?? $order->guest?->first_name ?? '—' }}
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Order details --}}
    <div class="lg:col-span-2 space-y-4">
        {{-- Items --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Laundry Items</h2>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-5 py-2.5 font-semibold text-gray-600">Item</th>
                        <th class="text-center px-5 py-2.5 font-semibold text-gray-600">Service</th>
                        <th class="text-center px-5 py-2.5 font-semibold text-gray-600">Qty</th>
                        <th class="text-right px-5 py-2.5 font-semibold text-gray-600">Unit</th>
                        <th class="text-right px-5 py-2.5 font-semibold text-gray-600">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($order->items as $item)
                    <tr>
                        <td class="px-5 py-3 font-medium text-gray-900">{{ $item['name'] ?? $item['item'] }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full capitalize">{{ str_replace('_', ' ', $item['service']) }}</span>
                        </td>
                        <td class="px-5 py-3 text-center">{{ $item['qty'] }}</td>
                        <td class="px-5 py-3 text-right text-gray-600">Rp {{ number_format($item['unit_price'] ?? 0, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-medium text-gray-900">Rp {{ number_format($item['line_total'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-100">
                    <tr>
                        <td colspan="4" class="px-5 py-3 text-right font-semibold text-gray-700">Total</td>
                        <td class="px-5 py-3 text-right font-bold text-primary-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if ($order->notes)
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-1">Notes</h3>
            <p class="text-sm text-gray-600">{{ $order->notes }}</p>
        </div>
        @endif
    </div>

    {{-- Sidebar: Status timeline + Actions --}}
    <div class="space-y-4">
        {{-- Status timeline --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Status Progress</h3>
            <div class="space-y-0">
                @foreach ($order->statuses() as $idx => $status)
                @php
                    $currentIdx = array_search($order->status, $order->statuses());
                    $isDone = $idx <= $currentIdx;
                    $isCurrent = $idx === $currentIdx;
                @endphp
                <div class="flex items-start gap-3">
                    <div class="flex flex-col items-center">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs
                            {{ $isDone ? ($isCurrent ? 'bg-primary-600 text-white' : 'bg-emerald-500 text-white') : 'bg-gray-200 text-gray-500' }}">
                            {{ $isDone ? '✓' : ($idx + 1) }}
                        </div>
                        @if ($idx < count($order->statuses()) - 1)
                        <div class="w-0.5 h-8 {{ $idx < $currentIdx ? 'bg-emerald-300' : 'bg-gray-200' }}"></div>
                        @endif
                    </div>
                    <div class="pb-5 {{ !$isDone ? 'opacity-40' : '' }}">
                        <p class="text-sm font-medium {{ $isCurrent ? 'text-primary-700' : 'text-gray-700' }} capitalize">{{ $status }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 space-y-3">
            <h3 class="text-sm font-semibold text-gray-900">Actions</h3>

            @if ($order->status !== 'delivered')
            <form method="POST" action="{{ route('panel.pos.laundry.status', $order->id) }}">
                @csrf @method('PATCH')
                <select name="status" onchange="this.form.submit()"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-100 focus:border-primary-400 outline-none mb-2">
                    <option value="">Advance status...</option>
                    @foreach ($order->statuses() as $s)
                    <option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </form>
            @endif

            @if (!in_array($order->status, ['delivered']) && $order->payment_status === 'unpaid')
            <form method="POST" action="{{ route('panel.pos.laundry.deliver', $order->id) }}">
                @csrf
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-colors shadow-sm">
                    Deliver & Charge to Room
                </button>
            </form>
            @endif

            <div class="text-xs text-gray-500 space-y-1 pt-2 border-t border-gray-100">
                <p>Received by: <strong>{{ $order->receivedBy?->name ?? '—' }}</strong></p>
                <p>Delivered by: <strong>{{ $order->deliveredBy?->name ?? '—' }}</strong></p>
                <p>Payment: <span class="capitalize font-medium">{{ str_replace('_', ' ', $order->payment_status) }}</span></p>
            </div>
        </div>
    </div>
</div>

@endsection
