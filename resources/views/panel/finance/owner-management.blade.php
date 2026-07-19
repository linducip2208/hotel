@extends('panel.layout')
@section('title', 'Manajemen Owner')
@section('content')

@php
    $fmtRupiah = function($val) { return 'Rp ' . number_format(abs($val), 0, ',', '.'); };
@endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Portal Pemilik</h1>
    <p class="text-sm text-slate-500 mt-0.5">Kelola pemilik properti, distribusi laba, dan dokumen investor</p>
</div>

{{-- Owner Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Pemilik Aktif</p>
        <p class="text-2xl font-bold text-slate-900">{{ $owners->where('is_active', true)->count() }}</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Total Kepemilikan</p>
        <p class="text-2xl font-bold text-slate-900">{{ $owners->sum('ownership_pct') }}%</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Distribusi Bulan Ini</p>
        <p class="text-2xl font-bold text-slate-900">{{ $fmtRupiah($calculate['summary']['nop'] ?? 0) }}</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Distribusi Pending</p>
        <p class="text-2xl font-bold text-amber-600">{{ $distributions->where('status', 'pending')->count() }}</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">

    {{-- Owner List + Add Form --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700">Daftar Pemilik Properti</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Email</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Kepemilikan</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Investasi</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($owners as $o)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-violet-500 flex items-center justify-center text-white font-semibold text-xs">{{ strtoupper(substr($o->user->name, 0, 1)) }}</div>
                                    <span class="text-sm font-medium text-slate-800">{{ $o->user->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-sm text-slate-500">{{ $o->user->email }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-sm font-bold text-indigo-700">{{ number_format($o->ownership_pct, 1) }}%</span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono text-sm text-slate-700">
                                {{ $fmtRupiah($o->investment_amount) }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $o->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $o->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <form action="{{ route('panel.finance.owners.destroy', $o->id) }}" method="POST" onsubmit="return confirm('Hapus pemilik ini?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-medium text-rose-600 hover:text-rose-800 transition-colors">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <div class="flex flex-col items-center justify-center py-8 text-slate-400">
                                    <p class="text-sm">Belum ada pemilik terdaftar</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Distribution Calculation --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-slate-700">Perhitungan Distribusi</h2>
                <form method="GET" class="inline-flex items-center gap-2">
                    <input type="month" name="period" value="{{ \Carbon\Carbon::parse($period)->format('Y-m') }}" onchange="this.form.submit()"
                           class="text-sm font-medium bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 outline-none">
                </form>
            </div>

            @if(!empty($calculate['distributions']))
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
                @foreach($calculate['distributions'] as $dist)
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                    <p class="text-xs text-slate-500 mb-1">{{ $dist['owner_name'] }} ({{ number_format($dist['ownership_pct'], 1) }}%)</p>
                    <p class="text-lg font-bold text-slate-900">{{ $fmtRupiah($dist['distribution_amount']) }}</p>
                </div>
                @endforeach
            </div>

            <form action="{{ route('panel.finance.owners.distributions.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="period_start" value="{{ $calculate['summary']['period_start'] }}">
                <input type="hidden" name="period_end" value="{{ $calculate['summary']['period_end'] }}">
                <div class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pemilik</label>
                        <select name="owner_user_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                            @foreach($calculate['distributions'] as $dist)
                                <option value="{{ $dist['owner_user_id'] }}">{{ $dist['owner_name'] }} — {{ $fmtRupiah($dist['distribution_amount']) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jumlah</label>
                        <input type="number" name="distribution_amount" step="0.01"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-mono outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Metode</label>
                        <input type="text" name="payment_method" placeholder="Transfer Bank"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    </div>
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-sm">
                    Catat Distribusi
                </button>
            </form>
            @endif
        </div>

        {{-- Distributions History --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-slate-700">Riwayat Distribusi</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Periode</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Pemilik</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Jumlah</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($distributions as $d)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5 text-sm text-slate-700">
                                {{ \Carbon\Carbon::parse($d->period_start)->isoFormat('DD MMM Y') }} &mdash; {{ \Carbon\Carbon::parse($d->period_end)->isoFormat('DD MMM Y') }}
                            </td>
                            <td class="px-4 py-3.5 text-sm text-slate-700">{{ $d->owner?->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3.5 text-right font-mono text-sm font-bold text-slate-900">{{ $fmtRupiah($d->distribution_amount) }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $d->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $d->status === 'paid' ? 'Dibayar' : 'Pending' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                @if($d->status === 'pending')
                                <form action="{{ route('panel.finance.owners.distributions.pay', $d->id) }}" method="POST" class="inline-flex items-center gap-2">
                                    @csrf
                                    <button class="text-xs font-medium text-emerald-600 hover:text-emerald-800 transition-colors">Tandai Dibayar</button>
                                </form>
                                @else
                                <span class="text-xs text-slate-400">{{ $d->paid_at?->format('d M Y') }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">
                                <div class="flex flex-col items-center justify-center py-8 text-slate-400">
                                    <p class="text-sm">Belum ada distribusi</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($distributions->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $distributions->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Sidebar forms --}}
    <div class="space-y-6">
        {{-- Add Owner --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4">Tambah Pemilik</h2>
            <form action="{{ route('panel.finance.owners.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">User <span class="text-red-500">*</span></label>
                    <select name="user_id" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                        <option value="">— pilih user —</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kepemilikan (%) <span class="text-red-500">*</span></label>
                    <input type="number" name="ownership_pct" required step="0.01" min="0" max="100" value="100"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jumlah Investasi</label>
                    <input type="number" name="investment_amount" step="0.01" min="0" value="0"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-mono outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Bergabung</label>
                    <input type="date" name="joined_at" value="{{ now()->toDateString() }}"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                    Tambah Pemilik
                </button>
            </form>
        </div>

        {{-- Upload Document --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4">Upload Dokumen</h2>
            <form action="{{ route('panel.finance.owners.documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pemilik <span class="text-red-500">*</span></label>
                    <select name="owner_user_id" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                        <option value="">— pilih pemilik —</option>
                        @foreach($owners->where('is_active', true) as $o)
                            <option value="{{ $o->user_id }}">{{ $o->user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Judul <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="Laporan Tahunan 2026"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tipe <span class="text-red-500">*</span></label>
                    <select name="document_type" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                        <option value="financial_report">Laporan Keuangan</option>
                        <option value="tax_report">Laporan Pajak</option>
                        <option value="agreement">Perjanjian</option>
                        <option value="invoice">Invoice</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">File <span class="text-red-500">*</span></label>
                    <input type="file" name="file" required
                           class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                    Upload Dokumen
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
