@extends('panel.layout')
@section('title', 'Membership Spa')
@section('content')

<div class="mb-6" x-data="{ editing: null }">
    <h1 class="text-2xl font-bold text-gray-900">Membership Spa</h1>
    <p class="text-sm text-gray-500 mt-0.5">Kelola keanggotaan spa tamu</p>
</div>

<div class="grid lg:grid-cols-3 gap-5" x-data="{ editing: null }">

    {{-- Memberships list --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">No. Membership</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tamu</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Paket</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Masa Aktif</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Harga</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($memberships as $m)
                        @php
                            $planLabel = match($m->plan_type) { 'monthly' => 'Bulanan', 'quarterly' => 'Triwulan', 'annual' => 'Tahunan', default => $m->plan_type };
                            $statusColor = match($m->status) { 'active' => 'emerald', 'expired' => 'gray', 'cancelled' => 'red', default => 'gray' };
                            $statusLabel = match($m->status) { 'active' => 'Aktif', 'expired' => 'Kadaluarsa', 'cancelled' => 'Dibatalkan', default => $m->status };
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <span class="text-xs font-mono font-semibold text-primary-700 bg-primary-50 px-2 py-0.5 rounded">{{ $m->membership_number }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="text-sm font-medium text-gray-900">{{ $m->guest?->name ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-sm text-gray-700">{{ $planLabel }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <div class="text-xs text-gray-600">{{ $m->start_date->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-400">s/d {{ $m->end_date->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-900">
                                Rp {{ number_format($m->price, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-xs font-medium bg-{{ $statusColor }}-50 text-{{ $statusColor }}-700 px-2.5 py-1 rounded-full">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-3 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="editing = (editing === {{ $m->id }} ? null : {{ $m->id }})"
                                            class="text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-1 rounded-lg hover:bg-amber-100 transition-colors">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('panel.spa.memberships.update', $m->id) }}"
                                          onsubmit="return confirm('Nonaktifkan membership {{ $m->membership_number }}?')" class="inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="plan_type" value="{{ $m->plan_type }}">
                                        <input type="hidden" name="start_date" value="{{ $m->start_date->format('Y-m-d') }}">
                                        <input type="hidden" name="end_date" value="{{ $m->end_date->format('Y-m-d') }}">
                                        <input type="hidden" name="price" value="{{ $m->price }}">
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit"
                                                class="text-xs font-semibold text-red-600 bg-red-50 px-2 py-1 rounded-lg hover:bg-red-100 transition-colors">
                                            Nonaktifkan
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        {{-- Inline edit row --}}
                        <tr x-show="editing === {{ $m->id }}" x-cloak class="bg-amber-50/30">
                            <td colspan="7" class="px-5 py-3">
                                <form method="POST" action="{{ route('panel.spa.memberships.update', $m->id) }}" class="flex flex-wrap items-end gap-3">
                                    @csrf @method('PUT')
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Paket</label>
                                        <select name="plan_type"
                                                class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all">
                                            <option value="monthly" {{ $m->plan_type === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                            <option value="quarterly" {{ $m->plan_type === 'quarterly' ? 'selected' : '' }}>Triwulan</option>
                                            <option value="annual" {{ $m->plan_type === 'annual' ? 'selected' : '' }}>Tahunan</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tgl Mulai</label>
                                        <input type="date" name="start_date" required value="{{ $m->start_date->format('Y-m-d') }}"
                                               class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tgl Akhir</label>
                                        <input type="date" name="end_date" required value="{{ $m->end_date->format('Y-m-d') }}"
                                               class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Harga (Rp)</label>
                                        <input type="number" name="price" step="1" required value="{{ $m->price }}"
                                               class="w-36 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Metode Bayar</label>
                                        <input type="text" name="payment_method" value="{{ $m->payment_method }}"
                                               class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                                        <select name="status"
                                                class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all">
                                            <option value="active" {{ $m->status === 'active' ? 'selected' : '' }}>Aktif</option>
                                            <option value="expired" {{ $m->status === 'expired' ? 'selected' : '' }}>Kadaluarsa</option>
                                            <option value="cancelled" {{ $m->status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="hidden" name="auto_renew" value="0">
                                            <input type="checkbox" name="auto_renew" value="1" {{ $m->auto_renew ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-amber-600 focus:ring-amber-400">
                                            <span class="text-xs text-gray-600">Perpanjangan Otomatis</span>
                                        </label>
                                    </div>
                                    <button type="submit"
                                            class="bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                                        Simpan
                                    </button>
                                    <button type="button" @click="editing = null"
                                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                                        Batal
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-sm text-gray-400">Belum ada membership.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3 px-1">
            {{ $memberships->links() }}
        </div>
    </div>

    {{-- Add membership form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Tambah Membership</h2>
        </div>
        <form method="POST" action="{{ route('panel.spa.memberships.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">ID Tamu <span class="text-red-500">*</span></label>
                <input type="number" name="guest_id" required placeholder="ID tamu"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Paket <span class="text-red-500">*</span></label>
                <select name="plan_type" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="monthly">Bulanan</option>
                    <option value="quarterly">Triwulan</option>
                    <option value="annual">Tahunan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal Mulai <span class="text-red-500">*</span></label>
                <input type="date" name="start_date" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal Akhir <span class="text-red-500">*</span></label>
                <input type="date" name="end_date" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Harga (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="price" step="1" required placeholder="1000000"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Metode Pembayaran</label>
                <select name="payment_method"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="cash">Tunai</option>
                    <option value="transfer">Transfer Bank</option>
                    <option value="credit_card">Kartu Kredit</option>
                    <option value="qris">QRIS</option>
                </select>
            </div>
            <button type="submit"
                    class="w-full bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Tambah Membership
            </button>
        </form>
    </div>

</div>

@endsection
