@extends('panel.layout')
@section('title', 'Verifikasi Dua Langkah')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Verifikasi Dua Langkah</h1>
    <p class="text-sm text-slate-500 mt-0.5">Lindungi akun Anda dengan autentikasi dua faktor</p>
</div>

<div class="max-w-xl">
    {{-- Status card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-5">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Status 2FA</h2>
            @if ($twoFactorEnabled)
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Aktif
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                    Tidak Aktif
                </span>
            @endif
        </div>
        <div class="px-5 py-4">
            @if ($twoFactorEnabled)
                <div class="flex items-center gap-3 text-sm text-slate-600 mb-4">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Verifikasi dua langkah <strong>aktif</strong> untuk akun Anda.</span>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    {{-- Generate new recovery codes --}}
                    <a href="{{ route('two-factor.recovery') }}"
                       class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 px-4 rounded-xl transition-colors shadow-sm shadow-indigo-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Generate Kode Pemulihan Baru
                    </a>

                    {{-- Disable button --}}
                    <button type="button"
                            onclick="document.getElementById('disableForm').classList.toggle('hidden')"
                            class="inline-flex items-center justify-center gap-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-sm font-semibold py-2.5 px-4 rounded-xl border border-rose-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        Nonaktifkan 2FA
                    </button>
                </div>

                {{-- Disable confirmation form --}}
                <div id="disableForm" class="hidden mt-4 bg-rose-50 border border-rose-200 rounded-xl p-4">
                    <p class="text-sm text-rose-700 mb-3 font-medium">Konfirmasi: Masukkan password Anda untuk menonaktifkan 2FA.</p>
                    <form method="POST" action="{{ route('two-factor.disable') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-600 mb-1">Password</label>
                            <input type="password" name="password" id="password" required autocomplete="current-password"
                                   class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-800
                                          focus:ring-3 focus:ring-rose-500/20 focus:border-rose-500 outline-none
                                          hover:border-slate-400 transition-shadow"
                                   placeholder="••••••••">
                            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold py-2 px-4 rounded-xl transition-colors shadow-sm shadow-rose-500/20">
                                Nonaktifkan 2FA
                            </button>
                            <button type="button"
                                    onclick="document.getElementById('disableForm').classList.toggle('hidden')"
                                    class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-600 text-sm font-semibold py-2 px-4 rounded-xl border border-slate-200 transition-colors">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="flex items-center gap-3 text-sm text-slate-600 mb-4">
                    <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Verifikasi dua langkah <strong>belum aktif</strong> untuk akun Anda. Aktifkan untuk keamanan tambahan.</span>
                </div>

                <a href="{{ route('two-factor.setup') }}"
                   class="inline-flex items-center gap-2 bg-gradient-to-br from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700
                          text-white font-bold text-sm rounded-xl px-6 py-3
                          shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40
                          hover:-translate-y-0.5 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Aktifkan Verifikasi Dua Langkah
                </a>
            @endif
        </div>
    </div>

    {{-- Info card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-slate-700">Tentang Verifikasi Dua Langkah</h2>
        </div>
        <div class="px-5 py-4 space-y-4 text-sm text-slate-600 leading-relaxed">
            <p>Verifikasi dua langkah menambahkan lapisan keamanan ekstra ke akun Anda. Selain password, Anda akan diminta memasukkan kode dari aplikasi autentikator setiap kali login.</p>
            <div class="grid sm:grid-cols-3 gap-3">
                <div class="bg-slate-50 rounded-xl p-3 text-center">
                    <div class="text-2xl mb-1">📱</div>
                    <p class="text-xs font-semibold text-slate-700 mb-0.5">1. Instal Aplikasi</p>
                    <p class="text-xs text-slate-500">Unduh Google Authenticator, Authy, atau aplikasi TOTP lainnya.</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-3 text-center">
                    <div class="text-2xl mb-1">📷</div>
                    <p class="text-xs font-semibold text-slate-700 mb-0.5">2. Pindai QR Code</p>
                    <p class="text-xs text-slate-500">Scan kode QR yang ditampilkan saat setup menggunakan aplikasi Anda.</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-3 text-center">
                    <div class="text-2xl mb-1">🔑</div>
                    <p class="text-xs font-semibold text-slate-700 mb-0.5">3. Simpan Kode Pemulihan</p>
                    <p class="text-xs text-slate-500">Download atau cetak kode pemulihan untuk akses darurat.</p>
                </div>
            </div>
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                <div class="flex items-start gap-2.5">
                    <svg class="w-5 h-5 text-indigo-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    <div>
                        <p class="font-semibold text-indigo-800 mb-0.5">Rekomendasi aplikasi autentikator</p>
                        <p class="text-indigo-600/80 text-xs">Google Authenticator, Authy, Microsoft Authenticator, Bitwarden, 1Password — semua mendukung TOTP standar.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
