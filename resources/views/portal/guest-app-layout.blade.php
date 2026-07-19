<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <title>@yield('title', 'Portal Tamu') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .bottom-nav-bar { padding-bottom: env(safe-area-inset-bottom, 0px); }
        @media (min-width: 768px) {
            .bottom-nav-bar { display: none; }
        }
    </style>
    @stack('head')
</head>
<body class="h-full bg-stone-50 text-stone-800 antialiased">

{{-- Desktop: Top Header --}}
<header class="hidden md:flex fixed inset-x-0 top-0 z-40 bg-white/95 backdrop-blur border-b border-stone-200 shadow-sm h-16 items-center px-6">
    <div class="max-w-6xl w-full mx-auto flex items-center justify-between">
        <a href="{{ route('customer.guest.dashboard') }}" class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <span class="font-bold text-lg text-stone-900">{{ config('app.name') }}</span>
        </a>
        <nav class="flex items-center gap-6">
            <a href="{{ route('customer.guest.dashboard') }}" class="text-sm font-medium text-stone-600 hover:text-indigo-600 transition-colors {{ request()->routeIs('customer.guest.dashboard') ? 'text-indigo-600' : '' }}">Dashboard</a>
            <a href="{{ route('customer.guest.booking') }}" class="text-sm font-medium text-stone-600 hover:text-indigo-600 transition-colors {{ request()->routeIs('customer.guest.booking') ? 'text-indigo-600' : '' }}">Pesanan Saya</a>
            <a href="{{ route('customer.guest.room-service') }}" class="text-sm font-medium text-stone-600 hover:text-indigo-600 transition-colors {{ request()->routeIs('customer.guest.room-service') ? 'text-indigo-600' : '' }}">Room Service</a>
            <a href="{{ route('customer.guest.requests') }}" class="text-sm font-medium text-stone-600 hover:text-indigo-600 transition-colors {{ request()->routeIs('customer.guest.requests') ? 'text-indigo-600' : '' }}">Permintaan</a>
            <a href="{{ route('customer.guest.chat') }}" class="text-sm font-medium text-stone-600 hover:text-indigo-600 transition-colors {{ request()->routeIs('customer.guest.chat') ? 'text-indigo-600' : '' }}">Chat</a>
        </nav>
        <div class="flex items-center gap-3">
            <span class="text-sm text-stone-500">Halo, <span class="font-semibold text-stone-700">{{ auth('customer')->user()->name ?? auth('customer')->user()->first_name }}</span></span>
            <form action="{{ route('customer.logout') }}" method="POST">
                @csrf
                <button class="text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 px-3 py-1.5 rounded-lg transition-colors">Keluar</button>
            </form>
        </div>
    </div>
</header>

{{-- Main Content --}}
<main class="md:pt-20 pt-4 pb-24 md:pb-16 min-h-full">
    <div class="max-w-6xl mx-auto px-4 md:px-6">
        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
                <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif
        @yield('content')
    </div>
</main>

{{-- Mobile Bottom Navigation Bar --}}
<nav class="bottom-nav-bar fixed inset-x-0 bottom-0 z-50 bg-white border-t border-stone-200 shadow-lg">
    <div class="flex items-center justify-around py-2">
        <a href="{{ route('customer.guest.dashboard') }}" class="flex flex-col items-center gap-0.5 px-2 py-1 text-[10px] font-medium {{ request()->routeIs('customer.guest.dashboard') ? 'text-indigo-600' : 'text-stone-400' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Beranda
        </a>
        <a href="{{ route('customer.guest.booking') }}" class="flex flex-col items-center gap-0.5 px-2 py-1 text-[10px] font-medium {{ request()->routeIs('customer.guest.booking') ? 'text-indigo-600' : 'text-stone-400' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Pesanan
        </a>
        <a href="{{ route('customer.guest.room-service') }}" class="flex flex-col items-center gap-0.5 px-2 py-1 text-[10px] font-medium {{ request()->routeIs('customer.guest.room-service') ? 'text-indigo-600' : 'text-stone-400' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Pesan
        </a>
        <a href="{{ route('customer.guest.requests') }}" class="flex flex-col items-center gap-0.5 px-2 py-1 text-[10px] font-medium {{ request()->routeIs('customer.guest.requests') ? 'text-indigo-600' : 'text-stone-400' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Minta
        </a>
        <a href="{{ route('customer.guest.chat') }}" class="flex flex-col items-center gap-0.5 px-2 py-1 text-[10px] font-medium {{ request()->routeIs('customer.guest.chat') ? 'text-indigo-600' : 'text-stone-400' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            Chat
        </a>
    </div>
</nav>

@stack('scripts')
</body>
</html>
