<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $property?->name ?? config('app.name'))</title>
    <meta name="description" content="@yield('description', ($property?->name ?? '') . ' — Hotel di ' . ($property?->city ?? 'Indonesia') . '. Reservasi online, pengalaman menginap nyaman, fasilitas lengkap.')">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:600,700,900|inter:300,400,500,600,700&display=swap" rel="stylesheet">
    @stack('head')
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .font-display { font-family: 'Playfair Display', Georgia, serif; }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body class="bg-stone-50 text-slate-800 antialiased">

{{-- ════════════════════════ HEADER ════════════════════════ --}}
<header x-data="{ scrolled: false, mobileOpen: false }"
        @scroll.window="scrolled = window.scrollY > 8"
        :class="scrolled ? 'bg-white/95 backdrop-blur shadow-sm border-slate-200' : 'bg-transparent border-transparent'"
        class="fixed inset-x-0 top-0 z-40 border-b transition-all duration-200">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">
            {{-- Brand --}}
            <a href="/" class="flex items-center gap-2.5 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div class="hidden sm:block leading-tight">
                    <p :class="scrolled ? 'text-slate-900' : 'text-white drop-shadow'" class="font-display font-bold text-lg transition-colors">{{ $property?->name ?? config('app.name') }}</p>
                    @if($property?->city)
                        <p :class="scrolled ? 'text-slate-500' : 'text-white/80 drop-shadow'" class="text-[10px] uppercase tracking-[0.2em] transition-colors">{{ $property->city }}</p>
                    @endif
                </div>
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden lg:flex items-center gap-8">
                <a href="/" :class="scrolled ? 'text-slate-700' : 'text-white/90 drop-shadow'" class="text-sm font-medium hover:text-indigo-500 transition-colors">Beranda</a>
                <a href="/rooms" :class="scrolled ? 'text-slate-700' : 'text-white/90 drop-shadow'" class="text-sm font-medium hover:text-indigo-500 transition-colors">Kamar</a>
                <a href="/blog" :class="scrolled ? 'text-slate-700' : 'text-white/90 drop-shadow'" class="text-sm font-medium hover:text-indigo-500 transition-colors">Blog</a>
                <a href="/about" :class="scrolled ? 'text-slate-700' : 'text-white/90 drop-shadow'" class="text-sm font-medium hover:text-indigo-500 transition-colors">Tentang</a>
                <a href="/contact" :class="scrolled ? 'text-slate-700' : 'text-white/90 drop-shadow'" class="text-sm font-medium hover:text-indigo-500 transition-colors">Kontak</a>
            </nav>

            {{-- Right --}}
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ url('/admin') }}"
                       :class="scrolled ? 'text-slate-700 hover:bg-slate-100' : 'text-white hover:bg-white/10'"
                       class="hidden sm:inline-flex items-center gap-1.5 text-sm font-medium px-3 py-2 rounded-full transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       :class="scrolled ? 'text-slate-700 hover:bg-slate-100' : 'text-white hover:bg-white/10'"
                       class="hidden sm:inline-flex items-center gap-1.5 text-sm font-medium px-3 py-2 rounded-full transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        Login
                    </a>
                @endauth
                <div class="relative hidden sm:block" x-data="{ openLang: false }">
                    <button @click="openLang=!openLang"
                            :class="scrolled ? 'text-slate-700 hover:bg-slate-100' : 'text-white hover:bg-white/10'"
                            class="inline-flex items-center gap-1.5 text-sm font-medium px-3 py-2 rounded-full transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                        {{ strtoupper(app()->getLocale()) }}
                    </button>
                    <div x-show="openLang" @click.outside="openLang=false" x-cloak
                         class="absolute right-0 top-full mt-2 w-32 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden z-50">
                        <a href="{{ route('locale.switch', 'id') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm {{ app()->getLocale() === 'id' ? 'text-indigo-700 bg-indigo-50 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                            <span>🇮🇩</span> Bahasa Indonesia
                        </a>
                        <a href="{{ route('locale.switch', 'en') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm {{ app()->getLocale() === 'en' ? 'text-indigo-700 bg-indigo-50 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                            <span>🇬🇧</span> English
                        </a>
                    </div>
                </div>
                <a href="/booking"
                   class="hidden sm:inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-full shadow-md shadow-indigo-500/30 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Book Now
                </a>
                <button @click="mobileOpen = !mobileOpen"
                        :class="scrolled ? 'text-slate-700 hover:bg-slate-100' : 'text-white hover:bg-white/10'"
                        class="lg:hidden p-2 rounded-lg transition-colors">
                    <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile nav --}}
        <div x-show="mobileOpen" x-cloak x-transition class="lg:hidden bg-white border-t border-slate-100 py-3 space-y-1">
            <a href="/" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Beranda</a>
            <a href="/rooms" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Kamar</a>
            <a href="/blog" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Blog</a>
            <a href="/about" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Tentang</a>
            <a href="/contact" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Kontak</a>
            @auth
                <a href="{{ url('/admin') }}" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Login</a>
            @endauth
            <a href="{{ route('locale.switch', 'id') }}" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">🇮🇩 Bahasa Indonesia</a>
            <a href="{{ route('locale.switch', 'en') }}" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">🇬🇧 English</a>
            <a href="/booking" class="block mx-2 mt-2 text-center bg-indigo-600 text-white text-sm font-semibold py-2.5 rounded-lg">Book Now</a>
        </div>
    </div>
</header>

{{-- ════════════════════════ MAIN ════════════════════════ --}}
<main>
    @yield('content')
</main>

{{-- ════════════════════════ FOOTER ════════════════════════ --}}
<footer class="bg-slate-900 text-slate-300 mt-16">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-12 lg:py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
            {{-- Brand col --}}
            <div class="lg:col-span-2">
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
                    </div>
                    <p class="font-display font-bold text-xl text-white">{{ $property?->name ?? config('app.name') }}</p>
                </div>
                <p class="text-sm text-slate-400 max-w-md leading-relaxed">
                    @if($property)
                        {{ $property->description ?? 'Pengalaman menginap istimewa dengan layanan profesional di ' . ($property->city ?? 'Indonesia') . '.' }}
                    @else
                        Pengalaman menginap istimewa dengan layanan profesional.
                    @endif
                </p>
                @if($property?->star_rating)
                    <div class="flex items-center gap-1 mt-3">
                        @for($i = 0; $i < $property->star_rating; $i++)
                            <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                @endif
            </div>

            {{-- Quick links --}}
            <div>
                <p class="text-xs font-semibold text-white uppercase tracking-[0.18em] mb-4">Navigasi</p>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="/" class="text-slate-400 hover:text-white transition-colors">Beranda</a></li>
                    <li><a href="/rooms" class="text-slate-400 hover:text-white transition-colors">Kamar &amp; Tarif</a></li>
                    <li><a href="/blog" class="text-slate-400 hover:text-white transition-colors">Blog</a></li>
                    <li><a href="/booking" class="text-slate-400 hover:text-white transition-colors">Reservasi</a></li>
                    <li><a href="/about" class="text-slate-400 hover:text-white transition-colors">Tentang Kami</a></li>
                    <li><a href="/contact" class="text-slate-400 hover:text-white transition-colors">Kontak</a></li>
                    @auth
                        <li><a href="{{ url('/admin') }}" class="text-slate-400 hover:text-white transition-colors">Dashboard</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="text-slate-400 hover:text-white transition-colors">Login Staff</a></li>
                    @endauth
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <p class="text-xs font-semibold text-white uppercase tracking-[0.18em] mb-4">Hubungi Kami</p>
                <ul class="space-y-2.5 text-sm">
                    @if($property?->address_line1)
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-slate-400">{{ $property->address_line1 }}{{ $property->city ? ', ' . $property->city : '' }}</span>
                        </li>
                    @endif
                    @if($property?->phone)
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <a href="tel:{{ $property->phone }}" class="text-slate-400 hover:text-white transition-colors">{{ $property->phone }}</a>
                        </li>
                    @endif
                    @if($property?->email)
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <a href="mailto:{{ $property->email }}" class="text-slate-400 hover:text-white transition-colors">{{ $property->email }}</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10 pt-6 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <p>&copy; {{ now()->year }} {{ $property?->name ?? config('app.name') }}. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <a href="/privacy" class="hover:text-white transition-colors">Privacy</a>
                <a href="/terms" class="hover:text-white transition-colors">Terms</a>
                <span class="text-slate-700">·</span>
                <span>Powered by Hotel HMS</span>
            </div>
        </div>
    </div>
</footer>

{{-- ════════════════════════ SOURCE CODE PURCHASE POPUP ════════════════════════ --}}
<div id="sc-badge"
     style="position:fixed;bottom:24px;right:24px;z-index:9999;display:none;cursor:pointer;"
     onclick="document.getElementById('sc-popup').style.display='flex';this.style.display='none';"
     title="Info source code">
    <div style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border-radius:50px;padding:10px 18px;
                font-size:13px;font-weight:600;box-shadow:0 4px 20px rgba(79,70,229,.45);
                display:flex;align-items:center;gap:8px;
                animation:sc-bounce 2.5s infinite;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
        </svg>
        Source Code
    </div>
</div>

<div id="sc-popup"
     style="position:fixed;bottom:24px;right:24px;z-index:9999;display:none;flex-direction:column;
            width:320px;border-radius:20px;overflow:hidden;
            box-shadow:0 20px 60px rgba(0,0,0,.18),0 4px 16px rgba(79,70,229,.2);
            animation:sc-slidein .35s cubic-bezier(.22,.68,0,1.2) both;">
    <div style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);padding:18px 20px 14px;position:relative;">
        <button onclick="scDismiss()"
                style="position:absolute;top:12px;right:14px;background:rgba(255,255,255,.15);
                       border:none;color:#fff;border-radius:50%;width:26px;height:26px;cursor:pointer;
                       font-size:16px;line-height:1;display:flex;align-items:center;justify-content:center;"
                aria-label="Close">×</button>
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="background:rgba(255,255,255,.15);border-radius:12px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="20" height="20" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
            </div>
            <div>
                <p style="margin:0;font-size:15px;font-weight:700;color:#fff;">Hotel HMS Source Code</p>
                <p style="margin:2px 0 0;font-size:12px;color:rgba(255,255,255,.75);">Laravel 11 · Full Stack</p>
            </div>
        </div>
    </div>
    <div style="background:#fff;padding:18px 20px 20px;">
        <p style="margin:0 0 12px;font-size:13.5px;color:#374151;line-height:1.55;">
            Dapatkan <strong>source code lengkap</strong> sistem manajemen hotel ini —
            termasuk Front Office, POS, Accounting, Channel Manager, RMS, dan 17 modul lainnya.
        </p>
        <div style="background:#f5f3ff;border-radius:10px;padding:12px 14px;margin-bottom:14px;">
            <p style="margin:0 0 4px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6d28d9;">✦ Yang Kamu Dapat</p>
            <ul style="margin:0;padding-left:16px;font-size:12.5px;color:#4b5563;line-height:1.8;">
                <li>Source code Laravel 11 + 341 PHP files</li>
                <li>141 Blade views + Tailwind UI system</li>
                <li>46 migrations, 130 Eloquent models</li>
                <li>BYOK integrations (AI/Payment/SMS/Channel)</li>
                <li>Pest 84/84 test suite + full docs</li>
            </ul>
        </div>
        <a href="/docs.html" target="_blank" rel="noopener"
           style="display:flex;align-items:center;justify-content:center;gap:8px;background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe;border-radius:12px;padding:10px 16px;font-size:13px;font-weight:700;text-decoration:none;margin-bottom:8px;transition:all .15s;"
           onmouseover="this.style.background='#e0e7ff';this.style.borderColor='#a5b4fc';"
           onmouseout="this.style.background='#eef2ff';this.style.borderColor='#c7d2fe';">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Baca Panduan Lengkap (Docs)
        </a>
        <a href="https://wa.me/62081296052010?text=Halo%2C+saya+tertarik+dengan+source+code+Hotel+HMS+Laravel.+Boleh+info+lebih+lanjut%3F"
           target="_blank" rel="noopener"
           style="display:flex;align-items:center;justify-content:center;gap:8px;background:#25d366;color:#fff;border-radius:12px;padding:11px 16px;font-size:13.5px;font-weight:700;text-decoration:none;margin-bottom:8px;transition:background .15s;"
           onmouseover="this.style.background='#1fba59'" onmouseout="this.style.background='#25d366'">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Chat di WhatsApp
        </a>
        <p style="text-align:center;font-size:12px;color:#9ca3af;margin:0;">📞 <strong style="color:#374151;">081296052010</strong> · Respon cepat</p>
    </div>
</div>

<style>
@keyframes sc-slidein { from { opacity:0; transform:translateY(24px) scale(.95); } to { opacity:1; transform:translateY(0) scale(1); } }
@keyframes sc-bounce  { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-4px); } }
[x-cloak] { display: none !important; }
</style>

<script>
(function() {
    var DISMISS_KEY = 'sc_popup_dismissed_v1';
    var popup = document.getElementById('sc-popup');
    var badge = document.getElementById('sc-badge');
    if (localStorage.getItem(DISMISS_KEY)) { popup.style.display = 'none'; badge.style.display = 'block'; return; }
    setTimeout(function() { popup.style.display = 'flex'; }, 2500);
})();
function scDismiss() {
    document.getElementById('sc-popup').style.display = 'none';
    document.getElementById('sc-badge').style.display = 'block';
    localStorage.setItem('sc_popup_dismissed_v1', '1');
}
</script>

</body>
</html>
