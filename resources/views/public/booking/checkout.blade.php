@extends('public.layout')
@section('title', 'Checkout')
@section('content')
<div x-data="abandonCartTracker()">
<h1 class="text-2xl font-bold mb-4">Checkout</h1>
@if ($errors->any())
    <div class="bg-red-50 text-red-800 p-3 rounded mb-3">{{ $errors->first() }}</div>
@endif
<form method="POST" action="{{ route('booking.submit') }}" class="bg-white border rounded p-4 max-w-lg space-y-3">
    @csrf
    @foreach ($data as $k => $v) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endforeach
    <input type="hidden" name="rate_plan_id" value="1">
    <input type="text" name="first_name" placeholder="Nama Depan" required class="w-full border rounded p-2">
    <input type="text" name="last_name" placeholder="Nama Belakang" class="w-full border rounded p-2">
    <input type="email" name="email" placeholder="Email" required class="w-full border rounded p-2"
           x-on:focusout="track($el.value)">
    <input type="tel" name="phone" placeholder="No. WA / HP" required class="w-full border rounded p-2">
    <textarea name="special_requests" placeholder="Permintaan khusus" class="w-full border rounded p-2"></textarea>
    <button class="bg-primary-600 text-white px-4 py-2 rounded">Lanjut ke Pembayaran</button>
</form>
</div>
@endsection

@push('scripts')
<script>
function abandonCartTracker() {
    return {
        tracked: false,
        tracking: false,
        async track(email) {
            if (this.tracked || !email || !email.includes('@')) return;
            this.tracking = true;
            try {
                const sessionId = localStorage.getItem('booking_session_id') || crypto.randomUUID();
                localStorage.setItem('booking_session_id', sessionId);
                const cartData = JSON.stringify(@json($data ?? []));
                await fetch('{{ route('booking.cart.track') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ email, session_id: sessionId, cart_data: cartData, guest_name: document.querySelector('[name=first_name]')?.value || '' })
                });
                this.tracked = true;
            } catch (e) {
                // silent fail
            } finally {
                this.tracking = false;
            }
        }
    };
}
</script>
@endpush
