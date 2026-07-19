@extends('panel.layout')
@section('title', $account->exists ? 'Edit Corporate' : 'Tambah Corporate')
@section('content')

@php $isEdit = $account->exists; @endphp

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.sales.corporate.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $isEdit ? 'Edit Corporate Account' : 'Tambah Corporate Account' }}</h1>
        <p class="text-sm text-gray-500">{{ $isEdit ? $account->company_name : 'Daftarkan perusahaan korporat baru' }}</p>
    </div>
</div>

<form method="POST" action="{{ $isEdit ? route('panel.sales.corporate.update', $account->id) : route('panel.sales.corporate.store') }}">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Company Info --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Informasi Perusahaan</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Perusahaan <span class="text-rose-500">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name', $account->company_name) }}" required
                               class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">NPWP / Tax ID</label>
                        <input type="text" name="tax_id" value="{{ old('tax_id', $account->tax_id) }}"
                               class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Industri</label>
                        <input type="text" name="industry" value="{{ old('industry', $account->industry) }}"
                               class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kontak Person</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person', $account->contact_person) }}"
                               class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone', $account->phone) }}"
                               class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $account->email) }}"
                               class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat</label>
                        <textarea name="address" rows="2"
                                  class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('address', $account->address) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Rate Agreement --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Kesepakatan Tarif</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Jenis Rate <span class="text-rose-500">*</span></label>
                        <select name="rate_agreement_type" required
                                class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="fixed" {{ old('rate_agreement_type', $account->rate_agreement_type) === 'fixed' ? 'selected' : '' }}>Fixed</option>
                            <option value="percentage_discount" {{ old('rate_agreement_type', $account->rate_agreement_type) === 'percentage_discount' ? 'selected' : '' }}>Diskon Persentase</option>
                            <option value="dynamic" {{ old('rate_agreement_type', $account->rate_agreement_type) === 'dynamic' ? 'selected' : '' }}>Dynamic</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Diskon (%)</label>
                        <input type="number" name="discount_pct" step="0.01" min="0" max="100" value="{{ old('discount_pct', $account->discount_pct) }}"
                               class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Limit Kredit (Rp)</label>
                        <input type="number" name="credit_limit" step="0.01" min="0" value="{{ old('credit_limit', $account->credit_limit) }}"
                               class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>

            {{-- Contract --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Kontrak & Komitmen</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Mulai Kontrak</label>
                        <input type="date" name="contract_start" value="{{ old('contract_start', $account->contract_start?->format('Y-m-d')) }}"
                               class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Selesai Kontrak</label>
                        <input type="date" name="contract_end" value="{{ old('contract_end', $account->contract_end?->format('Y-m-d')) }}"
                               class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Term Pembayaran (hari)</label>
                        <input type="number" name="payment_terms_days" min="0" value="{{ old('payment_terms_days', $account->payment_terms_days) }}"
                               class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Komitmen Malam/Tahun</label>
                        <input type="number" name="annual_room_night_commitment" min="0" value="{{ old('annual_room_night_commitment', $account->annual_room_night_commitment) }}"
                               class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>
        </div>

        {{-- Right --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
                <h3 class="font-bold text-gray-900 mb-3">Status</h3>
                @if($isEdit)
                <select name="status" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="active" {{ $account->status === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="suspended" {{ $account->status === 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
                    <option value="expired" {{ $account->status === 'expired' ? 'selected' : '' }}>Kadaluarsa</option>
                </select>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
                <h3 class="font-bold text-gray-900 mb-3">Catatan</h3>
                <textarea name="notes" rows="4"
                          class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes', $account->notes) }}</textarea>
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl transition-colors shadow-sm text-sm">
                {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Corporate Account' }}
            </button>

            @if($isEdit)
            <form method="POST" action="{{ route('panel.sales.corporate.destroy', $account->id) }}" onsubmit="return confirm('Hapus corporate account ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold py-3 px-4 rounded-xl transition-colors text-sm mt-2">Hapus Corporate Account</button>
            </form>
            @endif
        </div>
    </div>
</form>
@endsection
