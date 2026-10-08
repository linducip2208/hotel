@extends('admin.layout')
@section('title', 'Subscriptions')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Subscriptions</h1>
    <p class="text-sm text-gray-500 mt-1">Semua langganan tenant</p>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tenant</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Plan</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Cycle</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Price (IDR)</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Period End</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($subscriptions as $s)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium text-gray-900">{{ $s->tenant?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $s->plan?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $s->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($s->status === 'past_due' ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-500') }}">
                            {{ $s->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $s->billing_cycle }}</td>
                    <td class="px-4 py-3 text-right font-mono text-gray-800">Rp {{ number_format($s->price_paid_idr, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-gray-500">{{ $s->current_period_end?->format('d M Y') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-12 text-center text-sm text-gray-400">Belum ada langganan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($subscriptions->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $subscriptions->links() }}</div>
    @endif
</div>
@endsection
