@extends('panel.layout')
@section('title', 'Revenue Management')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Revenue Management</h1>
    <p class="text-sm text-gray-500 mt-0.5">30-day rolling window — occupancy, rate performance, and yield</p>
</div>

{{-- KPI cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    {{-- Occupancy --}}
    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <span class="text-xs font-medium text-primary-600 bg-primary-50 px-2 py-0.5 rounded-full">30-day</span>
        </div>
        @php $occ = $summary['occupancy_pct'] ?? 0; @endphp
        <div class="text-3xl font-bold text-gray-900 tabular-nums">{{ $occ }}%</div>
        <div class="text-sm text-gray-500 mt-0.5 mb-3">Occupancy</div>
        <div class="w-full bg-gray-100 rounded-full h-2">
            <div class="h-2 rounded-full @if($occ >= 80) bg-emerald-500 @elseif($occ >= 50) bg-primary-500 @else bg-amber-400 @endif"
                 style="width: {{ min($occ, 100) }}%"></div>
        </div>
    </div>

    {{-- ADR --}}
    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Avg</span>
        </div>
        <div class="text-2xl font-bold text-gray-900 tabular-nums truncate">
            Rp {{ number_format($summary['adr'] ?? 0, 0, ',', '.') }}
        </div>
        <div class="text-sm text-gray-500 mt-0.5">Average Daily Rate</div>
    </div>

    {{-- RevPAR --}}
    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                </svg>
            </div>
            <span class="text-xs font-medium text-violet-600 bg-violet-50 px-2 py-0.5 rounded-full">KPI</span>
        </div>
        <div class="text-2xl font-bold text-gray-900 tabular-nums truncate">
            Rp {{ number_format($summary['revpar'] ?? 0, 0, ',', '.') }}
        </div>
        <div class="text-sm text-gray-500 mt-0.5">RevPAR</div>
    </div>

</div>

{{-- Module links --}}
<div class="grid md:grid-cols-3 gap-4">
    @foreach ([
        ['label' => 'Demand Forecast', 'desc' => '14-day forward demand projection', 'route' => 'panel.rms.forecast', 'color' => 'blue', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>'],
        ['label' => 'Yield Report', 'desc' => 'Period RevPAR & ADR analysis', 'route' => 'panel.rms.yield', 'color' => 'emerald', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
        ['label' => 'Rate Shopper', 'desc' => 'Comp set rates vs your pricing', 'route' => 'panel.rms.rate-shopper', 'color' => 'amber', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>'],
    ] as $mod)
    <a href="{{ route($mod['route']) }}"
       class="group flex items-start gap-4 bg-white rounded-2xl p-5 shadow-card border border-gray-100 hover:shadow-card-hover hover:border-{{ $mod['color'] }}-100 transition-all">
        <div class="w-11 h-11 rounded-xl bg-{{ $mod['color'] }}-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-{{ $mod['color'] }}-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                {!! $mod['icon'] !!}
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold text-gray-900 group-hover:text-{{ $mod['color'] }}-700 transition-colors">{{ $mod['label'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">{{ $mod['desc'] }}</div>
        </div>
        <svg class="w-4 h-4 text-gray-300 group-hover:text-{{ $mod['color'] }}-400 mt-0.5 shrink-0 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>
    @endforeach
</div>

@endsection
