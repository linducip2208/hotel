<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','Admin') — {{ config('app.name') }}</title>
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet">
@vite(['resources/css/app.css','resources/js/app.js'])
<style>
    .scrollbar-thin{scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.18) transparent}
    .scrollbar-thin::-webkit-scrollbar{width:6px}
    .scrollbar-thin::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:3px}
    .nav-chevron{transition:transform .2s}
    [aria-expanded="true"] .nav-chevron{transform:rotate(90deg)}
</style>
</head>
<body class="h-full bg-slate-50 font-sans antialiased text-slate-800"
      x-data="{ sidebarMobile: false }">

{{-- Mobile overlay --}}
<div x-show="sidebarMobile" x-cloak
     x-transition:enter="transition-opacity ease-out duration-200"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     @click="sidebarMobile=false"
     class="fixed inset-0 z-20 bg-slate-900/60 backdrop-blur-sm lg:hidden"></div>

{{-- ════════════════ SIDEBAR ════════════════ --}}
<aside :class="sidebarMobile ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed inset-y-0 left-0 z-30 w-72 flex flex-col
              bg-gradient-to-b from-slate-950 via-slate-900 to-rose-950
              shadow-2xl transition-transform duration-300 ease-out lg:transition-none">

    <div class="flex items-center gap-3 px-5 py-5 border-b border-white/5">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-amber-500 flex items-center justify-center shadow-lg shadow-rose-900/40 shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-white font-semibold text-sm leading-tight truncate">Vendor Admin</p>
            <p class="text-white/40 text-[11px] tracking-wide uppercase">{{ config('app.name') }}</p>
        </div>
        <button @click="sidebarMobile=false" class="lg:hidden text-white/60 hover:text-white p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    @php
        $safeRoute = function (string $name) {
            try { return route($name); } catch (\Throwable) { return '#'; }
        };
        $isActive = function (string $name) {
            return request()->routeIs($name) || request()->routeIs($name.'.*');
        };
        $groupActive = function (array $names) {
            foreach ($names as $n) if (request()->routeIs($n) || request()->routeIs($n.'.*')) return true;
            return false;
        };
        $renderItem = function (string $icon, string $label, string $route) use ($safeRoute, $isActive) {
            $active = $isActive($route);
            $cls = $active
                ? 'flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium bg-white/10 text-white shadow-inner'
                : 'flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium text-white/65 hover:bg-white/5 hover:text-white transition-colors';
            return '<a href="' . $safeRoute($route) . '" class="' . $cls . '">' . $icon . '<span class="flex-1 truncate">' . $label . '</span></a>';
        };
        $cluster = fn(string $l) => '<p class="px-3 pt-5 pb-2 text-[10px] uppercase tracking-[0.18em] font-semibold text-white/30">' . $l . '</p>';
    @endphp

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-px scrollbar-thin">

        {!! $renderItem(
            '<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
            'Dashboard', 'admin.dashboard'
        ) !!}

        {!! $cluster('Customers') !!}

        @php $tnOpen = $groupActive(['admin.tenants']); @endphp
        <div x-data="{ open: {{ $tnOpen ? 'true' : 'false' }} }" class="space-y-px">
            <button @click="open=!open" :aria-expanded="open ? 'true' : 'false'"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium {{ $tnOpen ? 'text-white' : 'text-white/65 hover:bg-white/5 hover:text-white' }} transition-colors">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span class="flex-1 text-left">Tenants</span>
                <svg class="nav-chevron w-3.5 h-3.5 text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" x-collapse class="ml-4 pl-3 border-l border-white/5 space-y-px">
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'All Tenants',     'admin.tenants.index') !!}
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'Provision New',   'admin.tenants.create') !!}
            </div>
        </div>

        @php $lcOpen = $groupActive(['admin.licenses']); @endphp
        <div x-data="{ open: {{ $lcOpen ? 'true' : 'false' }} }" class="space-y-px">
            <button @click="open=!open" :aria-expanded="open ? 'true' : 'false'"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium {{ $lcOpen ? 'text-white' : 'text-white/65 hover:bg-white/5 hover:text-white' }} transition-colors">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span class="flex-1 text-left">Licenses</span>
                <svg class="nav-chevron w-3.5 h-3.5 text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" x-collapse class="ml-4 pl-3 border-l border-white/5 space-y-px">
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'All Licenses',    'admin.licenses.index') !!}
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'Issue New',       'admin.licenses.create') !!}
            </div>
        </div>

        {!! $cluster('Revenue') !!}

        @php $blOpen = $groupActive(['admin.billing']); @endphp
        <div x-data="{ open: {{ $blOpen ? 'true' : 'false' }} }" class="space-y-px">
            <button @click="open=!open" :aria-expanded="open ? 'true' : 'false'"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium {{ $blOpen ? 'text-white' : 'text-white/65 hover:bg-white/5 hover:text-white' }} transition-colors">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="flex-1 text-left">Billing</span>
                <svg class="nav-chevron w-3.5 h-3.5 text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" x-collapse class="ml-4 pl-3 border-l border-white/5 space-y-px">
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'Overview',        'admin.billing.index') !!}
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'Subscriptions',   'admin.billing.subscriptions') !!}
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'Invoices',        'admin.billing.invoices') !!}
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'Failed Payments', 'admin.billing.failed') !!}
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'Coupons',         'admin.billing.coupons') !!}
            </div>
        </div>

        {!! $cluster('Operations') !!}

        @php $tlOpen = $groupActive(['admin.telemetry']); @endphp
        <div x-data="{ open: {{ $tlOpen ? 'true' : 'false' }} }" class="space-y-px">
            <button @click="open=!open" :aria-expanded="open ? 'true' : 'false'"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium {{ $tlOpen ? 'text-white' : 'text-white/65 hover:bg-white/5 hover:text-white' }} transition-colors">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span class="flex-1 text-left">Telemetry</span>
                <svg class="nav-chevron w-3.5 h-3.5 text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" x-collapse class="ml-4 pl-3 border-l border-white/5 space-y-px">
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'Overview',        'admin.telemetry.index') !!}
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'Errors',          'admin.telemetry.errors') !!}
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'Health',          'admin.telemetry.health') !!}
            </div>
        </div>

        @php $spOpen = $groupActive(['admin.support']); @endphp
        <div x-data="{ open: {{ $spOpen ? 'true' : 'false' }} }" class="space-y-px">
            <button @click="open=!open" :aria-expanded="open ? 'true' : 'false'"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium {{ $spOpen ? 'text-white' : 'text-white/65 hover:bg-white/5 hover:text-white' }} transition-colors">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span class="flex-1 text-left">Support</span>
                <svg class="nav-chevron w-3.5 h-3.5 text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" x-collapse class="ml-4 pl-3 border-l border-white/5 space-y-px">
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'Tickets',         'admin.support.tickets') !!}
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'Knowledge Base',  'admin.support.kb') !!}
            </div>
        </div>

        {!! $cluster('System') !!}

        @php $syOpen = $groupActive(['admin.system','admin.admin-users']); @endphp
        <div x-data="{ open: {{ $syOpen ? 'true' : 'false' }} }" class="space-y-px">
            <button @click="open=!open" :aria-expanded="open ? 'true' : 'false'"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium {{ $syOpen ? 'text-white' : 'text-white/65 hover:bg-white/5 hover:text-white' }} transition-colors">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="flex-1 text-left">Configuration</span>
                <svg class="nav-chevron w-3.5 h-3.5 text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" x-collapse class="ml-4 pl-3 border-l border-white/5 space-y-px">
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'Plans',           'admin.system.plans') !!}
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'Feature Flags',   'admin.system.flags') !!}
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'Email Templates', 'admin.system.email-templates') !!}
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'Audit Log',       'admin.system.audit') !!}
                {!! $renderItem('<span class="w-1.5 h-1.5 rounded-full bg-white/30 shrink-0"></span>', 'Admin Users',     'admin.admin-users.index') !!}
            </div>
        </div>

    </nav>

    <div class="border-t border-white/5 px-4 py-3 flex items-center gap-3">
        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-rose-400 to-amber-500 flex items-center justify-center text-white font-semibold text-xs shadow-md shrink-0">
            {{ strtoupper(substr(auth('admin')->user()?->name ?? 'A', 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-white text-xs font-semibold truncate">{{ auth('admin')->user()?->name ?? 'Admin' }}</p>
            <p class="text-white/40 text-[10px] truncate">{{ auth('admin')->user()?->email }}</p>
        </div>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button title="Logout" class="text-white/40 hover:text-rose-400 transition-colors p-1.5 rounded-md hover:bg-white/5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </button>
        </form>
    </div>
</aside>

{{-- ════════════════ MAIN ════════════════ --}}
<div class="lg:pl-72 flex flex-col min-h-full">
    <header class="sticky top-0 z-10 bg-white/85 backdrop-blur-md border-b border-slate-200/70 shadow-sm">
        <div class="flex items-center gap-3 px-4 lg:px-6 py-3">
            <button @click="sidebarMobile=!sidebarMobile"
                    class="lg:hidden p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="hidden lg:flex items-center gap-2 text-sm">
                <span class="text-slate-400">Vendor Admin</span>
                <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="font-semibold text-slate-900">@yield('title', 'Dashboard')</span>
            </div>
            <div class="flex-1"></div>
            <a href="{{ route('admin.tenants.create') }}"
               class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold px-3.5 py-1.5 rounded-xl transition-colors shadow-sm shadow-rose-500/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span class="hidden sm:inline">New Tenant</span>
            </a>
        </div>
    </header>

    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show=false, 4000)"
         class="mx-4 lg:mx-6 mt-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm shadow-sm">
        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <main class="flex-1 p-4 lg:p-6">
        @yield('content')
    </main>

    <footer class="border-t border-slate-100 px-6 py-3 text-xs text-slate-400 flex items-center justify-between">
        <span>{{ config('app.name') }} Vendor Admin &copy; {{ date('Y') }}</span>
        <span>v{{ config('app.version', '1.0') }}</span>
    </footer>
</div>

</body>
</html>
