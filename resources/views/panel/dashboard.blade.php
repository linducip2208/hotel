@extends('panel.layout')
@section('title', 'Dashboard')
@section('content')

@php
    $rt = function (string $name, ...$args) {
        try { return route($name, $args); } catch (\Throwable) { return '#'; }
    };
    // Be defensive: dashboard may be rendered before controller binds these.
    $property = $property ?? (app()->bound('current_property') ? app('current_property') : null);
    $kpi = $kpi ?? [
        'arrivals_today' => 0, 'departures_today' => 0, 'in_house' => 0, 'pending_payment' => 0,
        'occupancy_pct' => 0, 'total_rooms' => 0, 'occupied_rooms' => 0,
        'adr' => 0, 'revpar' => 0, 'total_rev_today' => 0,
        'arrivals_list' => collect(), 'departures_list' => collect(),
    ];
    $trend = $trend ?? collect();
@endphp

{{-- ════════════════════════ HERO ════════════════════════ --}}
<div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-indigo-900 to-violet-900 p-6 lg:p-8 mb-6 shadow-xl shadow-indigo-900/20">
    <div class="absolute inset-0 opacity-30 pointer-events-none"
         style="background-image:radial-gradient(circle at 25% 0%,rgba(99,102,241,.4),transparent 50%),radial-gradient(circle at 80% 100%,rgba(139,92,246,.3),transparent 50%);"></div>
    <div class="relative flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div>
            <p class="text-indigo-200/80 text-xs uppercase tracking-[0.2em] font-semibold mb-1">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
            <h1 class="text-2xl lg:text-3xl font-bold text-white tracking-tight">{{ $property?->name ?? config('app.name') }}</h1>
            <p class="text-indigo-100/70 text-sm mt-1">
                @if(($kpi['total_rooms'] ?? 0) > 0)
                    {{ $kpi['occupied_rooms'] }} / {{ $kpi['total_rooms'] }} kamar terisi
                    · <span class="font-semibold text-white">{{ $kpi['occupancy_pct'] }}%</span> occupancy
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ $rt('panel.fo.reservations.create') }}"
               class="inline-flex items-center gap-2 bg-white text-slate-900 hover:bg-slate-100 text-sm font-semibold px-4 py-2.5 rounded-xl shadow-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Reservasi Baru
            </a>
            <a href="{{ $rt('panel.fo.calendar') }}"
               class="inline-flex items-center gap-2 bg-white/10 backdrop-blur text-white hover:bg-white/15 text-sm font-medium px-4 py-2.5 rounded-xl border border-white/15 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Tape Chart
            </a>
            <a href="{{ $rt('panel.fo.night-audit.index') }}"
               class="inline-flex items-center gap-2 bg-white/10 backdrop-blur text-white hover:bg-white/15 text-sm font-medium px-4 py-2.5 rounded-xl border border-white/15 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                Night Audit
            </a>
        </div>
    </div>
</div>

{{-- ════════════════════════ KPI ROW ════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-3 lg:gap-4 mb-6">

    {{-- Occupancy --}}
    @php $occ = $kpi['occupancy_pct'] ?? 0; $occColor = $occ >= 80 ? 'emerald' : ($occ >= 50 ? 'indigo' : 'amber'); @endphp
    <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-{{ $occColor }}-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-{{ $occColor }}-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Today</span>
        </div>
        <div class="flex items-baseline gap-1">
            <div class="text-3xl font-bold text-slate-900 tabular-nums">{{ $occ }}</div>
            <div class="text-base font-semibold text-slate-400">%</div>
        </div>
        <div class="text-xs text-slate-500 mt-0.5">Occupancy</div>
        <div class="mt-3 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
            <div class="h-1.5 rounded-full bg-{{ $occColor }}-500 transition-all" style="width: {{ min($occ, 100) }}%"></div>
        </div>
    </div>

    {{-- In-house --}}
    <a href="{{ $rt('panel.fo.in-house') }}" class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all group">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <span class="text-[10px] font-semibold text-violet-500 uppercase tracking-wide">Live</span>
        </div>
        <div class="text-3xl font-bold text-slate-900 tabular-nums">{{ $kpi['in_house'] }}</div>
        <div class="text-xs text-slate-500 mt-0.5 group-hover:text-violet-600 transition-colors">In-house guests</div>
    </a>

    {{-- Arrivals --}}
    <a href="{{ $rt('panel.fo.arrivals') }}" class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all group">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M13 6l6 6-6 6"/></svg>
            </div>
            <span class="text-[10px] font-semibold text-indigo-500 uppercase tracking-wide">Today</span>
        </div>
        <div class="text-3xl font-bold text-slate-900 tabular-nums">{{ $kpi['arrivals_today'] }}</div>
        <div class="text-xs text-slate-500 mt-0.5 group-hover:text-indigo-600 transition-colors">Arrivals</div>
    </a>

    {{-- Departures --}}
    <a href="{{ $rt('panel.fo.departures') }}" class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all group">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12H3M11 18l-6-6 6-6"/></svg>
            </div>
            <span class="text-[10px] font-semibold text-blue-500 uppercase tracking-wide">Today</span>
        </div>
        <div class="text-3xl font-bold text-slate-900 tabular-nums">{{ $kpi['departures_today'] }}</div>
        <div class="text-xs text-slate-500 mt-0.5 group-hover:text-blue-600 transition-colors">Departures</div>
    </a>

    {{-- Outstanding --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-[10px] font-semibold text-amber-600 uppercase tracking-wide">Unpaid</span>
        </div>
        <div class="text-3xl font-bold text-slate-900 tabular-nums">{{ $kpi['pending_payment'] }}</div>
        <div class="text-xs text-slate-500 mt-0.5">Outstanding</div>
    </div>
</div>

{{-- ════════════════════════ REVENUE & TREND ════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    {{-- Revenue today + ADR + RevPAR --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Revenue Today</p>
                <p class="text-2xl font-bold text-slate-900 tabular-nums mt-1">Rp {{ number_format($kpi['total_rev_today'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center shadow-md shadow-emerald-500/30">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100">
            <div>
                <p class="text-[11px] text-slate-400 uppercase font-semibold tracking-wide">ADR</p>
                <p class="text-sm font-bold text-slate-900 tabular-nums mt-0.5">Rp {{ number_format($kpi['adr'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-[11px] text-slate-400 uppercase font-semibold tracking-wide">RevPAR</p>
                <p class="text-sm font-bold text-slate-900 tabular-nums mt-0.5">Rp {{ number_format($kpi['revpar'] ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- 7-day Occupancy Trend --}}
    @if (isset($trend) && $trend->isNotEmpty())
    <div class="lg:col-span-2 bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Occupancy Trend</p>
                <p class="text-sm font-semibold text-slate-700 mt-0.5">7 hari terakhir</p>
            </div>
            <a href="{{ $rt('panel.reports.occupancy') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1">
                Full report
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="flex items-end gap-2 h-24">
            @foreach ($trend as $t)
                @php $h = max(6, (int) round($t['occ'] * 0.85)); $tColor = $t['occ'] >= 80 ? 'emerald' : ($t['occ'] >= 50 ? 'indigo' : 'amber'); @endphp
                <div class="flex-1 flex flex-col items-center gap-1.5 group">
                    <div class="text-[10px] font-mono text-slate-400 tabular-nums opacity-0 group-hover:opacity-100 transition-opacity">{{ $t['occ'] }}%</div>
                    <div class="w-full rounded-t-md transition-all bg-{{ $tColor }}-500 group-hover:bg-{{ $tColor }}-600"
                         style="height: {{ $h }}px; min-height: 6px;"></div>
                    <div class="text-[10px] text-slate-400 font-medium">{{ $t['date'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- ════════════════════════ ROLE METRICS ════════════════════════ --}}
@php $role = $role ?? null; $roleMetrics = $roleMetrics ?? []; @endphp

@if ($role && !empty($roleMetrics))
    @foreach ($roleMetrics as $roleKey => $cards)
        @php
            $roleConfig = match ($roleKey) {
                'hk'    => ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>', 'color' => 'violet',  'label' => 'Housekeeping'],
                'fo'    => ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>', 'color' => 'indigo',  'label' => 'Front Office'],
                'acc'   => ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>', 'color' => 'emerald', 'label' => 'Accounting'],
                'kasir' => ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>', 'color' => 'orange',  'label' => 'Kasir'],
                default => ['icon' => '', 'color' => 'slate', 'label' => ucfirst($roleKey)],
            };
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-{{ $roleConfig['color'] }}-100 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-{{ $roleConfig['color'] }}-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $roleConfig['icon'] !!}</svg>
                    </div>
                    <h2 class="text-sm font-bold text-slate-800">Widget {{ $roleConfig['label'] }}</h2>
                </div>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-{{ min(count($cards), 4) }} gap-0 divide-x divide-slate-100">
                @foreach ($cards as $metricKey => $metricValue)
                    <div class="p-5">
                        <p class="text-2xl font-bold text-slate-900 tabular-nums">
                            @if (str_contains($metricKey, 'revenue') || str_contains($metricKey, 'expense') || str_contains($metricKey, 'income'))
                                Rp {{ number_format($metricValue, 0, ',', '.') }}
                            @else
                                {{ is_numeric($metricValue) ? number_format($metricValue, 0, ',', '.') : $metricValue }}
                            @endif
                        </p>
                        <p class="text-xs text-slate-500 mt-1 capitalize">{{ str_replace('_', ' ', $metricKey) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@endif

{{-- ════════════════════════ PENDING APPROVALS ════════════════════════ --}}
@php
    $pendingApprovals = \App\Models\ApprovalRequest::where('status', 'pending')
        ->where('property_id', $property?->id)
        ->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();
@endphp

@if($pendingApprovals->isNotEmpty())
<div class="mb-6 reveal">
    <h3 class="text-lg font-bold text-slate-800 mb-3">⏳ Menunggu Persetujuan</h3>
    <div class="grid gap-3">
        @foreach($pendingApprovals as $approval)
        <div class="bg-white border border-amber-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="font-semibold text-slate-800">{{ $approval->action_type ?? 'Permintaan' }}</p>
                <p class="text-xs text-slate-500">{{ $approval->created_at->diffForHumans() }}</p>
            </div>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('panel.approvals.approve', $approval->id) }}">
                    @csrf
                    <button class="bg-emerald-600 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-emerald-700 transition-colors">Setujui</button>
                </form>
                <form method="POST" action="{{ route('panel.approvals.reject', $approval->id) }}">
                    @csrf
                    <button class="bg-rose-600 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-rose-700 transition-colors">Tolak</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ════════════════════════ MODULE CATALOG ════════════════════════ --}}
@php
    $modules = $roleModules ?? [
        // Operations
        ['cluster' => 'Operations', 'label' => 'Reservations',   'desc' => 'Booking, check-in, folios',     'route' => 'panel.fo.reservations.index',     'color' => 'indigo',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'],
        ['cluster' => 'Operations', 'label' => 'Tape Chart',     'desc' => 'Visual room timeline',          'route' => 'panel.fo.calendar',                'color' => 'sky',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'],
        ['cluster' => 'Operations', 'label' => 'Night Audit',    'desc' => 'EOD posting & rollover',        'route' => 'panel.fo.night-audit.index',       'color' => 'slate',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>'],
        ['cluster' => 'Operations', 'label' => 'E-Registration', 'desc' => 'Online check-in',               'route' => 'panel.fo.e-registration.index',    'color' => 'cyan',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7l-2.5 2.5L9 12.5"/>'],
        ['cluster' => 'Operations', 'label' => 'Out of Order',   'desc' => 'OOO room status',               'route' => 'panel.fo.ooo.index',               'color' => 'rose',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>'],
        ['cluster' => 'Operations', 'label' => 'Cashier Shifts', 'desc' => 'Open/close shifts',             'route' => 'panel.fo.shifts.index',            'color' => 'amber',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['cluster' => 'Operations', 'label' => 'Housekeeping',   'desc' => 'HK board & tasks',              'route' => 'panel.hk.board',                   'color' => 'violet',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>'],
        ['cluster' => 'Operations', 'label' => 'Lost & Found',   'desc' => 'Items left by guests',          'route' => 'panel.hk.lost-found.index',        'color' => 'fuchsia',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>'],

        // F&B
        ['cluster' => 'F&B / POS',  'label' => 'POS',            'desc' => 'Point of Sale',                 'route' => 'panel.pos.index',                  'color' => 'orange',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>'],
        ['cluster' => 'F&B / POS',  'label' => 'KDS',            'desc' => 'Kitchen Display',               'route' => 'panel.pos.kds',                    'color' => 'red',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082"/>'],
        ['cluster' => 'F&B / POS',  'label' => 'Laundry',        'desc' => 'Laundry POS',                   'route' => 'panel.pos.laundry.index',          'color' => 'sky',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>'],

        // Revenue
        ['cluster' => 'Revenue',    'label' => 'Rate Calendar',  'desc' => 'Daily rates',                   'route' => 'panel.pricing.calendar',           'color' => 'emerald',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>'],
        ['cluster' => 'Revenue',    'label' => 'Dynamic Rules',  'desc' => 'Yield automation',              'route' => 'panel.pricing.rules',              'color' => 'teal',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>'],
        ['cluster' => 'Revenue',    'label' => 'Parity Alerts',  'desc' => 'OTA price parity',              'route' => 'panel.pricing.parity',             'color' => 'amber',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>'],
        ['cluster' => 'Revenue',    'label' => 'RMS',            'desc' => 'Revenue management',            'route' => 'panel.rms.dashboard',              'color' => 'cyan',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
        ['cluster' => 'Revenue',    'label' => 'Channels',       'desc' => 'OTA distribution',              'route' => 'panel.channel.index',              'color' => 'rose',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064"/>'],
        ['cluster' => 'Revenue',    'label' => 'Allotments',     'desc' => 'Group blocks',                  'route' => 'panel.sales.allotments.index',     'color' => 'pink',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>'],

        // AI Tools (BYOK)
        ['cluster' => 'AI Tools (BYOK)', 'label' => 'AI Hub',         'desc' => 'Semua tool AI di satu tempat',         'route' => 'panel.ai.hub',           'color' => 'violet',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>'],
        ['cluster' => 'AI Tools (BYOK)', 'label' => 'AI Concierge',   'desc' => 'Chatbot multi-bahasa',                 'route' => 'panel.ai.concierge',     'color' => 'indigo',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>'],
        ['cluster' => 'AI Tools (BYOK)', 'label' => 'Auto-Translate', 'desc' => 'Terjemahkan konten',                   'route' => 'panel.ai.translate',     'color' => 'sky',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>'],
        ['cluster' => 'AI Tools (BYOK)', 'label' => 'Demand Forecast','desc' => 'Prediksi okupansi 30 hari',            'route' => 'panel.ai.forecast',      'color' => 'emerald', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>'],
        ['cluster' => 'AI Tools (BYOK)', 'label' => 'Review Replies', 'desc' => 'AI generate balasan review',           'route' => 'panel.ai.review-replies','color' => 'amber',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'],

        // Guests & CRM
        ['cluster' => 'Guests',     'label' => 'Guests',         'desc' => 'Profiles & history',            'route' => 'panel.guests.index',               'color' => 'indigo',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
        ['cluster' => 'Guests',     'label' => 'Loyalty',        'desc' => 'Members &amp; tiers',           'route' => 'panel.loyalty.members',            'color' => 'amber',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>'],
        ['cluster' => 'Guests',     'label' => 'Communications', 'desc' => 'Inbox &amp; campaigns',         'route' => 'panel.comm.inbox',                 'color' => 'blue',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>'],
        ['cluster' => 'Guests',     'label' => 'Concierge',      'desc' => 'Requests &amp; POIs',           'route' => 'panel.concierge.requests.index',   'color' => 'emerald',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>'],
        ['cluster' => 'Guests',     'label' => 'Surveys',        'desc' => 'Guest feedback',                'route' => 'panel.survey.index',               'color' => 'teal',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>'],
        ['cluster' => 'Guests',     'label' => 'Referrals',      'desc' => 'Affiliate program',             'route' => 'panel.marketing.referrals',        'color' => 'fuchsia',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>'],

        // Finance
        ['cluster' => 'Finance',    'label' => 'Accounting',     'desc' => 'COA · Journal · AR/AP',         'route' => 'panel.accounting.dashboard',       'color' => 'emerald',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>'],
        ['cluster' => 'Finance',    'label' => 'Bank Recon',     'desc' => 'Match transactions',            'route' => 'panel.finance.bank-recon',         'color' => 'cyan',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 6h18m-9 8h9m-3-4l3 4-3 4M3 14h6l3 4-3 4H3"/>'],
        ['cluster' => 'Finance',    'label' => 'Budget',         'desc' => 'Plan vs actual',                'route' => 'panel.finance.budget',             'color' => 'lime',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2"/>'],
        ['cluster' => 'Finance',    'label' => 'FX Rates',       'desc' => 'Currency conversion',           'route' => 'panel.finance.fx-rates',           'color' => 'sky',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>'],
        ['cluster' => 'Finance',    'label' => 'Owner Stmt',     'desc' => 'Distribution reports',          'route' => 'panel.finance.owner-statements',   'color' => 'indigo',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],

        // Inventory & People
        ['cluster' => 'Inventory & People', 'label' => 'Inventory',     'desc' => 'Stock items',           'route' => 'panel.inventory.index',            'color' => 'orange',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
        ['cluster' => 'Inventory & People', 'label' => 'Purchasing',    'desc' => 'PR · PO · GR',          'route' => 'panel.inventory.po.index',         'color' => 'amber',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>'],
        ['cluster' => 'Inventory & People', 'label' => 'Asset Mgmt',    'desc' => 'PPM · work orders',     'route' => 'panel.asset.index',                'color' => 'slate',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>'],
        ['cluster' => 'Inventory & People', 'label' => 'HR & Payroll',  'desc' => 'Staff · attendance',    'route' => 'panel.hr.employees',               'color' => 'violet',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
        ['cluster' => 'Inventory & People', 'label' => 'Spa',           'desc' => 'Wellness bookings',     'route' => 'panel.spa.appointments',           'color' => 'pink',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>'],
        ['cluster' => 'Inventory & People', 'label' => 'Banquet',       'desc' => 'Events &amp; MICE',     'route' => 'panel.banquet.events.index',       'color' => 'rose',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2"/>'],

        // Insights
        ['cluster' => 'Insights',   'label' => 'Reports',        'desc' => 'Occupancy · Cashier · BPS',     'route' => 'panel.reports.occupancy',          'color' => 'blue',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>'],
        ['cluster' => 'Insights',   'label' => 'Sustainability', 'desc' => 'Eco metrics',                   'route' => 'panel.sustainability.dashboard',   'color' => 'emerald',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>'],
        ['cluster' => 'Insights',   'label' => 'Audit Log',      'desc' => 'Activity trail',                'route' => 'panel.audit.index',                'color' => 'slate',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>'],
        ['cluster' => 'Insights',   'label' => 'Knowledge Base', 'desc' => 'Internal docs',                 'route' => 'panel.kb.index',                   'color' => 'cyan',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>'],
        ['cluster' => 'Insights',   'label' => 'Settings',       'desc' => 'Property &amp; integrations',   'route' => 'panel.settings.property',          'color' => 'gray',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'],
    ];

    $clusters = collect($modules)->groupBy('cluster');
@endphp

<div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden mb-6">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
        <div>
            <h2 class="text-base font-bold text-slate-900">Semua Modul</h2>
            <p class="text-xs text-slate-500 mt-0.5">{{ count($modules) }} fitur tersedia</p>
        </div>
        <a href="{{ $rt('panel.search') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1">
            Cari
            <kbd class="text-[10px] bg-slate-100 border border-slate-200 rounded px-1 font-mono">⌘K</kbd>
        </a>
    </div>

    @foreach ($clusters as $cluster => $items)
    <div class="px-6 py-5 border-b border-slate-100 last:border-b-0">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.18em] mb-3">{{ $cluster }}</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-2.5">
            @foreach ($items as $m)
            <a href="{{ $rt($m['route']) }}"
               class="group flex flex-col gap-2 p-3.5 rounded-xl border border-slate-200/60 hover:border-{{ $m['color'] }}-300 hover:bg-{{ $m['color'] }}-50/40 hover:shadow-sm transition-all">
                <div class="w-9 h-9 rounded-lg bg-{{ $m['color'] }}-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-{{ $m['color'] }}-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">{!! $m['icon'] !!}</svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate group-hover:text-{{ $m['color'] }}-700 transition-colors">{{ $m['label'] }}</p>
                    <p class="text-[11px] text-slate-500 truncate">{{ $m['desc'] }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

{{-- ════════════════════════ ACTIVITY ════════════════════════ --}}
<div class="grid lg:grid-cols-2 gap-4">

    {{-- Arrivals Today --}}
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M13 6l6 6-6 6"/></svg>
                </div>
                <h2 class="text-sm font-bold text-slate-800">Arrivals Today</h2>
                <span class="text-xs font-mono text-slate-400 tabular-nums">{{ $kpi['arrivals_today'] }}</span>
            </div>
            <a href="{{ $rt('panel.fo.arrivals') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Lihat semua →</a>
        </div>
        @php $arrivals = $kpi['arrivals_list'] ?? collect(); @endphp
        @if ($arrivals->isNotEmpty())
        <ul class="divide-y divide-slate-100">
            @foreach ($arrivals->take(5) as $r)
                @php
                    $name = $r->primaryGuest?->full_name ?? 'Guest';
                    $initials = collect(explode(' ', $name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
                @endphp
                <li class="px-5 py-3 flex items-center gap-3 hover:bg-slate-50 transition-colors">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-400 to-violet-500 text-white flex items-center justify-center text-xs font-bold shrink-0 shadow-sm">{{ $initials }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $name }}</p>
                        <p class="text-[11px] text-slate-400 font-mono tabular-nums">{{ $r->ref }}</p>
                    </div>
                    <a href="{{ route('panel.fo.reservations.show', $r->id) }}"
                       class="text-xs text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-full font-semibold transition-colors">View</a>
                </li>
            @endforeach
        </ul>
        @else
        <div class="flex flex-col items-center justify-center py-10 text-slate-400">
            <svg class="w-10 h-10 mb-2 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
            <p class="text-sm">Belum ada arrival hari ini</p>
        </div>
        @endif
    </div>

    {{-- Departures Today --}}
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12H3M11 18l-6-6 6-6"/></svg>
                </div>
                <h2 class="text-sm font-bold text-slate-800">Departures Today</h2>
                <span class="text-xs font-mono text-slate-400 tabular-nums">{{ $kpi['departures_today'] }}</span>
            </div>
            <a href="{{ $rt('panel.fo.departures') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Lihat semua →</a>
        </div>
        @php $departures = $kpi['departures_list'] ?? collect(); @endphp
        @if ($departures->isNotEmpty())
        <ul class="divide-y divide-slate-100">
            @foreach ($departures->take(5) as $r)
                @php
                    $name = $r->primaryGuest?->full_name ?? 'Guest';
                    $initials = collect(explode(' ', $name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
                    $balance = (float) $r->balance;
                    $balCls = $balance > 0 ? 'text-rose-600' : 'text-emerald-600';
                @endphp
                <li class="px-5 py-3 flex items-center gap-3 hover:bg-slate-50 transition-colors">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-cyan-500 text-white flex items-center justify-center text-xs font-bold shrink-0 shadow-sm">{{ $initials }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $name }}</p>
                        <p class="text-[11px] {{ $balCls }} font-mono tabular-nums">Rp {{ number_format($balance, 0, ',', '.') }}</p>
                    </div>
                    <a href="{{ route('panel.fo.reservations.show', $r->id) }}"
                       class="text-xs text-blue-700 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded-full font-semibold transition-colors">View</a>
                </li>
            @endforeach
        </ul>
        @else
        <div class="flex flex-col items-center justify-center py-10 text-slate-400">
            <svg class="w-10 h-10 mb-2 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
            <p class="text-sm">Belum ada departure hari ini</p>
        </div>
        @endif
    </div>

</div>

@endsection
