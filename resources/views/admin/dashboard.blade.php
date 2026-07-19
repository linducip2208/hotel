@extends('admin.layout')
@section('title', 'Dashboard')
@section('content')

@php
    $rt = function (string $name, ...$args) {
        try { return route($name, $args); } catch (\Throwable) { return '#'; }
    };
    $stats = $stats ?? [];
    $tenants = $stats['tenants'] ?? ['active'=>0,'trial'=>0,'suspended'=>0,'canceled'=>0];
    $recentTenants = $recentTenants ?? collect();
    $recentEvents = $recentEvents ?? collect();
@endphp

{{-- ════════════════ HERO ════════════════ --}}
<div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-rose-950 to-amber-950 p-6 lg:p-8 mb-6 shadow-xl shadow-rose-900/20">
    <div class="absolute inset-0 opacity-30 pointer-events-none"
         style="background-image:radial-gradient(circle at 25% 0%,rgba(244,63,94,.45),transparent 50%),radial-gradient(circle at 80% 100%,rgba(245,158,11,.35),transparent 50%);"></div>
    <div class="relative flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div>
            <p class="text-rose-200/80 text-xs uppercase tracking-[0.2em] font-semibold mb-1">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
            <h1 class="text-2xl lg:text-3xl font-bold text-white tracking-tight">Vendor Operations Dashboard</h1>
            <p class="text-rose-100/70 text-sm mt-1">
                {{ $stats['tenants_total'] ?? 0 }} tenants ·
                {{ $stats['subscriptions'] ?? 0 }} active subscriptions ·
                Rp {{ number_format($stats['mrr'] ?? 0, 0, ',', '.') }} MRR
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ $rt('admin.tenants.create') }}" class="inline-flex items-center gap-2 bg-white text-slate-900 hover:bg-slate-100 text-sm font-semibold px-4 py-2.5 rounded-xl shadow-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Tenant
            </a>
            <a href="{{ $rt('admin.licenses.create') }}" class="inline-flex items-center gap-2 bg-white/10 backdrop-blur text-white hover:bg-white/15 text-sm font-medium px-4 py-2.5 rounded-xl border border-white/15 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Issue License
            </a>
        </div>
    </div>
</div>

{{-- ════════════════ TENANT STATUS GRID ════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mb-6">
    <a href="{{ $rt('admin.tenants.index') }}?status=active" class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all group">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <span class="text-[10px] font-semibold text-emerald-500 uppercase tracking-wide">Active</span>
        </div>
        <div class="text-3xl font-bold text-slate-900 tabular-nums">{{ $tenants['active'] }}</div>
        <div class="text-xs text-slate-500 mt-0.5 group-hover:text-emerald-600 transition-colors">Active Tenants</div>
    </a>

    <a href="{{ $rt('admin.tenants.index') }}?status=trial" class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all group">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-[10px] font-semibold text-indigo-500 uppercase tracking-wide">Trial</span>
        </div>
        <div class="text-3xl font-bold text-slate-900 tabular-nums">{{ $tenants['trial'] }}</div>
        <div class="text-xs text-slate-500 mt-0.5 group-hover:text-indigo-600 transition-colors">In Trial</div>
    </a>

    <a href="{{ $rt('admin.tenants.index') }}?status=suspended" class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all group">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <span class="text-[10px] font-semibold text-amber-600 uppercase tracking-wide">Suspended</span>
        </div>
        <div class="text-3xl font-bold text-slate-900 tabular-nums">{{ $tenants['suspended'] }}</div>
        <div class="text-xs text-slate-500 mt-0.5">Suspended</div>
    </a>

    <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
            <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Canceled</span>
        </div>
        <div class="text-3xl font-bold text-slate-900 tabular-nums">{{ $tenants['canceled'] }}</div>
        <div class="text-xs text-slate-500 mt-0.5">Canceled</div>
    </div>
</div>

{{-- ════════════════ REVENUE & EVENTS ════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    {{-- MRR --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">MRR</p>
                <p class="text-2xl font-bold text-slate-900 tabular-nums mt-1">Rp {{ number_format($stats['mrr'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center shadow-md shadow-emerald-500/30">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100">
            <div>
                <p class="text-[11px] text-slate-400 uppercase font-semibold tracking-wide">Active Subs</p>
                <p class="text-sm font-bold text-slate-900 tabular-nums mt-0.5">{{ $stats['subscriptions'] ?? 0 }}</p>
            </div>
            <div>
                <p class="text-[11px] text-slate-400 uppercase font-semibold tracking-wide">Plans</p>
                <p class="text-sm font-bold text-slate-900 tabular-nums mt-0.5">{{ $stats['plans_total'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    {{-- Invoices --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Revenue This Month</p>
                <p class="text-2xl font-bold text-slate-900 tabular-nums mt-1">Rp {{ number_format($stats['revenue_month'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-400 to-violet-600 flex items-center justify-center shadow-md shadow-indigo-500/30">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100">
            <div>
                <p class="text-[11px] text-slate-400 uppercase font-semibold tracking-wide">Paid</p>
                <p class="text-sm font-bold text-emerald-600 tabular-nums mt-0.5">{{ $stats['invoices_paid'] ?? 0 }}</p>
            </div>
            <div>
                <p class="text-[11px] text-slate-400 uppercase font-semibold tracking-wide">Unpaid</p>
                <p class="text-sm font-bold text-rose-600 tabular-nums mt-0.5">{{ $stats['invoices_unpaid'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    {{-- License events --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">License Events</p>
                <p class="text-2xl font-bold text-slate-900 tabular-nums mt-1">{{ $stats['license_events']['today'] ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-400 to-amber-500 flex items-center justify-center shadow-md shadow-rose-500/30">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100">
            <div>
                <p class="text-[11px] text-slate-400 uppercase font-semibold tracking-wide">Today</p>
                <p class="text-sm font-bold text-slate-900 tabular-nums mt-0.5">{{ $stats['license_events']['today'] ?? 0 }}</p>
            </div>
            <div>
                <p class="text-[11px] text-slate-400 uppercase font-semibold tracking-wide">This Week</p>
                <p class="text-sm font-bold text-slate-900 tabular-nums mt-0.5">{{ $stats['license_events']['week'] ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════ MODULE CATALOG ════════════════ --}}
@php
    $modules = [
        ['cluster' => 'Customers', 'label' => 'All Tenants',     'desc' => 'Manage hotel tenants',          'route' => 'admin.tenants.index',         'color' => 'rose',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>'],
        ['cluster' => 'Customers', 'label' => 'Provision New',   'desc' => 'Onboard new tenant',            'route' => 'admin.tenants.create',        'color' => 'pink',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>'],
        ['cluster' => 'Customers', 'label' => 'All Licenses',    'desc' => 'License master list',           'route' => 'admin.licenses.index',        'color' => 'amber',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>'],
        ['cluster' => 'Customers', 'label' => 'Issue License',   'desc' => 'Generate new key',              'route' => 'admin.licenses.create',       'color' => 'orange',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>'],

        ['cluster' => 'Revenue', 'label' => 'Billing Overview',  'desc' => 'Financial summary',             'route' => 'admin.billing.index',         'color' => 'emerald',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>'],
        ['cluster' => 'Revenue', 'label' => 'Subscriptions',     'desc' => 'Active recurring plans',        'route' => 'admin.billing.subscriptions', 'color' => 'teal',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>'],
        ['cluster' => 'Revenue', 'label' => 'Invoices',          'desc' => 'Issued invoices',               'route' => 'admin.billing.invoices',      'color' => 'lime',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>'],
        ['cluster' => 'Revenue', 'label' => 'Failed Payments',   'desc' => 'Charge retry queue',            'route' => 'admin.billing.failed',        'color' => 'rose',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>'],
        ['cluster' => 'Revenue', 'label' => 'Coupons',           'desc' => 'Discount codes',                'route' => 'admin.billing.coupons',       'color' => 'fuchsia',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>'],

        ['cluster' => 'Operations', 'label' => 'Telemetry',      'desc' => 'Client app health',             'route' => 'admin.telemetry.index',       'color' => 'cyan',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>'],
        ['cluster' => 'Operations', 'label' => 'Errors',         'desc' => 'Tenant-side errors',            'route' => 'admin.telemetry.errors',      'color' => 'red',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['cluster' => 'Operations', 'label' => 'Health',         'desc' => 'Heartbeat & uptime',            'route' => 'admin.telemetry.health',      'color' => 'sky',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>'],
        ['cluster' => 'Operations', 'label' => 'Tickets',        'desc' => 'Customer support',              'route' => 'admin.support.tickets',       'color' => 'blue',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>'],
        ['cluster' => 'Operations', 'label' => 'Knowledge Base', 'desc' => 'Internal articles',             'route' => 'admin.support.kb',            'color' => 'indigo',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>'],

        ['cluster' => 'System',  'label' => 'Plans',             'desc' => 'Pricing tiers',                 'route' => 'admin.system.plans',          'color' => 'violet',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>'],
        ['cluster' => 'System',  'label' => 'Feature Flags',     'desc' => 'Toggle features',               'route' => 'admin.system.flags',          'color' => 'purple',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>'],
        ['cluster' => 'System',  'label' => 'Email Templates',   'desc' => 'Notification copy',             'route' => 'admin.system.email-templates','color' => 'pink',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'],
        ['cluster' => 'System',  'label' => 'Audit Log',         'desc' => 'System activity trail',         'route' => 'admin.system.audit',          'color' => 'slate',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>'],
        ['cluster' => 'System',  'label' => 'Admin Users',       'desc' => 'Manage admin team',             'route' => 'admin.admin-users.index',     'color' => 'gray',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
    ];
    $clusters = collect($modules)->groupBy('cluster');
@endphp

<div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden mb-6">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
        <div>
            <h2 class="text-base font-bold text-slate-900">Semua Modul Admin</h2>
            <p class="text-xs text-slate-500 mt-0.5">{{ count($modules) }} fitur tersedia · 51 routes total</p>
        </div>
    </div>

    @foreach ($clusters as $cluster => $items)
    <div class="px-6 py-5 border-b border-slate-100 last:border-b-0">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.18em] mb-3">{{ $cluster }}</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2.5">
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

{{-- ════════════════ ACTIVITY ROW ════════════════ --}}
<div class="grid lg:grid-cols-2 gap-4">

    {{-- Recent tenants --}}
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-rose-100 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                </div>
                <h2 class="text-sm font-bold text-slate-800">Recent Tenants</h2>
                <span class="text-xs font-mono text-slate-400 tabular-nums">{{ $recentTenants->count() }}</span>
            </div>
            <a href="{{ $rt('admin.tenants.index') }}" class="text-xs text-rose-600 hover:text-rose-700 font-medium">Lihat semua →</a>
        </div>
        @if ($recentTenants->isNotEmpty())
        <ul class="divide-y divide-slate-100">
            @foreach ($recentTenants as $t)
                @php
                    $name = $t->name ?? $t->id;
                    $initials = collect(explode(' ', $name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
                    $statusColors = ['active' => 'emerald', 'trial' => 'indigo', 'suspended' => 'amber', 'canceled' => 'slate'];
                    $sc = $statusColors[$t->status ?? ''] ?? 'slate';
                @endphp
                <li class="px-5 py-3 flex items-center gap-3 hover:bg-slate-50 transition-colors">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-rose-400 to-amber-500 text-white flex items-center justify-center text-xs font-bold shrink-0 shadow-sm">{{ $initials ?: '?' }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $name }}</p>
                        <p class="text-[11px] text-slate-400 tabular-nums">{{ optional($t->created_at)->diffForHumans() }}</p>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2 py-0.5 rounded-full">{{ $t->status ?? 'unknown' }}</span>
                    <a href="{{ $rt('admin.tenants.show', $t->id) }}" class="text-xs text-rose-600 bg-rose-50 hover:bg-rose-100 px-2.5 py-1 rounded-full font-semibold transition-colors">View</a>
                </li>
            @endforeach
        </ul>
        @else
        <div class="flex flex-col items-center justify-center py-10 text-slate-400">
            <svg class="w-10 h-10 mb-2 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
            <p class="text-sm">Belum ada tenant</p>
        </div>
        @endif
    </div>

    {{-- Recent license events --}}
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h2 class="text-sm font-bold text-slate-800">License Activity</h2>
                <span class="text-xs font-mono text-slate-400 tabular-nums">{{ $recentEvents->count() }}</span>
            </div>
            <a href="{{ $rt('admin.licenses.index') }}" class="text-xs text-amber-600 hover:text-amber-700 font-medium">Lihat semua →</a>
        </div>
        @if ($recentEvents->isNotEmpty())
        <ul class="divide-y divide-slate-100">
            @foreach ($recentEvents as $ev)
                @php
                    $eventColor = match(true) {
                        str_contains($ev->event_type ?? '', 'pair') => 'emerald',
                        str_contains($ev->event_type ?? '', 'revoke') => 'rose',
                        str_contains($ev->event_type ?? '', 'heartbeat') => 'indigo',
                        str_contains($ev->event_type ?? '', 'expire') => 'amber',
                        default => 'slate',
                    };
                @endphp
                <li class="px-5 py-3 flex items-center gap-3 hover:bg-slate-50 transition-colors">
                    <div class="w-2 h-2 rounded-full bg-{{ $eventColor }}-500 shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $ev->event_type ?? 'event' }}</p>
                        <p class="text-[11px] text-slate-400 tabular-nums">{{ optional($ev->created_at)->diffForHumans() }}</p>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400 truncate max-w-[100px]">{{ $ev->license_id ?? $ev->tenant_id ?? '' }}</span>
                </li>
            @endforeach
        </ul>
        @else
        <div class="flex flex-col items-center justify-center py-10 text-slate-400">
            <svg class="w-10 h-10 mb-2 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <p class="text-sm">Belum ada aktivitas license</p>
        </div>
        @endif
    </div>

</div>

@endsection
