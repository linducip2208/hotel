@extends('panel.layout')
@section('title', 'Night Audit')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Night Audit</h1>
        <p class="text-sm text-gray-500 mt-0.5">Nightly revenue reconciliation & reporting</p>
    </div>
    <form method="POST" action="{{ route('panel.fo.night-audit.run') }}" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        <button type="submit"
                class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors"
                :disabled="loading">
            <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span x-text="loading ? 'Running...' : 'Run Audit Now'"></span>
        </button>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Occ %</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">ADR</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">RevPAR</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Room Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($audits as $a)
                @php
                    $isDone = $a->status === 'completed';
                    $occ = data_get($a->summary, 'occupancy_pct', null);
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 font-medium text-gray-800">{{ $a->audit_date->format('d M Y') }}</td>
                    <td class="px-4 py-3.5 text-center">
                        @if ($isDone)
                        <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Completed
                        </span>
                        @elseif ($a->status === 'running')
                        <span class="inline-flex items-center gap-1 text-xs font-medium bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full">
                            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Running
                        </span>
                        @else
                        <span class="text-xs font-medium bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full">{{ $a->status }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if (!is_null($occ))
                        <span class="text-sm font-semibold tabular-nums @if($occ >= 80) text-emerald-600 @elseif($occ >= 50) text-primary-600 @else text-amber-600 @endif">
                            {{ $occ }}%
                        </span>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">
                        Rp {{ number_format(data_get($a->summary, 'adr', 0), 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">
                        Rp {{ number_format(data_get($a->summary, 'revpar', 0), 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm font-semibold text-gray-900">
                        Rp {{ number_format(data_get($a->summary, 'room_revenue_gross', 0), 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-16 text-center">
                        <div class="flex flex-col items-center text-gray-400">
                            <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                            </div>
                            <p class="text-sm font-medium text-gray-500">No audit records yet</p>
                            <p class="text-xs text-gray-400 mt-1">Run an audit to generate nightly stats.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($audits->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
        {{ $audits->links() }}
    </div>
    @endif
</div>

@endsection
