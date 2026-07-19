@extends('panel.layout')
@section('title', 'Channel Parity Alerts')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Channel Parity Monitor</h1>
        <p class="text-sm text-gray-500 mt-0.5">Detect and resolve rate discrepancies across OTA channels</p>
    </div>
    <form method="POST" action="{{ route('panel.pricing.parity.check') }}">
        @csrf
        <button type="submit"
                class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Check Now
        </button>
    </form>
</div>

@if (session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-5 text-sm font-medium">
    {{ session('success') }}
</div>
@endif

{{-- Stats Summary --}}
@php
    $open     = $alerts->where('status', 'open')->count();
    $critical = $alerts->where('severity', 'critical')->count();
    $high     = $alerts->where('severity', 'high')->count();
@endphp
<div class="grid grid-cols-3 gap-5 mb-5">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 text-center">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Open Alerts</div>
        <div class="text-4xl font-bold text-gray-800">{{ $open }}</div>
        <div class="text-xs text-gray-400 mt-1">Requires action</div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-red-100 p-5 text-center">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Critical</div>
        <div class="text-4xl font-bold text-red-600">{{ $critical }}</div>
        <div class="text-xs text-gray-400 mt-1">Severity: critical</div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-orange-100 p-5 text-center">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">High</div>
        <div class="text-4xl font-bold text-orange-500">{{ $high }}</div>
        <div class="text-xs text-gray-400 mt-1">Severity: high</div>
    </div>
</div>

{{-- Alerts Table --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Room Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Channel</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Direct Rate</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">OTA Rate</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Gap</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Severity</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($alerts as $alert)
                @php
                    $severityColors = ['low' => 'gray', 'medium' => 'amber', 'high' => 'orange', 'critical' => 'red'];
                    $sc = $severityColors[$alert->severity] ?? 'gray';
                    $rowBg = $alert->severity === 'critical' ? 'bg-red-50/40' : ($alert->severity === 'high' ? 'bg-orange-50/30' : '');
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors {{ $rowBg }}">
                    <td class="px-5 py-3.5 text-sm text-gray-700">{{ $alert->check_date->format('d M Y') }}</td>
                    <td class="px-4 py-3.5 text-sm text-gray-700">{{ $alert->roomType?->name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-sm font-medium text-gray-800">{{ $alert->channel?->name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">Rp {{ number_format($alert->direct_rate, 0, ',', '.') }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">Rp {{ number_format($alert->channel_rate, 0, ',', '.') }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm font-bold text-red-600">{{ number_format($alert->gap_pct * 100, 1) }}%</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-0.5 rounded-full capitalize">{{ $alert->severity }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium px-2.5 py-0.5 rounded-full {{ $alert->status === 'resolved' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }} capitalize">
                            {{ $alert->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if ($alert->status !== 'resolved')
                        <form method="POST" action="{{ route('panel.pricing.parity.resolve', $alert->id) }}" class="inline">
                            @csrf
                            <input type="hidden" name="action" value="Manual review">
                            <button type="submit"
                                    class="text-xs font-semibold text-primary-600 hover:text-primary-800 transition-colors">
                                Resolve
                            </button>
                        </form>
                        @else
                        <span class="text-xs text-gray-400">Done</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-12 text-center text-sm text-gray-400">No parity alerts. All channel rates are within acceptable range.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($alerts->hasPages())
    <div class="px-5 py-3 border-t border-gray-50">{{ $alerts->links() }}</div>
    @endif
</div>

@endsection
