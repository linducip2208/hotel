<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:600,700,900|inter:300,400,500,600,700,800|jetbrains-mono:400,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .font-display { font-family: 'Playfair Display', Georgia, serif; }
        @keyframes floatSlow { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
        @keyframes fadeSlideUp { 0%{transform:translateY(24px);opacity:0} 100%{transform:translateY(0);opacity:1} }
        .animate-float-slow { animation: floatSlow 6s ease-in-out infinite; }
        .animate-fade-slide { animation: fadeSlideUp .7s cubic-bezier(.16,1,.3,1) both; }
        .delay-100 { animation-delay: .1s; }
        .delay-200 { animation-delay: .2s; }
        .delay-300 { animation-delay: .3s; }
        .delay-400 { animation-delay: .4s; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-stone-50 antialiased">

<div class="min-h-screen grid lg:grid-cols-2">
    {{-- ═══════════ LEFT: Hero Brand Panel ═══════════ --}}
    <div class="hidden lg:flex relative bg-gradient-to-br from-indigo-700 via-indigo-900 to-slate-950 p-12 flex-col justify-between overflow-hidden">
        {{-- Decorative circles --}}
        <div class="absolute inset-0 opacity-30"
             style="background-image:
                radial-gradient(circle at 20% 30%, rgba(139,92,246,.5) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(236,72,153,.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 85%, rgba(99,102,241,.4) 0%, transparent 45%);"></div>
        {{-- Grid texture --}}
        <div class="absolute inset-0 opacity-[0.06]"
             style="background-image:linear-gradient(rgba(255,255,255,.4) 1px, transparent 1px),linear-gradient(90deg, rgba(255,255,255,.4) 1px, transparent 1px);background-size:60px 60px;"></div>
        {{-- Large decorative hotel icon --}}
        <div class="absolute -bottom-20 -right-20 text-[20rem] opacity-[0.07] select-none">🏨</div>

        {{-- Logo + Brand --}}
        <div class="relative">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-white/20 to-white/5 backdrop-blur flex items-center justify-center shadow-lg shadow-black/20 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <span class="font-display font-bold text-3xl text-white tracking-tight">{{ config('app.name') }}</span>
                    <p class="text-indigo-200/60 text-[11px] uppercase tracking-[0.2em] mt-0.5">Hotel Management System</p>
                </div>
            </a>
        </div>

        {{-- Tagline + Benefits --}}
        <div class="relative text-white">
            <h2 class="font-display text-5xl font-bold leading-[1.1] mb-5 animate-fade-slide">
                Kelola Hotel, <br>Tanpa Batas.
            </h2>
            <p class="text-indigo-100/80 text-lg leading-relaxed mb-10 max-w-md animate-fade-slide delay-100">
                Sistem manajemen hotel all-in-one: Front Office, POS, Accounting, Channel Manager, RMS, dan 17 modul lainnya — dalam satu dashboard.
            </p>
            <div class="grid grid-cols-3 gap-4 max-w-md animate-fade-slide delay-200">
                <div class="bg-white/10 backdrop-blur border border-white/15 rounded-2xl p-4 text-center">
                    <div class="text-2xl mb-1">🏨</div>
                    <p class="text-xs text-white/80 font-medium leading-tight">Front Office &amp; Booking</p>
                </div>
                <div class="bg-white/10 backdrop-blur border border-white/15 rounded-2xl p-4 text-center">
                    <div class="text-2xl mb-1">📊</div>
                    <p class="text-xs text-white/80 font-medium leading-tight">Accounting &amp; Laporan</p>
                </div>
                <div class="bg-white/10 backdrop-blur border border-white/15 rounded-2xl p-4 text-center">
                    <div class="text-2xl mb-1">🔗</div>
                    <p class="text-xs text-white/80 font-medium leading-tight">OTA Channel Manager</p>
                </div>
            </div>
        </div>

        {{-- Copyright --}}
        <div class="relative text-indigo-200/50 text-xs">
            &copy; {{ date('Y') }} {{ config('app.name') }} &middot; Powered by Laravel
        </div>
    </div>

    {{-- ═══════════ RIGHT: Login Form ═══════════ --}}
    <div class="flex items-center justify-center p-6 sm:p-8 lg:p-16">
        <div class="w-full max-w-md">

            {{-- Mobile brand (visible lg:hidden) --}}
            <div class="lg:hidden text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="text-left">
                        <span class="font-display font-bold text-xl text-slate-900">{{ config('app.name') }}</span>
                        <p class="text-[10px] text-slate-500 uppercase tracking-[0.15em]">Hotel Management</p>
                    </div>
                </a>
            </div>

            <h1 class="font-display text-4xl font-bold text-slate-900 mb-2">Masuk</h1>
            <p class="text-slate-500 mb-8">
                Belum punya akun?
                <a href="{{ route('saas.signup.show') }}" class="text-indigo-600 font-semibold hover:text-indigo-700 hover:underline transition-colors">Daftar gratis</a>
            </p>

            {{-- Session expired alert --}}
            @if (request('expired') || session('error') && str_contains(session('error'), 'Sesi'))
                <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-4 mb-6 text-sm">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="font-semibold mb-0.5">Sesi Berakhir</p>
                            <p class="text-amber-700">Sesi Anda telah berakhir karena tidak aktif. Silakan login kembali.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Error alert --}}
            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-xl p-4 mb-6 text-sm">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="font-semibold mb-0.5">Login gagal</p>
                            <p class="text-rose-600">{{ $errors->first() }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Session status --}}
            @if (session('status'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl p-4 mb-6 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Login form --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-800 placeholder-slate-400
                                  focus:ring-3 focus:ring-indigo-500/20 focus:border-indigo-500 transition-shadow
                                  hover:border-slate-400"
                           placeholder="nama@hotel.com">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-semibold text-slate-700">Kata Sandi</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700 hover:underline transition-colors">Lupa password?</a>
                        @endif
                    </div>
                    <input type="password" name="password" id="password" required autocomplete="current-password"
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-800 placeholder-slate-400
                                  focus:ring-3 focus:ring-indigo-500/20 focus:border-indigo-500 transition-shadow
                                  hover:border-slate-400"
                           placeholder="••••••••">
                </div>
                <label class="inline-flex items-center gap-2.5 cursor-pointer group">
                    <input type="checkbox" name="remember"
                           class="rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500 shadow-sm
                                  group-hover:border-indigo-400 transition-colors">
                    <span class="text-sm text-slate-600 group-hover:text-slate-800 transition-colors">Ingat saya</span>
                </label>
                <button type="submit"
                        class="w-full bg-gradient-to-br from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700
                               text-white font-bold text-sm rounded-xl px-6 py-3.5
                               shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40
                               hover:-translate-y-0.5 transition-all duration-200
                               focus:outline-none focus:ring-3 focus:ring-indigo-500/40">
                    Masuk ke Dashboard
                </button>
            </form>

            {{-- Divider --}}
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="bg-stone-50 px-4 text-slate-400 font-medium">atau</span>
                </div>
            </div>

            {{-- Demo Login Box --}}
            <div class="bg-stone-50 border border-stone-200 rounded-2xl p-5 text-sm">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-lg">🧪</span>
                    <span class="font-bold text-slate-800">Demo Login</span>
                </div>
                <div class="space-y-1.5 text-stone-600 text-xs font-mono leading-relaxed">
                    <div class="flex justify-between"><span class="font-semibold text-slate-700">Admin:</span> <span>admin@demohotel.id / password123</span></div>
                    <div class="flex justify-between"><span class="font-semibold text-slate-700">Front Office:</span> <span>fo@demohotel.id / password123</span></div>
                    <div class="flex justify-between"><span class="font-semibold text-slate-700">Housekeeping:</span> <span>hk@demohotel.id / password123</span></div>
                    <div class="flex justify-between"><span class="font-semibold text-slate-700">Accounting:</span> <span>acc@demohotel.id / password123</span></div>
                    <div class="flex justify-between"><span class="font-semibold text-slate-700">Manager:</span> <span>manager@demohotel.id / password123</span></div>
                </div>
            </div>

            {{-- Back to home --}}
            <div class="mt-6 text-center">
                <a href="{{ route('home') }}" class="text-sm text-slate-500 hover:text-indigo-600 transition-colors inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
