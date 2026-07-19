<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aktifkan Verifikasi Dua Langkah — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:600,700,900|inter:300,400,500,600,700,800|jetbrains-mono:400,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; background: #f5f5f4; }
        .font-display { font-family: 'Playfair Display', Georgia, serif; }
        @keyframes fadeSlideUp { 0%{transform:translateY(24px);opacity:0} 100%{transform:translateY(0);opacity:1} }
        .animate-fade-slide { animation: fadeSlideUp .7s cubic-bezier(.16,1,.3,1) both; }
        .delay-100 { animation-delay: .1s; }
        .delay-200 { animation-delay: .2s; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full antialiased flex items-center justify-center py-8 px-4">

<main class="w-full max-w-lg mx-auto animate-fade-slide">
    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-br from-indigo-600 to-violet-600 px-8 py-8 text-center text-white">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="font-display text-2xl font-bold tracking-tight">Aktifkan Verifikasi Dua Langkah</h1>
            <p class="text-indigo-100/70 text-sm mt-1.5">Lindungi akun Anda dengan autentikasi dua faktor</p>
        </div>

        {{-- Body --}}
        <div class="px-8 py-6 space-y-6">

            @if (session('status'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl p-4 text-sm">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-xl p-4 text-sm">
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

            {{-- Step 1: QR Code --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">1</span>
                    <span class="text-sm font-semibold text-slate-800">Pindai QR Code</span>
                </div>
                @if (!empty($qrCodeUrl))
                <div class="flex justify-center">
                    <div class="border-2 border-slate-200 rounded-xl p-4 bg-slate-50">
                        {!! QrCode::size(220)->generate($qrCodeUrl) !!}
                    </div>
                </div>
                <p class="text-xs text-slate-400 text-center mt-2">Buka aplikasi autentikator (Google Authenticator, Authy, dll.) lalu pindai kode QR ini</p>
                @else
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-amber-700 text-sm text-center">
                    Tidak dapat membuat QR code. Gunakan kunci manual di bawah ini.
                </div>
                @endif
            </div>

            {{-- Step 2: Manual Setup Key --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">2</span>
                    <span class="text-sm font-semibold text-slate-800">Kunci Manual</span>
                </div>
                <div x-data="{ copied: false }" class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Setup Key</span>
                        <button
                            type="button"
                            @@click="navigator.clipboard.writeText('{{ $secret }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="text-xs font-medium text-indigo-600 hover:text-indigo-700 transition-colors inline-flex items-center gap-1"
                        >
                            <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                            <svg x-show="copied" class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="copied ? 'Disalin!' : 'Salin'"></span>
                        </button>
                    </div>
                    <p class="text-sm font-mono text-slate-800 break-all tracking-wider select-all leading-relaxed">{{ $secret }}</p>
                    <p class="text-[11px] text-slate-400 mt-2">Gunakan kunci ini jika tidak bisa memindai QR code. Paste ke aplikasi autentikator Anda.</p>
                </div>
            </div>

            {{-- Step 3: Verify Setup --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">3</span>
                    <span class="text-sm font-semibold text-slate-800">Verifikasi</span>
                </div>
                <form method="POST" action="{{ route('two-factor.enable') }}" x-data="{ code: '' }">
                    @csrf
                    <label for="code" class="block text-sm font-medium text-slate-600 mb-1.5">Masukkan kode 6 digit dari aplikasi Anda</label>
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
                        class="w-full border border-slate-300 rounded-xl px-4 py-3.5 text-center text-2xl font-mono tracking-[0.3em] text-slate-800
                               focus:ring-3 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none
                               hover:border-slate-400 transition-shadow placeholder:text-slate-300"
                    >
                    <button type="submit"
                            class="mt-4 w-full bg-gradient-to-br from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700
                                   text-white font-bold text-sm rounded-xl px-6 py-3
                                   shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40
                                   hover:-translate-y-0.5 transition-all duration-200
                                   focus:outline-none focus:ring-3 focus:ring-indigo-500/40">
                        Aktifkan Verifikasi Dua Langkah
                    </button>
                </form>
            </div>

            {{-- Warning --}}
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800 flex items-start gap-2.5">
                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                <div>
                    <p class="font-semibold mb-0.5">Peringatan</p>
                    <p class="text-amber-700">Simpan kode pemulihan di tempat aman setelah mengaktifkan 2FA. Anda akan membutuhkannya jika kehilangan akses ke aplikasi autentikator.</p>
                </div>
            </div>

        </div>
    </div>

    {{-- Back link --}}
    <div class="text-center mt-5">
        <a href="{{ route('panel.dashboard') }}" class="text-sm text-slate-500 hover:text-indigo-600 transition-colors inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Dashboard
        </a>
    </div>
</main>

</body>
</html>
