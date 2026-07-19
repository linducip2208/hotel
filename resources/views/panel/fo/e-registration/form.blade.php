<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=5,user-scalable=yes">
    <meta name="theme-color" content="#4f46e5">
    <title>E-Registration — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        canvas#signature-pad { touch-action: none; }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased min-h-screen" x-data="eRegistration()">

{{-- ═══════════════════════════════════════ HEADER --}}
<header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between sticky top-0 z-10 shadow-sm">
    <div>
        <h1 class="text-lg font-bold text-gray-900">E-Registration</h1>
        <p class="text-xs text-gray-500">{{ $reservation->primaryGuest?->full_name }} — {{ $reservation->ref }}</p>
    </div>
    <div class="text-xs text-gray-400">Step <span x-text="step" class="font-semibold text-primary-600"></span> of 4</div>
</header>

{{-- ═══════════════════════════════════════ MAIN FORM --}}
<main class="max-w-lg mx-auto px-4 py-6 pb-24">
    @if ($existingCard && $existingCard->is_verified)
        {{-- Already completed --}}
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-8 text-center">
            <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-lg font-bold text-emerald-800 mb-1">Registration Complete</h2>
            <p class="text-sm text-emerald-600">Your e-registration card has been verified.</p>
        </div>
    @elseif ($existingCard && !$existingCard->is_verified)
        {{-- Submitted but not verified --}}
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center">
            <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-lg font-bold text-amber-800 mb-1">Awaiting Verification</h2>
            <p class="text-sm text-amber-600">Your registration has been submitted. Staff will verify shortly.</p>
        </div>
    @else
        {{-- ═══ PROGRESS BAR --}}
        <div class="flex items-center gap-2 mb-8">
            <template x-for="s in [1,2,3,4]" :key="s">
                <div class="flex-1 h-1.5 rounded-full transition-colors duration-300"
                     :class="s <= step ? 'bg-primary-500' : 'bg-gray-200'"></div>
            </template>
        </div>

        <form method="POST" action="{{ route('panel.fo.e-registration.store', $reservation->id) }}" @submit.prevent="submitForm()"
              class="bg-white rounded-2xl shadow-card border border-gray-100">

            {{-- ═══ STEP 1: Guest Info --}}
            <div x-show="step === 1" class="p-6 space-y-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Guest Information</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Please verify your details below</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name (as per ID)</label>
                    <input type="text" x-model="form.full_name" required
                           class="w-full text-base border border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">ID Type</label>
                    <select x-model="form.id_type" required
                            class="w-full text-base border border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Select ID type...</option>
                        <option value="KTP">KTP</option>
                        <option value="PASSPORT">Paspor</option>
                        <option value="SIM">SIM</option>
                        <option value="KITAS">KITAS</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">ID Number</label>
                    <input type="text" x-model="form.id_number" required
                           class="w-full text-base border border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nationality</label>
                        <input type="text" x-model="form.nationality" required
                               class="w-full text-base border border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Date of Birth</label>
                        <input type="date" x-model="form.date_of_birth" required
                               class="w-full text-base border border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
                    <textarea x-model="form.address" rows="2"
                              class="w-full text-base border border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 resize-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                        <input type="tel" x-model="form.phone"
                               class="w-full text-base border border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                        <input type="email" x-model="form.email"
                               class="w-full text-base border border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
            </div>

            {{-- ═══ STEP 2: Stay Details --}}
            <div x-show="step === 2" class="p-6 space-y-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Stay Details</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Travel information</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Purpose of Stay</label>
                    <select x-model="form.purpose_of_stay"
                            class="w-full text-base border border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Select...</option>
                        <option value="business">Business</option>
                        <option value="leisure">Leisure</option>
                        <option value="event">Event / Conference</option>
                        <option value="transit">Transit</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Vehicle Plate Number</label>
                    <input type="text" x-model="form.vehicle_plate" placeholder="e.g. B 1234 ABC"
                           class="w-full text-base border border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Next Destination</label>
                    <input type="text" x-model="form.next_destination" placeholder="e.g. Jakarta, Bali..."
                           class="w-full text-base border border-gray-200 rounded-xl px-4 py-3 bg-white text-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            {{-- ═══ STEP 3: Signature --}}
            <div x-show="step === 3" class="p-6 space-y-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Signature</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Sign using your finger or stylus in the box below</p>
                </div>

                <div class="bg-white rounded-xl border-2 border-dashed border-gray-300 overflow-hidden">
                    <canvas id="signature-pad"
                            class="w-full block"
                            style="height: 200px; cursor: crosshair;"
                            @touchstart.prevent="startSignature($event)"
                            @touchmove.prevent="drawSignature($event)"
                            @touchend.prevent="endSignature()"
                            @mousedown.prevent="startSignature($event)"
                            @mousemove.prevent="drawSignature($event)"
                            @mouseup.prevent="endSignature()"
                            @mouseleave="endSignature()"></canvas>
                </div>

                <button type="button" @click="clearSignature()"
                        class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-red-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Clear Signature
                </button>
            </div>

            {{-- ═══ STEP 4: Review --}}
            <div x-show="step === 4" class="p-6 space-y-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Review & Confirm</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Please review the information before submitting</p>
                </div>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <dt class="text-gray-500">Full Name</dt>
                        <dd class="font-medium text-gray-900" x-text="form.full_name"></dd>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <dt class="text-gray-500">ID</dt>
                        <dd class="font-medium text-gray-900" x-text="form.id_type + ' — ' + form.id_number"></dd>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <dt class="text-gray-500">Nationality</dt>
                        <dd class="font-medium text-gray-900" x-text="form.nationality"></dd>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <dt class="text-gray-500">Date of Birth</dt>
                        <dd class="font-medium text-gray-900" x-text="form.date_of_birth"></dd>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <dt class="text-gray-500">Purpose</dt>
                        <dd class="font-medium text-gray-900" x-text="form.purpose_of_stay || '—'"></dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-gray-500">Signature</dt>
                        <dd class="font-medium text-gray-900">
                            <template x-if="form.signature_image">
                                <span class="text-emerald-600">Signed</span>
                            </template>
                            <template x-if="!form.signature_image">
                                <span class="text-red-500">Not signed</span>
                            </template>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- ═══ NAVIGATION BUTTONS --}}
            <div class="px-6 py-5 border-t border-gray-100 flex items-center gap-3">
                <button type="button" x-show="step > 1" @click="step--"
                        class="flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium px-5 py-3 rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back
                </button>

                <div class="flex-1"></div>

                <button type="button" x-show="step < 4" @click="nextStep()"
                        class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-6 py-3 rounded-xl transition shadow-sm">
                    Next
                    <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>

                <button type="submit" x-show="step === 4"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-6 py-3 rounded-xl transition shadow-sm">
                    Submit Registration
                </button>
            </div>
        </form>
    @endif
</main>

{{-- ═══════════════════════════════════════ ALPINE.JS --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('eRegistration', () => ({
        step: 1,
        drawing: false,
        canvas: null,
        ctx: null,

        form: {
            full_name: '{{ old('full_name', $reservation->primaryGuest?->full_name ?? '') }}',
            id_type: '{{ old('id_type', '') }}',
            id_number: '{{ old('id_number', '') }}',
            nationality: '{{ old('nationality', $reservation->primaryGuest?->country ?? '') }}',
            date_of_birth: '{{ old('date_of_birth', $reservation->primaryGuest?->date_of_birth?->toDateString() ?? '') }}',
            address: '{{ old('address', '') }}',
            phone: '{{ old('phone', $reservation->primaryGuest?->phone ?? '') }}',
            email: '{{ old('email', $reservation->primaryGuest?->email ?? '') }}',
            vehicle_plate: '{{ old('vehicle_plate', '') }}',
            purpose_of_stay: '{{ old('purpose_of_stay', '') }}',
            next_destination: '{{ old('next_destination', '') }}',
            signature_image: null,
        },

        init() {
            this.$nextTick(() => {
                this.canvas = document.getElementById('signature-pad');
                if (this.canvas) {
                    this.ctx = this.canvas.getContext('2d');
                    this.resizeCanvas();
                    window.addEventListener('resize', () => this.resizeCanvas());
                }
            });
        },

        resizeCanvas() {
            if (!this.canvas) return;
            const rect = this.canvas.getBoundingClientRect();
            this.canvas.width = rect.width * (window.devicePixelRatio || 1);
            this.canvas.height = 200 * (window.devicePixelRatio || 1);
            this.ctx = this.canvas.getContext('2d');
            this.ctx.scale(window.devicePixelRatio || 1, window.devicePixelRatio || 1);
            this.ctx.strokeStyle = '#000';
            this.ctx.lineWidth = 3;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
        },

        getPos(e) {
            const rect = this.canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return { x: clientX - rect.left, y: clientY - rect.top };
        },

        startSignature(e) {
            this.drawing = true;
            const pos = this.getPos(e);
            this.ctx.beginPath();
            this.ctx.moveTo(pos.x, pos.y);
        },

        drawSignature(e) {
            if (!this.drawing) return;
            const pos = this.getPos(e);
            this.ctx.lineTo(pos.x, pos.y);
            this.ctx.stroke();
        },

        endSignature() {
            if (!this.drawing) return;
            this.drawing = false;
            this.ctx.closePath();
            this.form.signature_image = this.canvas.toDataURL('image/png');
        },

        clearSignature() {
            if (!this.ctx || !this.canvas) return;
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            this.form.signature_image = null;
        },

        nextStep() {
            if (this.step < 4) this.step++;
        },

        submitForm() {
            const payload = { ...this.form };
            const formEl = document.createElement('form');
            formEl.method = 'POST';
            formEl.action = '{{ route('panel.fo.e-registration.store', $reservation->id) }}';
            formEl.style.display = 'none';

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            formEl.appendChild(csrf);

            for (const [key, value] of Object.entries(payload)) {
                if (value === null || value === undefined) continue;
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value === 'true' ? '1' : (value === 'false' ? '0' : value);
                formEl.appendChild(input);
            }

            document.body.appendChild(formEl);
            formEl.submit();
        },
    }));
});
</script>

</body>
</html>
