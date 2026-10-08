@extends('public.layout')
@section('title', 'Checkout')
@section('content')
<div x-data="checkoutForm()">
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <h1 class="text-2xl font-bold mb-4">Selesaikan Pemesanan</h1>

        @if ($errors->any())
            <div class="bg-red-50 text-red-800 border border-red-200 p-3 rounded mb-4">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('booking.submit') }}" x-on:submit="submitting = true" class="bg-white border rounded p-4 space-y-6">
            @csrf

            {{-- Stay (carried from selection) --}}
            <input type="hidden" name="check_in" value="{{ $data['check_in'] }}">
            <input type="hidden" name="check_out" value="{{ $data['check_out'] }}">
            <input type="hidden" name="room_type_id" value="{{ $data['room_type_id'] }}">
            <input type="hidden" name="rate_plan_id" value="{{ $data['rate_plan_id'] }}">
            <input type="hidden" name="promo_code" value="{{ $data['promo_code'] ?? '' }}">
            <input type="hidden" name="adults" value="{{ $data['adults'] }}">
            <input type="hidden" name="children" value="{{ $data['children'] ?? 0 }}">

            <section>
                <h2 class="font-semibold mb-3">1. Data Tamu</h2>
                <div class="grid md:grid-cols-2 gap-3">
                    <div>
                        <label for="first_name" class="block text-sm font-medium mb-1">Nama Depan <span class="text-red-500">*</span></label>
                        <input id="first_name" type="text" name="first_name" required maxlength="100" value="{{ old('first_name') }}" class="w-full border rounded p-2 @error('first_name') border-red-400 @enderror" x-on:focusout.debounce.500ms="trackEmail()">
                        @error('first_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium mb-1">Nama Belakang</label>
                        <input id="last_name" type="text" name="last_name" maxlength="100" value="{{ old('last_name') }}" class="w-full border rounded p-2">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium mb-1">Email <span class="text-red-500">*</span></label>
                        <input id="email" type="email" name="email" required maxlength="255" value="{{ old('email') }}" class="w-full border rounded p-2 @error('email') border-red-400 @enderror" x-on:focusout="trackEmail()">
                        @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium mb-1">No. HP / WhatsApp <span class="text-red-500">*</span></label>
                        <input id="phone" type="tel" name="phone" required maxlength="30" value="{{ old('phone') }}" class="w-full border rounded p-2 @error('phone') border-red-400 @enderror">
                        @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section>
                <h2 class="font-semibold mb-3">2. Metode Pembayaran</h2>
                @if ($paymentMethodGroups->isEmpty())
                    <div class="bg-yellow-50 border border-yellow-200 rounded p-3 text-sm text-yellow-800">
                        Pembayaran online sedang tidak tersedia. Silakan hubungi reservasi kami untuk konfirmasi manual.
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($paymentMethodGroups as $group => $methods)
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">{{ $group }}</p>
                                <div class="grid sm:grid-cols-2 gap-2">
                                    @foreach ($methods as $m)
                                        <label class="flex items-center gap-3 border rounded p-3 cursor-pointer hover:border-primary-400 has-[:checked]:border-primary-600 has-[:checked]:bg-primary-50 transition-colors">
                                            <input type="radio" name="payment_method" value="{{ $m['id'] }}" required
                                                   x-on:change="methodChosen = true" {{ old('payment_method') === $m['id'] ? 'checked' : '' }}>
                                            <span class="text-sm font-medium">{{ $m['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('payment_method') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
                @endif
            </section>

            <section>
                <h2 class="font-semibold mb-3">3. Permintaan Khusus (opsional)</h2>
                <textarea name="special_requests" rows="3" maxlength="1000" placeholder="Contoh: kamar lantai tinggi, late check-out" class="w-full border rounded p-2">{{ old('special_requests') }}</textarea>
            </section>

            <section>
                <label class="flex items-start gap-2 text-sm">
                    <input type="checkbox" name="agree_policy" value="1" required class="mt-1" x-on:change="agreed = $event.target.checked">
                    <span>Saya menyetujui syarat &amp; ketentuan serta kebijakan pembatalan <span class="text-red-500">*</span></span>
                </label>
                @error('agree_policy') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </section>

            <div>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-6 py-3 rounded font-medium transition-colors"
                        x-bind:disabled="submitting">
                    <span x-show="!submitting">Lanjut ke Pembayaran</span>
                    <span x-show="submitting" x-cloak>Memproses…</span>
                </button>
                <p class="text-xs text-gray-500 mt-2">Anda akan diarahkan ke halaman pembayaran yang aman.</p>
            </div>
        </form>
    </div>

    {{-- Price breakdown --}}
    <aside class="lg:col-span-1">
        <div class="bg-white border rounded p-4 lg:sticky lg:top-4">
            <h2 class="font-semibold mb-3">Ringkasan Harga</h2>
            <div class="text-sm space-y-2">
                <div class="flex justify-between text-gray-600">
                    <span>{{ $roomType->name }}</span>
                    <span>{{ $plan['name'] }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>{{ $data['check_in'] }} → {{ $data['check_out'] }}</span>
                    <span>{{ $quote['nights'] }} malam</span>
                </div>
                <hr>
                @foreach ($quote['nightly'] as $night)
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>{{ \Carbon\Carbon::parse($night['date'])->translatedFormat('d M Y') }}@if($night['from_fallback']) <span class="text-gray-400">(tarif standar)</span>@endif</span>
                        <span>Rp {{ number_format($night['amount'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <hr>
                <div class="flex justify-between">
                    <span>Subtotal Kamar</span>
                    <span>Rp {{ number_format($quote['room_total'], 0, ',', '.') }}</span>
                </div>
                @if ($quote['discount'] > 0)
                    <div class="flex justify-between text-emerald-700">
                        <span>Diskon Promo{{ isset($promo) && $promo ? ' ('.$promo->code.')' : '' }}</span>
                        <span>− Rp {{ number_format($quote['discount'], 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span>Service Charge ({{ number_format($quote['service_charge_pct'], 1, ',', '.') }}%)</span>
                    <span>Rp {{ number_format($quote['service_charge'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Pajak (PB1)</span>
                    <span>Rp {{ number_format($quote['tax'], 0, ',', '.') }}</span>
                </div>
                <hr>
                <div class="flex justify-between font-bold text-base">
                    <span>Total</span>
                    <span class="text-primary-700">Rp {{ number_format($quote['grand_total'], 0, ',', '.') }}</span>
                </div>
                <p class="text-xs text-gray-500 mt-2">Jumlah yang dibayar: <strong>Rp {{ number_format($quote['grand_total'], 0, ',', '.') }}</strong></p>
            </div>
        </div>
    </aside>
</div>
</div>
@endsection

@push('scripts')
<script>
function checkoutForm() {
    return {
        submitting: false,
        methodChosen: false,
        agreed: false,
        tracked: false,
        async trackEmail() {
            const email = document.querySelector('[name=email]')?.value || '';
            if (this.tracked || !email || !email.includes('@')) return;
            try {
                const sessionId = localStorage.getItem('booking_session_id') || crypto.randomUUID();
                localStorage.setItem('booking_session_id', sessionId);
                await fetch('{{ route('booking.cart.track') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        email,
                        session_id: sessionId,
                        guest_name: document.querySelector('[name=first_name]')?.value || '',
                        cart_data: JSON.stringify(@json($data))
                    })
                });
                this.tracked = true;
            } catch (e) {
                // non-blocking
            }
        }
    };
}
</script>
@endpush
