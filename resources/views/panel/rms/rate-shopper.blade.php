@extends('panel.layout')
@section('title', 'Rate Shopper')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Rate Shopper Snapshots</h1>
        <p class="text-sm text-gray-500 mt-0.5">Competitor rate benchmarking and rate index tracking</p>
    </div>
    <form method="POST" action="{{ route('panel.rms.rate-shopper.trigger') }}">
        @csrf
        <button type="submit"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Shop Now
        </button>
    </form>
</div>

@if (session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-5 text-sm font-medium">
    {{ session('success') }}
</div>
@endif

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Check Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Shopping For</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Our Rate</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Avg Comp</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Rate Index</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($snapshots as $s)
                @php
                    $idx = (float) $s->rate_index;
                    $idxColor = $idx >= 1.05 ? 'emerald' : ($idx <= 0.95 ? 'red' : 'gray');
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $s->check_date->format('d M Y') }}</td>
                    <td class="px-4 py-3.5 text-sm font-medium text-gray-800">{{ $s->shopped_for_date->format('d M Y') }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-primary-700 font-semibold">
                        Rp {{ number_format($s->our_rate, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">
                        Rp {{ number_format($s->avg_competitor_rate, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="inline-flex items-center gap-1 text-xs font-bold bg-{{ $idxColor }}-50 text-{{ $idxColor }}-700 px-2.5 py-1 rounded-full font-mono">
                            {{ $s->rate_index }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="flex flex-col items-center justify-center py-12">
                            <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-600">No snapshots yet</p>
                            <p class="text-xs text-gray-400 mt-1">Configure rate shopper provider in Settings → Integrations</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($snapshots->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
        {{ $snapshots->links() }}
    </div>
    @endif
</div>

@endsection
