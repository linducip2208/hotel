@extends('panel.layout')
@section('title', 'Linen & Laundry — Tracking Stok')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Linen & Laundry</h1>
        <p class="text-sm text-gray-500 mt-0.5">Tracking stok linen, PAR level, dan transaksi laundry internal</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.hk.linen.uniforms') }}" class="inline-flex items-center gap-1.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Seragam
        </a>
    </div>
</div>

@if (session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-5 text-sm font-medium">
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm font-medium">
    {{ session('error') }}
</div>
@endif

{{-- Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-card text-center">
        <div class="text-2xl font-bold text-emerald-700">{{ $totalStock }}</div>
        <div class="text-xs text-emerald-600 mt-0.5 font-medium">Total Stok</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-red-100 shadow-card text-center">
        <div class="text-2xl font-bold text-red-700">{{ $totalDamaged }}</div>
        <div class="text-xs text-red-600 mt-0.5 font-medium">Rusak / Dibuang</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-amber-100 shadow-card text-center">
        <div class="text-2xl font-bold text-amber-700">{{ $belowParCount }}</div>
        <div class="text-xs text-amber-600 mt-0.5 font-medium">Di Bawah PAR</div>
    </div>
</div>

{{-- Transaction Form --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 mb-6">
    <h2 class="text-sm font-semibold text-gray-700 mb-4">Catat Transaksi</h2>
    <form method="POST" action="{{ route('panel.hk.linen.transaction') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
        @csrf
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Kategori Linen</label>
            <select name="linen_category_id" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">-- Pilih --</option>
                @foreach ($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->current_stock }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Transaksi</label>
            <select name="transaction_type" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="issue">Issue (Keluarkan)</option>
                <option value="return">Return (Kembalikan)</option>
                <option value="wash">Wash (Cuci)</option>
                <option value="discard">Discard (Buang)</option>
                <option value="audit">Audit (Stok Opname)</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah</label>
            <input type="number" name="quantity" value="1" min="1" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium py-2.5 rounded-xl transition-colors shadow-sm">
                Catat
            </button>
        </div>
        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-600 mb-1">Lokasi Dari</label>
            <input type="text" name="location_from" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Contoh: Gudang Linen Lt.1">
        </div>
        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-600 mb-1">Lokasi Tujuan</label>
            <input type="text" name="location_to" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Contoh: Room 201">
        </div>
        <div class="md:col-span-4">
            <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
            <input type="text" name="notes" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Opsional">
        </div>
    </form>
</div>

{{-- Stock Level Cards --}}
<h3 class="text-base font-semibold text-gray-800 mb-3">Level Stok per Kategori</h3>

@if ($categories->isEmpty())
<div class="bg-white rounded-2xl shadow-card border border-gray-100 px-5 py-16 text-center">
    <p class="text-sm font-medium text-gray-700">Belum ada kategori linen</p>
    <p class="text-xs text-gray-400 mt-1">Tambahkan kategori linen untuk memulai tracking.</p>
</div>
@else
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
    @foreach ($categories as $cat)
    @php
        $pct = $cat->par_pct;
        $barColor = match($cat->stock_status) {
            'empty', 'below_par' => 'bg-red-500',
            'at_par' => 'bg-amber-500',
            'above_par' => 'bg-emerald-500',
        };
        $borderColor = match($cat->stock_status) {
            'empty' => 'border-red-200 bg-red-50/30',
            'below_par' => 'border-amber-200 bg-amber-50/30',
            default => 'border-emerald-200 bg-white',
        };
    @endphp
    <div class="rounded-2xl border shadow-card p-4 {{ $borderColor }}">
        <div class="flex items-start justify-between mb-3">
            <div>
                <h3 class="text-base font-semibold text-gray-900">{{ $cat->name }}</h3>
                <span class="text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full capitalize mt-1 inline-block">
                    {{ $cat->type }}
                </span>
            </div>
            @if ($cat->stock_status === 'empty')
            <span class="text-xs font-bold text-red-600 bg-red-100 px-2 py-0.5 rounded-full">Kosong</span>
            @elseif ($cat->stock_status === 'below_par')
            <span class="text-xs font-medium text-amber-600 bg-amber-100 px-2 py-0.5 rounded-full">Di Bawah PAR</span>
            @elseif ($cat->stock_status === 'at_par')
            <span class="text-xs font-medium text-amber-600 bg-amber-100 px-2 py-0.5 rounded-full">Pas PAR</span>
            @endif
        </div>

        <div class="mb-2">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>{{ $cat->current_stock }} / {{ $cat->par_level }} (PAR)</span>
                <span>{{ $pct }}%</span>
            </div>
            <div class="w-full h-2.5 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full {{ $barColor }} rounded-full transition-all duration-300" style="width:{{ $pct }}%"></div>
            </div>
        </div>

        <div class="flex items-center gap-3 text-xs text-gray-500 mt-3">
            <span>Rusak: <strong class="text-red-600">{{ $cat->damaged_count }}</strong></span>
            <span>Transaksi: <strong>{{ $cat->transactions_count }}</strong></span>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Recent Transactions --}}
@if ($transactions->isNotEmpty())
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Transaksi Terbaru</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategori</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Lokasi</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Oleh</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Waktu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($transactions as $tx)
                @php
                    $txColor = match($tx->transaction_type) {
                        'issue' => 'blue',
                        'return' => 'emerald',
                        'wash' => 'indigo',
                        'discard' => 'red',
                        'audit' => 'amber',
                        default => 'gray'
                    };
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3 text-sm font-medium text-gray-800">{{ $tx->linenCategory?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-medium bg-{{ $txColor }}-50 text-{{ $txColor }}-700 px-2 py-0.5 rounded-full capitalize">{{ $tx->transaction_type }}</span>
                    </td>
                    <td class="px-4 py-3 text-center text-sm text-gray-700 font-semibold">{{ $tx->quantity }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        @if ($tx->location_from || $tx->location_to)
                        {{ $tx->location_from ?? '—' }} → {{ $tx->location_to ?? '—' }}
                        @else
                        —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $tx->performedBy?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right text-xs text-gray-400">{{ $tx->created_at->format('d M H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
