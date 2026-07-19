<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Dua Langkah — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:600,700,900|inter:300,400,500,600,700,800|jetbrains-mono:400,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; background: #f5f5f4; }
        .font-display { font-family: 'Playfair Display', Georgia, serif; }
        @keyframes fadeSlideUp { 0%{transform:translateY(24px);opacity:0} 100%{transform:translateY(0);opacity:1} }
        .animate-fade-slide { animation: fadeSlideUp .7s cubic-bezier(.16,1,.3,1) both; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full antialiased flex items-center justify-center py-8 px-4">

<main class="w-full max-w-md mx-auto animate-fade-slide">
    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden"
         x-data="{ mode: 'totp', code: '', recoveryCode: '' }">
        {{-- Header --}}
        <div class="bg-gradient-to-br from-indigo-600 to-violet-600 px-8 py-8 text-center text-white">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="font-display text-2xl font-bold tracking-tight">Verifikasi Dua Langkah</h1>
            <p class="text-indigo-100/70 text-sm mt-1.5" x-show="mode === 'totp'">Masukkan kode dari aplikasi autentikator Anda</p>
            <p class="text-indigo-100/70 text-sm mt-1.5" x-show="mode === 'recovery'" x-cloak>Masukkan kode pemulihan Anda</p>
        </div>

        {{-- Tab switcher --}}
        <div class="flex border-b border-slate-100">
            <button
                type="button"
                @@click="mode = 'totp'"
                :class="mode === 'totp' ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-slate-400 hover:text-slate-600'"
                class="flex-1 px-6 py-3 text-sm border-b-2 transition-colors duration-200"
            >
                Kode Autentikator
            </button>
            <button
                type="button"
                @@click="mode = 'recovery'"
                :class="mode === 'recovery' ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-slate-400 hover:text-slate-600'"
                class="flex-1 px-6 py-3 text-sm border-b-2 transition-colors duration-200"
            >
                Kode Pemulihan
            </button>
        </div>

        {{-- Body --}}
        <div class="px-8 py-6">
            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-xl p-4 mb-6 text-sm">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- TOTP form --}}
            <div x-show="mode === 'totp'">
                <form method="POST" action="{{ route('two-factor.challenge') }}">
                    @csrf
                    <label for="code" class="block text-sm font-medium text-slate-600 mb-2">Kode 6 Digit</label>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        required
                        maxlength="6"
                        pattern="\d{6}"
                        placeholder="000000"
                        x-model="code"
                        autofocus
                        class="w-full border border-slate-300 rounded-xl px-4 py-3.5 text-center text-2xl font-mono tracking-[0.3em] text-slate-800
                               focus:ring-3 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none
                               hover:border-slate-400 transition-shadow placeholder:text-slate-300"
                    >
                    <button type="submit"
                            class="mt-5 w-full bg-gradient-to-br from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700
                                   text-white font-bold text-sm rounded-xl px-6 py-3
                                   shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40
                                   hover:-translate-y-0.5 transition-all duration-200
                                   focus:outline-none focus:ring-3 focus:ring-indigo-500/40">
                        Verifikasi
                    </button>
                </form>
            </div>

            {{-- Recovery code form --}}
            <div x-show="mode === 'recovery'" x-cloak>
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800 mb-5">
                    <p>Setiap kode pemulihan hanya bisa digunakan <strong>sekali</strong>. Setelah digunakan, kode tersebut akan hangus.</p>
                </div>
                <form method="POST" action="{{ route('two-factor.recovery.verify') }}">
                    @csrf
                    <label for="recovery_code" class="block text-sm font-medium text-slate-600 mb-2">Kode Pemulihan</label>
                    <input
                        type="text"
                        id="recovery_code"
                        name="recovery_code"
                        required
                        placeholder="XXXXX-XXXXX"
                        x-model="recoveryCode"
                        autofocus
                        class="w-full border border-slate-300 rounded-xl px-4 py-3.5 text-center font-mono text-slate-800
                               focus:ring-3 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none
                               hover:border-slate-400 transition-shadow placeholder:text-slate-300"
                    >
                    <p class="text-xs text-slate-400 mt-1.5">Masukkan salah satu kode pemulihan yang belum digunakan</p>
                    <button type="submit"
                            class="mt-5 w-full bg-gradient-to-br from-slate-700 to-slate-800 hover:from-slate-800 hover:to-slate-900
                                   text-white font-bold text-sm rounded-xl px-6 py-3
                                   shadow-lg shadow-slate-500/20 hover:shadow-xl hover:shadow-slate-500/30
                                   hover:-translate-y-0.5 transition-all duration-200
                                   focus:outline-none focus:ring-3 focus:ring-slate-500/30">
                        Gunakan Kode Pemulihan
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Back to login --}}
    <div class="text-center mt-5">
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-sm text-slate-500 hover:text-indigo-600 transition-colors inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Kembali ke Login
            </button>
        </form>
    </div>
</main>

</body>
</html>
