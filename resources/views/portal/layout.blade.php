<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Portal Tamu — ' . config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:600,700|inter:300,400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .font-display { font-family: 'Playfair Display', Georgia, serif; }
    </style>
    @stack('head')
</head>
<body class="bg-stone-50 text-slate-800 antialiased min-h-screen flex flex-col">

<header class="fixed inset-x-0 top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition-transform">
                    <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <span class="font-display font-bold text-lg text-slate-900">{{ config('app.name') }}</span>
            </a>

            <nav class="hidden md:flex items-center gap-6">
                <a href="{{ route('customer.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors {{ request()->routeIs('customer.dashboard') ? 'text-indigo-600' : '' }}">Dashboard</a>
                <a href="{{ route('customer.bookings') }}" class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors {{ request()->routeIs('customer.bookings*') ? 'text-indigo-600' : '' }}">Pesanan Saya</a>
                <a href="{{ route('customer.invoices') }}" class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors {{ request()->routeIs('customer.invoices*') ? 'text-indigo-600' : '' }}">Tagihan</a>
            </nav>

            <div class="flex items-center gap-3">
                <span class="hidden sm:inline text-sm text-slate-500">
                    Halo, <span class="font-semibold text-slate-700">{{ auth('customer')->user()->name }}</span>
                </span>
                <form action="{{ route('customer.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 px-3 py-2 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<main class="flex-1 pt-24 pb-16">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        @if(session('status'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-5 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-3 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</main>

<footer class="bg-slate-900 text-slate-400">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm">
            <p>&copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <a href="/" class="hover:text-white transition-colors">Beranda</a>
                <a href="/contact" class="hover:text-white transition-colors">Kontak</a>
                <span class="text-slate-700">·</span>
                <span>Portal Tamu</span>
            </div>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
