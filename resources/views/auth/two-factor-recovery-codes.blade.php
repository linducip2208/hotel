<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kode Pemulihan — {{ config('app.name') }}</title>
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

<main class="w-full max-w-lg mx-auto animate-fade-slide"
       x-data="{ copiedAll: false }">
    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-br from-indigo-600 to-violet-600 px-8 py-8 text-center text-white">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h1 class="font-display text-2xl font-bold tracking-tight">Kode Pemulihan</h1>
            <p class="text-indigo-100/70 text-sm mt-1.5">Simpan kode ini untuk akses darurat</p>
        </div>

        {{-- Body --}}
        <div class="px-8 py-6 space-y-5">
            @if (session('status'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl p-4 text-sm">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            {{-- Warning banner --}}
            <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-sm">
                <div class="flex items-start gap-2.5">
                    <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                    <div>
                        <p class="font-semibold text-rose-800 mb-0.5">Simpan kode ini di tempat aman!</p>
                        <p class="text-rose-600/90">Kode hanya muncul <strong>sekali</strong>. Setiap kode hanya bisa digunakan satu kali. Jika Anda kehilangan akses ke aplikasi autentikator, kode ini adalah satu-satunya cara masuk.</p>
                    </div>
                </div>
            </div>

            {{-- Recovery codes grid --}}
            <div class="grid grid-cols-2 gap-2.5">
                @foreach ($codes as $i => $code)
                    <div x-data="{ copied{{ $i }}: false }"
                         class="group relative bg-slate-50 border border-slate-200 hover:border-indigo-300 rounded-xl transition-colors cursor-pointer">
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-sm font-mono text-slate-800 font-medium select-all tracking-wide">{{ $code }}</span>
                            <button
                                type="button"
                                @@click.stop="navigator.clipboard.writeText('{{ $code }}'); copied{{ $i }} = true; setTimeout(() => copied{{ $i }} = false, 2000)"
                                class="shrink-0 ml-2 opacity-0 group-hover:opacity-100 transition-opacity"
                                title="Salin kode"
                            >
                                <svg x-show="!copied{{ $i }}" class="w-4 h-4 text-slate-400 hover:text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                <svg x-show="copied{{ $i }}" class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Action buttons --}}
            <div class="flex gap-3">
                <button
                    type="button"
                    @@click="
                        const codes = @js($codes);
                        navigator.clipboard.writeText(codes.join('\n'));
                        copiedAll = true;
                        setTimeout(() => copiedAll = false, 2500)
                    "
                    class="flex-1 inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 px-4 rounded-xl transition-colors shadow-sm shadow-indigo-500/20"
                >
                    <svg x-show="!copiedAll" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                    <svg x-show="copiedAll" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span x-text="copiedAll ? 'Disalin!' : 'Salin Semua Kode'"></span>
                </button>
                <button
                    type="button"
                    onclick="downloadRecoveryCodes()"
                    class="flex-1 inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold py-2.5 px-4 rounded-xl transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download TXT
                </button>
                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold py-2.5 px-4 rounded-xl transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak
                </button>
            </div>

            {{-- Done button --}}
            <a href="{{ route('panel.dashboard') }}"
               class="block w-full text-center bg-gradient-to-br from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700
                      text-white font-bold text-sm rounded-xl px-6 py-3.5
                      shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40
                      hover:-translate-y-0.5 transition-all duration-200">
                Saya sudah menyimpan kode — Lanjut ke Dashboard
            </a>
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

<script>
    function downloadRecoveryCodes() {
        const codes = @json($codes);
        const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
        const content = 'Kode Pemulihan — {{ config('app.name') }}\n\n' +
            codes.map((c, i) => (i + 1) + '. ' + c).join('\n') +
            '\n\nTanggal: ' + now + '\n' +
            'Setiap kode hanya bisa digunakan satu kali.\n' +
            'Simpan file ini di tempat aman.\n';

        const blob = new Blob([content], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'kode-pemulihan-{{ config('app.name') }}-{{ now()->format("Y-m-d") }}.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
</script>

</body>
</html>
