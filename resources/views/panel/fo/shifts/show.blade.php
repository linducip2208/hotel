@extends('panel.layout')
@section('title', 'Shift #' . $shift->id)
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.fo.shifts.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900">Shift #{{ $shift->id }}</h1>
            @if ($shift->closed_at)
            <span class="text-xs font-semibold bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full">Closed</span>
            @else
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Open
            </span>
            @endif
        </div>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ $shift->cashier?->name }}
            · {{ $shift->opened_at->format('d M Y H:i') }}
            → {{ $shift->closed_at?->format('H:i') ?? 'ongoing' }}
        </p>
    </div>
</div>

<div class="grid md:grid-cols-2 gap-5 mb-5">
    {{-- Cash Reconciliation --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Cash Reconciliation</h2>
        </div>
        <dl class="divide-y divide-gray-50 text-sm">
            <div class="flex justify-between items-center px-5 py-3">
                <dt class="text-gray-500">Opening Float</dt>
                <dd class="font-mono font-medium text-gray-800">Rp {{ number_format($shift->opening_float, 0, ',', '.') }}</dd>
            </div>
            <div class="flex justify-between items-center px-5 py-3">
                <dt class="text-gray-500">Expected Cash</dt>
                <dd class="font-mono font-medium text-gray-800">Rp {{ number_format($shift->expected_cash, 0, ',', '.') }}</dd>
            </div>
            <div class="flex justify-between items-center px-5 py-3">
                <dt class="text-gray-500">Actual Cash</dt>
                <dd class="font-mono font-medium text-gray-800">Rp {{ number_format($shift->actual_cash, 0, ',', '.') }}</dd>
            </div>
            <div class="flex justify-between items-center px-5 py-3.5">
                <dt class="font-semibold text-gray-700">Variance</dt>
                <dd class="font-mono font-bold text-lg {{ $shift->cash_variance < 0 ? 'text-red-600' : ($shift->cash_variance > 0 ? 'text-amber-600' : 'text-emerald-600') }}">
                    {{ $shift->cash_variance >= 0 ? '+' : '' }}Rp {{ number_format($shift->cash_variance, 0, ',', '.') }}
                </dd>
            </div>
        </dl>
        @if ($shift->cash_variance < 0)
        <div class="px-5 py-3 bg-red-50 border-t border-red-100">
            <p class="text-xs text-red-600 font-medium">Cash short — variance requires investigation.</p>
        </div>
        @elseif ($shift->cash_variance == 0)
        <div class="px-5 py-3 bg-emerald-50 border-t border-emerald-100">
            <p class="text-xs text-emerald-600 font-medium">Cash balanced perfectly.</p>
        </div>
        @endif
    </div>

    {{-- Breakdown --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Payment Breakdown</h2>
        </div>
        <div class="p-5">
            <pre class="text-xs bg-gray-50 rounded-xl p-4 text-gray-600 overflow-x-auto font-mono leading-relaxed">{{ json_encode($shift->breakdown, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
</div>

{{-- Payments in this shift --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Payments in This Shift</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Method</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Folio</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($shift->payments as $p)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 font-mono text-xs text-gray-500">{{ $p->created_at->format('H:i') }}</td>
                    <td class="px-4 py-3.5">
                        @php
                            $methodColor = match($p->method) { 'cash' => 'emerald', 'card' => 'blue', 'transfer' => 'violet', default => 'gray' };
                        @endphp
                        <span class="text-xs font-medium bg-{{ $methodColor }}-50 text-{{ $methodColor }}-700 px-2.5 py-0.5 rounded-full capitalize">{{ $p->method }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">
                        @if ($p->folio_id)
                        <a href="{{ route('panel.fo.folios.show', $p->folio_id) }}"
                           class="font-mono text-primary-600 hover:text-primary-800 transition-colors">
                            #{{ $p->folio_id }}
                        </a>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm font-semibold text-gray-800">
                        Rp {{ number_format($p->amount, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-10 text-center text-sm text-gray-400">No payments recorded in this shift.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
