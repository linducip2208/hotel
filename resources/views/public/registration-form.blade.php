<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Digital — {{ $property->name ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>
        body{font-family:'Inter',system-ui,sans-serif}
        @media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important}}
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

<div class="min-h-screen flex flex-col items-center justify-center px-4 py-10">

    {{-- Header --}}
    <div class="text-center mb-8 max-w-md">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-indigo-500/30">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Registrasi Digital</h1>
        <p class="text-sm text-slate-500 mt-1">{{ $property->name ?? 'Hotel' }}</p>
    </div>

    @if ($reg->status === 'signed')
    <div class="w-full max-w-md bg-emerald-50 border border-emerald-200 rounded-2xl p-6 text-center mb-6">
        <svg class="w-12 h-12 text-emerald-500 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <h3 class="text-lg font-semibold text-emerald-800 mb-1">Registrasi Selesai</h3>
        <p class="text-sm text-emerald-600">Terima kasih! Data Anda sudah tercatat. Silakan lanjut ke proses check-in saat tiba di hotel.</p>
    </div>
    @else
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-lg border border-slate-200 p-6 md:p-8">

        {{-- Guest info --}}
        <div class="bg-indigo-50 rounded-xl p-4 mb-6">
            <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wide">Data Tamu</p>
            <p class="text-sm font-semibold text-slate-900 mt-1">{{ $guest->full_name }}</p>
            @if ($guest->phone)
                <p class="text-xs text-slate-500">{{ $guest->phone }}</p>
            @endif
            @if ($guest->email)
                <p class="text-xs text-slate-500">{{ $guest->email }}</p>
            @endif
        </div>

        <form method="POST" action="{{ route('registration.submit', $reg->token) }}" enctype="multipart/form-data" x-data="{ drawing: false, cleared: true }">
            @csrf

            <div class="space-y-4">

                {{-- Full Name --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name', $guest->full_name) }}" required
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    @error('full_name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Phone / Email row --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">No. Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone', $guest->phone) }}"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email', $guest->email) }}"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>

                {{-- ID Type / ID Number row --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tipe Identitas <span class="text-rose-500">*</span></label>
                        <select name="id_type" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            <option value="KTP" {{ old('id_type') === 'KTP' ? 'selected' : '' }}>KTP</option>
                            <option value="SIM" {{ old('id_type') === 'SIM' ? 'selected' : '' }}>SIM</option>
                            <option value="PASSPORT" {{ old('id_type') === 'PASSPORT' ? 'selected' : '' }}>Paspor</option>
                            <option value="KITAS" {{ old('id_type') === 'KITAS' ? 'selected' : '' }}>KITAS</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nomor Identitas <span class="text-rose-500">*</span></label>
                        <input type="text" name="id_number" value="{{ old('id_number') }}" required
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        @error('id_number') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Nationality / Vehicle row --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kewarganegaraan <span class="text-rose-500">*</span></label>
                        <select name="nationality" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            <option value="Indonesia" {{ old('nationality') === 'Indonesia' ? 'selected' : '' }}>Indonesia</option>
                            @foreach (['Malaysia','Singapore','China','India','Japan','South Korea','Australia','United States','United Kingdom','Other'] as $nat)
                                <option value="{{ $nat }}" {{ old('nationality') === $nat ? 'selected' : '' }}>{{ $nat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Plat Kendaraan</label>
                        <input type="text" name="vehicle_plate" value="{{ old('vehicle_plate') }}" placeholder="Opsional"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>

                {{-- Special Requests --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Permintaan Khusus</label>
                    <textarea name="special_requests" rows="2"
                              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                              placeholder="Extra bed, late check-out, dll.">{{ old('special_requests') }}</textarea>
                </div>

                {{-- Signature Pad --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanda Tangan Digital <span class="text-rose-500">*</span></label>
                    <div class="relative bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl overflow-hidden"
                         @mousedown="drawing=true" @mouseup="drawing=false" @mouseleave="drawing=false"
                         @touchstart.prevent="drawing=true" @touchend="drawing=false">
                        <canvas id="signaturePad"
                                class="w-full h-40 cursor-crosshair"
                                style="touch-action: none;"></canvas>
                        <div class="absolute bottom-2 right-2 flex gap-1.5">
                            <button type="button" onclick="clearSignature()"
                                    class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-xs text-slate-500 hover:bg-slate-100 transition-colors">
                                Hapus
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="signature" id="signatureData" value="">
                    @error('signature') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    <p class="text-[11px] text-slate-400 mt-1">Gambar tanda tangan Anda di atas (mouse atau sentuh)</p>
                </div>

                {{-- ID Document Upload --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Upload Foto Identitas</label>
                    <div class="relative">
                        <input type="file" name="id_document" accept="image/*,.pdf"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Format: JPG, PNG, atau PDF. Maks 5 MB.</p>
                    @error('id_document') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Agreement --}}
                <div class="flex items-start gap-3 pt-2">
                    <input type="checkbox" name="agreement" id="agreement" required
                           class="mt-0.5 w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    <label for="agreement" class="text-xs text-slate-600 leading-relaxed cursor-pointer">
                        Saya menyatakan bahwa data yang diisi adalah benar dan lengkap. Saya setuju dengan
                        <a href="/terms" target="_blank" class="text-indigo-600 font-semibold hover:underline">syarat & ketentuan</a>
                        yang berlaku di {{ $property->name ?? 'hotel ini' }}.
                    </label>
                </div>
                @error('agreement') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror

                {{-- Submit --}}
                <button type="submit"
                        class="w-full bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-semibold text-sm py-3 rounded-xl transition-all shadow-lg shadow-indigo-500/25 mt-4">
                    Kirim Registrasi
                </button>
            </div>
        </form>
    </div>
    @endif

    <p class="text-xs text-slate-400 mt-6 text-center">
        &copy; {{ date('Y') }} {{ $property->name ?? config('app.name') }} &middot; Registrasi Digital
    </p>
</div>

<script>
const canvas = document.getElementById('signaturePad');
const ctx = canvas.getContext('2d');

function resizeCanvas() {
    const rect = canvas.parentElement.getBoundingClientRect();
    const ratio = window.devicePixelRatio || 1;
    canvas.width = rect.width * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    ctx.scale(ratio, ratio);
    ctx.strokeStyle = '#1e293b';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
}
resizeCanvas();
window.addEventListener('resize', resizeCanvas);

let drawing = false;
let hasSignature = false;

function getPos(e) {
    const rect = canvas.getBoundingClientRect();
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
    return { x: clientX - rect.left, y: clientY - rect.top };
}

canvas.addEventListener('mousedown', (e) => { drawing = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); });
canvas.addEventListener('mousemove', (e) => { if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); hasSignature = true; });
canvas.addEventListener('mouseup', () => { if (drawing) { drawing = false; updateSignatureData(); } });
canvas.addEventListener('mouseleave', () => { if (drawing) { drawing = false; updateSignatureData(); } });

canvas.addEventListener('touchstart', (e) => { e.preventDefault(); drawing = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); });
canvas.addEventListener('touchmove', (e) => { e.preventDefault(); if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); hasSignature = true; });
canvas.addEventListener('touchend', (e) => { e.preventDefault(); drawing = false; updateSignatureData(); });

function clearSignature() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    hasSignature = false;
    document.getElementById('signatureData').value = '';
}

function updateSignatureData() {
    document.getElementById('signatureData').value = canvas.toDataURL('image/png');
}

document.querySelector('form').addEventListener('submit', function(e) {
    if (!hasSignature) {
        e.preventDefault();
        alert('Mohon isi tanda tangan terlebih dahulu.');
        return;
    }
    updateSignatureData();
});
</script>
</body>
</html>
