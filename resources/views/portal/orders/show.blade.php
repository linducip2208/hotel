@extends('portal.layout')
@section('title', 'Detail Pesanan — Portal Tamu')

@section('content')
<div>
    <a href="{{ route('customer.bookings') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Pesanan Saya
    </a>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-6">
        <div>
            <h1 class="font-display text-2xl font-bold text-slate-900">Reservasi {{ $reservation->ref }}</h1>
            <p class="text-slate-500 text-sm mt-0.5">{{ $reservation->check_in->format('d M Y') }} &rarr; {{ $reservation->check_out->format('d M Y') }} &middot; {{ $reservation->check_in->diffInDays($reservation->check_out) }} malam</p>
        </div>
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold
            {{ match($reservation->status) {
                'confirmed' => 'bg-blue-50 text-blue-700',
                'checked_in' => 'bg-emerald-50 text-emerald-700',
                'checked_out', 'completed' => 'bg-slate-100 text-slate-600',
                'cancelled' => 'bg-red-50 text-red-600',
                default => 'bg-stone-50 text-stone-600'
            } }}">
            {{ match($reservation->status) {
                'confirmed' => 'Dikonfirmasi',
                'checked_in' => 'Check-in',
                'checked_out' => 'Check-out',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
                default => ucfirst($reservation->status)
            } }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Room Details --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <h2 class="font-semibold text-slate-800 mb-4">Detail Kamar</h2>
                @if($reservation->rooms->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach($reservation->rooms as $room)
                            <div class="py-3 first:pt-0 last:pb-0">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-slate-800">{{ $room->roomType?->name ?? 'Kamar' }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            @if($room->room)
                                                Kamar: {{ $room->room->room_number }}
                                            @endif
                                            @if($room->per_night_rates)
                                                &middot; Rp {{ number_format($room->per_night_rates[0] ?? $room->subtotal, 0, ',', '.') }}/malam
                                            @endif
                                        </p>
                                    </div>
                                    <p class="font-medium text-slate-800">Rp {{ number_format($room->subtotal, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-400 text-sm">Tidak ada detail kamar tersedia.</p>
                @endif
            </div>

            {{-- Addons --}}
            @if($reservation->addons->isNotEmpty())
                <div class="bg-white border border-slate-200 rounded-2xl p-6">
                    <h2 class="font-semibold text-slate-800 mb-4">Tambahan (Add-ons)</h2>
                    <div class="divide-y divide-slate-100">
                        @foreach($reservation->addons as $addon)
                            <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-slate-800">{{ $addon->description ?? 'Add-on #' . $addon->id }}</p>
                                    <p class="text-xs text-slate-400">{{ $addon->date_apply?->format('d M Y') ?? '' }}</p>
                                </div>
                                <p class="font-medium text-slate-800">Rp {{ number_format($addon->subtotal, 0, ',', '.') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Folio / Charges --}}
            @if($reservation->folios->isNotEmpty())
                <div class="bg-white border border-slate-200 rounded-2xl p-6">
                    <h2 class="font-semibold text-slate-800 mb-4">Tagihan (Folio)</h2>
                    @foreach($reservation->folios as $folio)
                        <div class="border border-slate-200 rounded-xl p-4 {{ !$loop->last ? 'mb-4' : '' }}">
                            <div class="flex items-center justify-between mb-3">
                                <p class="font-medium text-slate-800 text-sm">{{ $folio->folio_no ?? 'Folio #' . $folio->id }}</p>
                                <span class="text-xs font-semibold {{ $folio->balance > 0 ? 'text-red-500' : 'text-emerald-600' }}">
                                    {{ $folio->balance > 0 ? 'Belum Lunas' : 'Lunas' }}
                                </span>
                            </div>

                            @if($folio->charges->isNotEmpty())
                                <table class="w-full text-sm mb-2">
                                    <thead><tr class="text-xs text-slate-400"><th class="text-left pb-1">Deskripsi</th><th class="text-right pb-1">Jumlah</th></tr></thead>
                                    <tbody>
                                        @foreach($folio->charges as $charge)
                                            <tr class="border-t border-slate-50">
                                                <td class="py-1.5 text-slate-700">{{ $charge->description }}</td>
                                                <td class="py-1.5 text-right text-slate-700">Rp {{ number_format($charge->amount, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            @if($folio->payments->isNotEmpty())
                                <p class="text-xs font-semibold text-slate-500 mt-3 mb-1">Pembayaran:</p>
                                @foreach($folio->payments as $payment)
                                    <div class="flex items-center justify-between text-xs text-slate-600 py-1">
                                        <span>{{ $payment->payment_method }} &middot; {{ $payment->payment_date->format('d M Y') }}</span>
                                        <span class="font-medium">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            @endif

                            <div class="flex items-center justify-between border-t border-slate-200 pt-3 mt-3">
                                <span class="text-sm font-semibold text-slate-700">Sisa Tagihan</span>
                                <span class="text-lg font-bold {{ $folio->balance > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                    Rp {{ number_format($folio->balance, 0, ',', '.') }}
                                </span>
                            </div>

                            @if($folio->balance > 0)
                                <div class="mt-3 text-right">
                                    <a href="{{ route('customer.invoices.show', $folio->id) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">
                                        Bayar Sekarang
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Right: Summary --}}
        <div class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <h3 class="font-semibold text-slate-800 mb-4">Ringkasan Biaya</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Kamar</dt>
                        <dd class="font-medium text-slate-700">Rp {{ number_format($reservation->total_room, 0, ',', '.') }}</dd>
                    </div>
                    @if($reservation->total_addons > 0)
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Tambahan</dt>
                            <dd class="font-medium text-slate-700">Rp {{ number_format($reservation->total_addons, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                    @if($reservation->service_charge > 0)
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Service Charge</dt>
                            <dd class="font-medium text-slate-700">Rp {{ number_format($reservation->service_charge, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                    @if($reservation->tax_total > 0)
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Pajak</dt>
                            <dd class="font-medium text-slate-700">Rp {{ number_format($reservation->tax_total, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                    @if($reservation->discount_amount > 0)
                        <div class="flex items-center justify-between">
                            <dt class="text-emerald-600">Diskon</dt>
                            <dd class="font-medium text-emerald-600">-Rp {{ number_format($reservation->discount_amount, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                    <div class="flex items-center justify-between border-t border-slate-200 pt-3">
                        <dt class="font-semibold text-slate-800">Total</dt>
                        <dd class="font-bold text-slate-900 text-lg">Rp {{ number_format($reservation->grand_total, 0, ',', '.') }}</dd>
                    </div>
                    @if($reservation->balance > 0)
                        <div class="flex items-center justify-between">
                            <dt class="text-red-500 font-medium">Sisa Pembayaran</dt>
                            <dd class="font-bold text-red-600">Rp {{ number_format($reservation->balance, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6">
                <h3 class="font-semibold text-slate-800 mb-2">Tamu</h3>
                <p class="text-slate-700 font-medium">{{ $reservation->primaryGuest?->full_name ?? $guest->name }}</p>
                @if($reservation->primaryGuest?->email)
                    <p class="text-xs text-slate-400 mt-0.5">{{ $reservation->primaryGuest->email }}</p>
                @endif
                @if($reservation->primaryGuest?->phone)
                    <p class="text-xs text-slate-400">{{ $reservation->primaryGuest->phone }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
