@extends('panel.layout')
@section('title', 'Detail Vendor')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <a href="{{ route('panel.inventory.vendors.index') }}" class="text-xs text-primary-600 hover:underline mb-1 inline-block">&larr; Kembali ke daftar vendor</a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $vendor->name }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $vendor->category_label ?? $vendor->category }}</p>
    </div>
    <div class="flex items-center gap-2">
        <span class="text-xs px-2.5 py-1 rounded-full {{ $vendor->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
            {{ $vendor->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-5">

    {{-- Vendor Info --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Informasi Vendor</h2>
        </div>
        <div class="p-5 space-y-3 text-sm">
            @if($vendor->contact_person)
            <div class="flex justify-between"><span class="text-gray-400">Kontak</span><span class="font-semibold text-gray-700">{{ $vendor->contact_person }}</span></div>
            @endif
            @if($vendor->phone)
            <div class="flex justify-between"><span class="text-gray-400">Telepon</span><span class="font-semibold text-gray-700">{{ $vendor->phone }}</span></div>
            @endif
            @if($vendor->email)
            <div class="flex justify-between"><span class="text-gray-400">Email</span><span class="font-semibold text-gray-700">{{ $vendor->email }}</span></div>
            @endif
            @if($vendor->address)
            <div class="flex justify-between"><span class="text-gray-400">Alamat</span><span class="font-semibold text-gray-700 text-right max-w-[60%]">{{ $vendor->address }}</span></div>
            @endif
            @if($vendor->tax_id)
            <div class="flex justify-between"><span class="text-gray-400">NPWP</span><span class="font-semibold text-gray-700">{{ $vendor->tax_id }}</span></div>
            @endif
            <div class="flex justify-between"><span class="text-gray-400">Term Bayar</span><span class="font-semibold text-gray-700">{{ $vendor->payment_terms_days }} hari</span></div>
            <div class="flex justify-between"><span class="text-gray-400">Total Spend</span><span class="font-bold text-primary-600">Rp {{ number_format($totalSpend, 0, ',', '.') }}</span></div>
        </div>
    </div>

    {{-- Contracts --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Add Contract --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
            <div class="px-5 py-4">
                <h2 class="text-sm font-semibold text-gray-700">Tambah Kontrak</h2>
            </div>
            <form method="POST" action="{{ route('panel.inventory.vendors.contracts.store', $vendor->id) }}" class="p-5 space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">No. Kontrak</label>
                        <input type="text" name="contract_number" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nilai</label>
                        <input type="number" name="value" step="0.01" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal Selesai</label>
                        <input type="date" name="end_date" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Lingkup Pekerjaan</label>
                    <textarea name="scope_of_work" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all" placeholder="Deskripsi pekerjaan..."></textarea>
                </div>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
                    Simpan Kontrak
                </button>
            </form>
        </div>

        {{-- Contracts List --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Kontrak ({{ $vendor->contracts->count() }})</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($vendor->contracts as $c)
                <div class="flex items-center gap-4 px-5 py-3">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-900">{{ $c->contract_number ?: 'Kontrak #'.$c->id }}</div>
                        <div class="text-xs text-gray-400">{{ $c->start_date->format('d M Y') }} &mdash; {{ $c->end_date?->format('d M Y') ?? 'Berjalan' }}</div>
                    </div>
                    <div class="text-sm font-mono text-gray-700">Rp {{ number_format($c->value, 0, ',', '.') }}</div>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ 
                        $c->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 
                        ($c->status === 'expired' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-500')
                    }}">{{ $c->status }}</span>
                </div>
                @empty
                <div class="px-5 py-6 text-center text-xs text-gray-400">Belum ada kontrak</div>
                @endforelse
            </div>
        </div>

        {{-- Purchase History --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Riwayat Pembelian ({{ $vendor->purchaseOrders->count() }})</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50 text-left">
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">PO</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($vendor->purchaseOrders as $po)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3 text-sm font-semibold text-primary-600">{{ $po->po_number }}</td>
                            <td class="px-5 py-3 text-xs text-gray-500">{{ $po->order_date?->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-sm font-mono text-gray-700">Rp {{ number_format($po->total, 0, ',', '.') }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs px-2 py-0.5 rounded-full {{ 
                                    $po->status === 'sent' ? 'bg-blue-50 text-blue-700' : 
                                    ($po->status === 'received' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500')
                                }}">{{ $po->status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-5 py-6 text-center text-xs text-gray-400">Belum ada pembelian</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@endsection
