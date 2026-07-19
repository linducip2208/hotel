<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', 'Portal Investor') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:600,700|inter:300,400,500,600,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js" defer></script>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .font-display { font-family: 'Playfair Display', Georgia, serif; }
        .card-lift { transition: transform .3s, box-shadow .3s; }
        .card-lift:hover { transform: translateY(-4px); box-shadow: 0 16px 32px -8px rgba(0,0,0,.12); }
    </style>
    @stack('head')
</head>
<body class="bg-slate-50 text-slate-800 antialiased h-full"
      x-data="{ sidebarOpen: false }">

<div class="flex h-full">

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed lg:static inset-y-0 left-0 z-30 w-64 bg-gradient-to-b from-slate-900 via-slate-900 to-indigo-950 flex flex-col transition-transform duration-300 lg:transition-none shadow-2xl lg:shadow-none">

        <div class="flex items-center gap-3 px-5 py-5 border-b border-white/5">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-900/40 shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-white font-semibold text-sm leading-tight truncate">{{ $property->name ?? config('app.name') }}</p>
                <p class="text-white/40 text-[10px] tracking-wide uppercase">Portal Investor</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-px">
            <p class="px-3 pt-2 pb-2 text-[10px] uppercase tracking-[0.18em] font-semibold text-white/30">Menu</p>

            @php
                $isActive = fn($r) => request()->routeIs($r) || request()->routeIs($r.'.*');
                $navLink = function($href, $icon, $label) use ($isActive) {
                    $active = $isActive($href) || request()->fullUrlIs($href.'*');
                    $cls = $active
                        ? 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium bg-white/10 text-white'
                        : 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium text-white/65 hover:bg-white/5 hover:text-white transition-colors';
                    $svg = '<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">'.$icon.'</svg>';
                    return "<a href=\"$href\" class=\"$cls\">$svg<span class=\"flex-1 truncate\">$label</span></a>";
                };
            @endphp

            {!! $navLink(route('owner-portal.dashboard'), '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>', 'Dashboard') !!}

            {!! $navLink(route('owner-portal.financials'), '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>', 'Laporan Keuangan') !!}

            {!! $navLink(route('owner-portal.distributions'), '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'Distribusi') !!}
        </nav>

        <div class="border-t border-white/5 px-4 py-3 flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-400 to-violet-500 flex items-center justify-center text-white font-semibold text-xs shadow-md shrink-0">
                {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-xs font-semibold truncate">{{ auth()->user()?->name }}</p>
                <p class="text-white/40 text-[10px] truncate">{{ auth()->user()?->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button title="Keluar" class="text-white/40 hover:text-rose-400 transition-colors p-1.5 rounded-md hover:bg-white/5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </aside>

    {{-- Overlay mobile --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen=false"
         class="fixed inset-0 z-20 bg-slate-900/60 backdrop-blur-sm lg:hidden"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col overflow-y-auto">
        <header class="sticky top-0 z-10 bg-white/85 backdrop-blur-md border-b border-slate-200/70 shadow-sm">
            <div class="flex items-center gap-3 px-4 lg:px-6 py-3">
                <button @click="sidebarOpen=!sidebarOpen"
                        class="lg:hidden p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <span class="font-semibold text-slate-900">@yield('title', 'Portal Investor')</span>
                <div class="flex-1"></div>
                <span class="text-sm text-slate-500">{{ $property->name ?? '' }}</span>
            </div>
        </header>

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show=false, 4000)"
                 class="mx-4 lg:mx-6 mt-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm shadow-sm">
                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
                <button @click="show=false" class="ml-auto text-emerald-400 hover:text-emerald-600">&times;</button>
            </div>
        @endif
        @if(session('error'))
            <div class="mx-4 lg:mx-6 mt-4 flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl px-4 py-3 text-sm shadow-sm">
                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                {{ session('error') }}
            </div>
        @endif

        <main class="flex-1 p-4 lg:p-6">
            @yield('content')
        </main>

        <footer class="border-t border-slate-100 px-6 py-3 text-xs text-slate-400 flex items-center justify-between">
            <span>{{ config('app.name') }} &copy; {{ date('Y') }}</span>
            <span>Portal Investor</span>
        </footer>
    </div>
</div>

@stack('scripts')
</body>
</html>
