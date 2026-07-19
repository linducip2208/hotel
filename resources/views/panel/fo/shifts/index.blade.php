@extends('panel.layout')
@section('title', 'Cashier Shifts')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Cashier Shifts</h1>
    <p class="text-sm text-gray-500 mt-0.5">Open/close cash drawer, track variance</p>
</div>

{{-- Active shift banner / open shift --}}
@if ($current)
<div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-5">
    <div class="flex items-start justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                <span class="text-sm font-semibold text-amber-800">Shift Active</span>
            </div>
            <p class="text-sm text-amber-700">Opened {{ $current->opened_at->diffForHumans() }} · Float Rp {{ number_format($current->opening_float, 0, ',', '.') }}</p>
        </div>
    </div>
    <form method="POST" action="{{ route('panel.fo.shifts.close', $current->id) }}" class="mt-4 flex gap-3 items-end">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-amber-700 mb-1.5">Actual Cash on Drawer</label>
            <input type="number" step="0.01" name="actual_cash" required placeholder="0"
                   class="rounded-xl border border-amber-300 bg-white px-3.5 py-2 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100 transition-all">
        </div>
        <div class="flex-1">
            <label class="block text-xs font-semibold text-amber-700 mb-1.5">Notes</label>
            <input type="text" name="notes" placeholder="Shift notes…"
                   class="w-full rounded-xl border border-amber-300 bg-white px-3.5 py-2 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100 transition-all">
        </div>
        <button type="submit"
                class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            Close Shift
        </button>
    </form>
</div>
@else
<div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 mb-5">
    <div class="flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-emerald-800">No active shift</p>
            <p class="text-xs text-emerald-600 mt-0.5">Open a new cashier shift to start accepting payments.</p>
        </div>
        <form method="POST" action="{{ route('panel.fo.shifts.open') }}" class="flex items-center gap-3 shrink-0">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-emerald-700 mb-1">Opening Float</label>
                <input type="number" step="0.01" name="opening_float" required value="0"
                       class="rounded-xl border border-emerald-300 bg-white px-3.5 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition-all w-36">
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors mt-5">
                Open Shift
            </button>
        </form>
    </div>
</div>
@endif

{{-- Shift history --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Shift History</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Cashier</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Opened</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Closed</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Float</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Expected</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actual</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Variance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($shifts as $s)
                @php
                    $variance = (float) $s->cash_variance;
                    $varClass = $variance < 0 ? 'text-red-600 font-semibold' : ($variance > 0 ? 'text-amber-600 font-semibold' : 'text-emerald-600');
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-800">{{ $s->cashier?->name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-xs text-gray-600 font-mono">{{ $s->opened_at->format('d M H:i') }}</td>
                    <td class="px-4 py-3.5 text-xs {{ $s->closed_at ? 'text-gray-600 font-mono' : 'text-amber-600 font-medium' }}">
                        {{ $s->closed_at?->format('d M H:i') ?? 'Open' }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">{{ number_format($s->opening_float, 0, ',', '.') }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">{{ number_format($s->expected_cash, 0, ',', '.') }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">{{ number_format($s->actual_cash, 0, ',', '.') }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm {{ $varClass }}">{{ number_format($variance, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-10 text-center text-sm text-gray-400">No shift history yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
