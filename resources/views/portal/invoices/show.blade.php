@extends('portal.layout')
@section('title', 'Detail Tagihan — Portal Tamu')

@section('content')
<div>
    <a href="{{ route('customer.invoices') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Tagihan Saya
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Invoice Header --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div>
                        <h1 class="font-display text-2xl font-bold text-slate-900">{{ $folio->folio_no ?? 'Folio #' . $folio->id }}</h1>
                        @if($folio->reservation)
                            <p class="text-slate-500 text-sm mt-0.5">Reservasi: {{ $folio->reservation->ref }}</p>
                        @endif
                    </div>
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold {{ $folio->balance > 0 ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">
                        {{ $folio->balance > 0 ? 'Belum Lunas' : 'Lunas' }}
                    </span>
                </div>
            </div>

            {{-- Charges --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <h2 class="font-semibold text-slate-800 mb-4">Rincian Tagihan</h2>
                @if($folio->charges->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach($folio->charges as $charge)
                            <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between">
                                <div>
                                    <p class="text-slate-800">{{ $charge->description }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $charge->charge_date?->format('d M Y') ?? '' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-slate-800">Rp {{ number_format($charge->amount, 0, ',', '.') }}</p>
                                    @if($charge->tax_amount > 0)
                                        <p class="text-xs text-slate-400">+Rp {{ number_format($charge->tax_amount, 0, ',', '.') }} pajak</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-400 text-sm">Tidak ada rincian tagihan.</p>
                @endif
            </div>

            {{-- Payments History --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <h2 class="font-semibold text-slate-800 mb-4">Riwayat Pembayaran</h2>
                @if($folio->payments->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach($folio->payments as $payment)
                            <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between">
                                <div>
                                    <p class="text-slate-800 text-sm font-medium">
                                        {{ match($payment->payment_method) {
                                            'bank_transfer' => 'Transfer Bank',
                                            'ewallet' => 'E-Wallet',
                                            'credit_card' => 'Kartu Kredit',
                                            'cash' => 'Tunai',
                                            default => $payment->payment_method
                                        } }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $payment->payment_date?->format('d M Y') ?? '' }} &middot; {{ $payment->reference_no }}</p>
                                    @if(!empty($payment->gateway_payload['note']))
                                        <p class="text-xs text-slate-500 mt-0.5">Catatan: {{ $payment->gateway_payload['note'] }}</p>
                                    @endif
                                    @if(!empty($payment->gateway_payload['proof_path']))
                                        <a href="{{ Storage::url($payment->gateway_payload['proof_path']) }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-700 mt-0.5 inline-block">Lihat Bukti &nearr;</a>
                                    @endif
                                </div>
                                <p class="font-bold text-emerald-600">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-400 text-sm">Belum ada pembayaran.</p>
                @endif
            </div>
        </div>

        {{-- Right: Summary + Payment Form --}}
        <div class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <h3 class="font-semibold text-slate-800 mb-4">Ringkasan</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Total Tagihan</dt>
                        <dd class="font-medium text-slate-800">Rp {{ number_format($folio->total_charges, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Total Dibayar</dt>
                        <dd class="font-medium text-emerald-600">Rp {{ number_format($folio->total_payments, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-200 pt-3">
                        <dt class="font-semibold text-slate-800">Sisa Tagihan</dt>
                        <dd class="font-bold {{ $folio->balance > 0 ? 'text-red-600' : 'text-emerald-600' }} text-lg">
                            Rp {{ number_format($folio->balance, 0, ',', '.') }}
                        </dd>
                    </div>
                </dl>
            </div>

            @if($folio->balance > 0)
                <div class="bg-white border border-slate-200 rounded-2xl p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Upload Bukti Pembayaran</h3>
                    <form action="{{ route('customer.invoices.payment', $folio->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label for="amount" class="block text-sm font-semibold text-slate-700 mb-1.5">Jumlah Pembayaran</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400">Rp</span>
                                <input type="number" name="amount" id="amount" required
                                       value="{{ $folio->balance }}"
                                       max="{{ $folio->balance }}" min="1" step="1"
                                       class="w-full rounded-xl border border-slate-300 pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow">
                            </div>
                        </div>

                        <div>
                            <label for="payment_method" class="block text-sm font-semibold text-slate-700 mb-1.5">Metode Pembayaran</label>
                            <select name="payment_method" id="payment_method" required
                                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow">
                                <option value="bank_transfer">Transfer Bank</option>
                                <option value="ewallet">E-Wallet (GoPay/OVO/Dana)</option>
                                <option value="credit_card">Kartu Kredit</option>
                                <option value="cash">Tunai</option>
                            </select>
                        </div>

                        <div>
                            <label for="proof" class="block text-sm font-semibold text-slate-700 mb-1.5">Bukti Pembayaran</label>
                            <input type="file" name="proof" id="proof" accept=".jpg,.jpeg,.png,.pdf"
                                   class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow">
                            <p class="text-xs text-slate-400 mt-1.5">Format: JPG, PNG, atau PDF. Maks 5MB.</p>
                        </div>

                        <div>
                            <label for="note" class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan (opsional)</label>
                            <textarea name="note" id="note" rows="2" maxlength="500"
                                      class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow"
                                      placeholder="Contoh: Transfer via BCA atas nama..."></textarea>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-semibold py-3 rounded-xl shadow-lg shadow-indigo-500/25 transition-all hover:shadow-xl hover:shadow-indigo-500/30 text-sm">
                            Upload & Kirim Bukti
                        </button>

                        <p class="text-xs text-slate-400 text-center mt-2">Tim kami akan memverifikasi pembayaran dalam 1x24 jam kerja.</p>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
