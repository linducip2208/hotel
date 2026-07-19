@extends('panel.layout')
@section('title', 'Pengaturan Referral')
@section('content')

@php
$rType = request()->old('reward_type', session('referral_reward_type', 'discount'));
$rAmount = request()->old('default_reward_amount', session('referral_default_reward', 50000));
$rDiscount = request()->old('default_discount_pct', session('referral_default_discount', 10));
@endphp

<div class="mb-6">
    <a href="{{ route('panel.marketing.referrals') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600 transition-colors mb-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Referral
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Pengaturan Referral</h1>
    <p class="text-sm text-gray-500 mt-0.5">Konfigurasi reward dan insentif untuk program referral</p>
</div>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('panel.marketing.referrals.settings.save') }}" class="space-y-6">
        @csrf
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-6">
            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Default Reward Amount (Rp)</label>
                    <input type="number" name="default_reward_amount" required step="0.01" min="0"
                           value="{{ $rAmount }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">Jumlah reward default untuk setiap referral berhasil</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Default Discount (%)</label>
                    <input type="number" name="default_discount_pct" step="0.1" min="0" max="100"
                           value="{{ $rDiscount }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">Diskon untuk tamu yang menggunakan kode referral</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-6">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tipe Reward</label>
            <select name="reward_type" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="discount" {{ $rType === 'discount' ? 'selected' : '' }}>Diskon</option>
                <option value="cashback" {{ $rType === 'cashback' ? 'selected' : '' }}>Cashback</option>
                <option value="points" {{ $rType === 'points' ? 'selected' : '' }}>Loyalty Points</option>
                <option value="voucher" {{ $rType === 'voucher' ? 'selected' : '' }}>Voucher</option>
            </select>
            <p class="text-xs text-gray-400 mt-1">Bentuk reward yang diberikan kepada referrer</p>
        </div>

        <button type="submit"
                class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Simpan Pengaturan
        </button>
    </form>
</div>

@endsection
