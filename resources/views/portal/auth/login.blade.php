<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — Portal Tamu {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:600,700|inter:300,400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .font-display { font-family: 'Playfair Display', Georgia, serif; }
    </style>
</head>
<body class="bg-stone-50 text-slate-800 antialiased">

<div class="min-h-screen grid lg:grid-cols-2">

    <div class="hidden lg:flex relative bg-gradient-to-br from-indigo-600 via-indigo-700 to-slate-900 p-12 flex-col justify-between overflow-hidden">
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 30% 40%, rgba(255,255,255,.15) 0%, transparent 50%), radial-gradient(circle at 70% 80%, rgba(255,255,255,.08) 0%, transparent 50%)"></div>
        <div class="absolute -bottom-20 -right-20 text-[18rem] opacity-10">🏨</div>

        <div class="relative">
            <a href="/" class="flex items-center gap-2.5 text-white">
                <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <span class="font-display font-bold text-2xl">{{ config('app.name') }}</span>
            </a>
        </div>

        <div class="relative text-white">
            <h2 class="font-display text-4xl font-bold leading-tight mb-3">Portal Tamu</h2>
            <p class="text-indigo-200 text-lg leading-relaxed mb-10 max-w-sm">Akses pemesanan, tagihan, dan layanan hotel Anda — semua dalam satu tempat.</p>
            <div class="grid grid-cols-3 gap-4 max-w-sm">
                <div class="bg-white/10 backdrop-blur rounded-xl p-4 text-center">
                    <svg class="w-7 h-7 mx-auto mb-2 text-indigo-200" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-xs font-semibold text-indigo-100">Pemesanan</span>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl p-4 text-center">
                    <svg class="w-7 h-7 mx-auto mb-2 text-indigo-200" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-xs font-semibold text-indigo-100">Tagihan</span>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl p-4 text-center">
                    <svg class="w-7 h-7 mx-auto mb-2 text-indigo-200" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <span class="text-xs font-semibold text-indigo-100">Pembayaran</span>
                </div>
            </div>
        </div>

        <div class="relative text-indigo-300/60 text-xs">&copy; {{ date('Y') }} {{ config('app.name') }} &middot; Portal Tamu</div>
    </div>

    <div class="flex items-center justify-center p-6 lg:p-16">
        <div class="w-full max-w-md">
            <div class="lg:hidden mb-8 text-center">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center mx-auto mb-3 shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <span class="font-display font-bold text-xl text-slate-900">{{ config('app.name') }}</span>
            </div>

            <h1 class="font-display text-3xl font-bold text-slate-900 mb-1">Masuk</h1>
            <p class="text-slate-500 mb-8">Portal tamu — akses pemesanan & tagihan Anda.</p>

            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('customer.login.submit') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow"
                           placeholder="nama@email.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
                    <input type="password" name="password" id="password" required
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow"
                           placeholder="Masukkan password">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Ingat saya
                    </label>
                    <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">Lupa password?</a>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-semibold py-3 rounded-xl shadow-lg shadow-indigo-500/25 transition-all hover:shadow-xl hover:shadow-indigo-500/30">
                    Masuk
                </button>
            </form>

            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                <div class="relative flex justify-center text-sm"><span class="bg-stone-50 px-3 text-slate-400">atau</span></div>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm">
                <p class="font-semibold text-slate-800 mb-2">🧪 Demo Login</p>
                <div class="space-y-1 text-slate-600 text-xs font-mono">
                    <div><span class="font-bold">Tamu:</span> demo@tamu.test / password</div>
                </div>
                <p class="text-slate-400 text-xs mt-3 leading-relaxed">Belum punya akun? Akun dibuat otomatis saat Anda melakukan reservasi. Gunakan email yang didaftarkan saat booking.</p>
            </div>

            <p class="mt-6 text-center text-sm text-slate-400">
                <a href="/" class="text-indigo-600 hover:text-indigo-700 font-medium transition-colors">&larr; Kembali ke Beranda</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>
