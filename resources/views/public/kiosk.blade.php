<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <title>Self Check-In — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        body { touch-action: manipulation; -webkit-user-select: none; user-select: none; }
        .kiosk-btn { min-height: 60px; min-width: 60px; font-size: 1.25rem; }
        .numpad-btn { width: 80px; height: 80px; font-size: 1.5rem; border-radius: 16px; }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-navy-800 to-navy-950 text-white font-sans antialiased"
      x-data="kiosk()">

<div class="flex flex-col items-center justify-center min-h-full p-6">
    {{-- Step 1: Welcome --}}
    <div x-show="step === 0" class="text-center max-w-lg">
        <div class="w-24 h-24 rounded-full bg-white/10 flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
        <h1 class="text-3xl font-bold mb-3">Welcome!</h1>
        <p class="text-white/60 text-lg mb-8">Tap to begin self check-in</p>
        <button @click="step = 1" class="kiosk-btn bg-primary-600 hover:bg-primary-700 text-white font-bold px-12 py-5 rounded-2xl shadow-2xl transition-colors">
            Start Check-In
        </button>
        <p class="text-white/30 text-sm mt-8">Admin: 5-finger tap for staff mode</p>
    </div>

    {{-- Step 2: Search --}}
    <div x-show="step === 1" class="text-center max-w-md w-full">
        <h2 class="text-2xl font-bold mb-8">Enter Reservation Reference</h2>
        <div class="bg-white/10 rounded-2xl p-4 mb-4">
            <input type="text" x-model="ref" @keydown.enter="lookup()" placeholder="e.g. RES-20250101-ABC"
                   class="w-full bg-transparent border-none text-white text-2xl text-center placeholder-white/30 outline-none py-4">
        </div>

        {{-- Numpad --}}
        <div class="grid grid-cols-3 gap-3 mb-6">
            <template x-for="n in [1,2,3,4,5,6,7,8,9,'ABC',0,'DEF']" :key="n">
                <button @click="appendKey(n)" class="numpad-btn bg-white/10 hover:bg-white/20 font-bold flex items-center justify-center transition-colors" x-text="n"></button>
            </template>
        </div>

        <div class="flex gap-3">
            <button @click="ref = ref.slice(0,-1)" class="kiosk-btn flex-1 bg-white/10 hover:bg-white/20 font-semibold rounded-2xl transition-colors">⌫</button>
            <button @click="lookup()" :disabled="!ref" class="kiosk-btn flex-[2] bg-primary-600 hover:bg-primary-700 disabled:opacity-30 font-bold rounded-2xl transition-colors">Search</button>
        </div>
        <button @click="step = 0" class="text-white/40 text-sm mt-6 hover:text-white/60">← Back</button>
    </div>

    {{-- Step 3: Verify --}}
    <div x-show="step === 2" class="text-center max-w-lg w-full">
        <h2 class="text-2xl font-bold mb-6">Verify Your Stay</h2>
        <div class="bg-white/10 rounded-2xl p-6 mb-6 text-left space-y-3">
            <div class="flex justify-between"><span class="text-white/50">Guest</span><span class="font-semibold" x-text="result?.guest_name"></span></div>
            <div class="flex justify-between"><span class="text-white/50">Check-in</span><span class="font-semibold" x-text="result?.check_in"></span></div>
            <div class="flex justify-between"><span class="text-white/50">Check-out</span><span class="font-semibold" x-text="result?.check_out"></span></div>
            <div class="flex justify-between"><span class="text-white/50">Room</span><span class="font-semibold" x-text="result?.rooms?.join(', ')"></span></div>
        </div>
        <div class="flex gap-4">
            <button @click="doCheckin()" :disabled="loading" class="kiosk-btn flex-1 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-30 font-bold rounded-2xl transition-colors">
                <span x-show="!loading">Confirm Check-In</span>
                <span x-show="loading">Processing...</span>
            </button>
            <button @click="step = 1" class="kiosk-btn bg-white/10 hover:bg-white/20 font-semibold px-8 rounded-2xl transition-colors">← Back</button>
        </div>
    </div>

    {{-- Step 4: Done --}}
    <div x-show="step === 3" class="text-center max-w-lg">
        <div class="w-24 h-24 rounded-full bg-emerald-500/20 flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h2 class="text-3xl font-bold mb-3">Check-In Complete!</h2>
        <p class="text-white/60 text-lg mb-8">Enjoy your stay!</p>
        <a :href="receiptUrl" target="_blank" class="kiosk-btn inline-flex items-center bg-white/10 hover:bg-white/20 font-semibold px-10 py-5 rounded-2xl transition-colors mb-4">
            Print Receipt
        </a>
        <br>
        <button @click="reset()" class="text-white/40 text-sm mt-4 hover:text-white/60">New Check-In</button>
    </div>

    {{-- Error --}}
    <div x-show="error" class="text-center">
        <p class="text-red-300 text-xl mb-4" x-text="error"></p>
        <button @click="error=null; step=1" class="kiosk-btn bg-white/10 hover:bg-white/20 font-semibold px-10 py-5 rounded-2xl transition-colors">Try Again</button>
    </div>
</div>

<script>
function kiosk() {
    return {
        step: 0, ref: '', result: null, loading: false, error: null, receiptUrl: '',
        appendKey(key) { this.ref += key; },
        async lookup() {
            if (!this.ref.trim()) return;
            this.loading = true;
            try {
                const fd = new FormData(); fd.append('ref', this.ref.trim());
                const res = await fetch('/kiosk/lookup', { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                const data = await res.json();
                if (data.found) { this.result = data.reservation; this.step = 2; this.error = null; }
                else { this.error = data.message; }
            } catch(e) { this.error = 'Network error. Please try again.'; }
            this.loading = false;
        },
        async doCheckin() {
            this.loading = true;
            try {
                const fd = new FormData(); fd.append('reservation_id', this.result.id);
                const res = await fetch('/kiosk/checkin', { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                const data = await res.json();
                if (data.ok) { this.step = 3; this.receiptUrl = '/kiosk/print/' + this.result.id; }
                else { this.error = data.message; }
            } catch(e) { this.error = 'Check-in failed. Please see front desk.'; }
            this.loading = false;
        },
        reset() { this.step = 0; this.ref = ''; this.result = null; this.error = null; },
    };
}
</script>

</body>
</html>
