@extends('portal.guest-app-layout')
@section('title', 'Permintaan Tamu')
@section('content')

<h1 class="text-xl font-bold text-stone-900 mb-2">Permintaan</h1>
<p class="text-sm text-stone-500 mb-6">Minta housekeeping, perbaikan, atau amenitas tambahan</p>

{{-- New Request Form --}}
<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-5 mb-6">
    <h3 class="text-sm font-semibold text-stone-900 mb-4">Buat Permintaan Baru</h3>
    <form method="POST" action="{{ route('customer.guest.requests.store') }}">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-stone-500 mb-1">Jenis Permintaan *</label>
                <select name="type" required class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2.5 bg-white text-stone-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Pilih jenis...</option>
                    <option value="housekeeping">Housekeeping (handuk, cleaning)</option>
                    <option value="maintenance">Perbaikan (AC, TV, pipa)</option>
                    <option value="extra_amenities">Amenitas Tambahan (bantal, selimut)</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-stone-500 mb-1">Prioritas</label>
                <select name="priority" class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2.5 bg-white text-stone-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="normal">Normal</option>
                    <option value="low">Rendah</option>
                    <option value="high">Tinggi</option>
                </select>
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-xs font-medium text-stone-500 mb-1">Deskripsi *</label>
            <textarea name="description" rows="3" required placeholder="Jelaskan apa yang Anda butuhkan..."
                      class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2.5 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition shadow-sm">
            Kirim Permintaan
        </button>
    </form>
</div>

{{-- Request History --}}
<h2 class="text-sm font-semibold text-stone-500 uppercase tracking-wide mb-3">Riwayat Permintaan</h2>
<div class="space-y-2">
    @forelse ($requests as $req)
        @php
            $statusBadge = match($req->status) {
                'pending'   => 'bg-amber-100 text-amber-700',
                'in_progress'=> 'bg-blue-100 text-blue-700',
                'resolved'  => 'bg-emerald-100 text-emerald-700',
                'cancelled' => 'bg-stone-100 text-stone-500',
                default     => 'bg-stone-100 text-stone-500',
            };
            $typeIcon = match($req->type) {
                'housekeeping'     => '🏠',
                'maintenance'       => '🔧',
                'extra_amenities'  => '➕',
                default             => '📋',
            };
        @endphp
        <div class="bg-white rounded-xl p-4 border border-stone-100 shadow-sm">
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-2">
                    <span class="text-lg">{{ $typeIcon }}</span>
                    <span class="text-xs font-medium text-stone-500 uppercase">{{ str_replace('_', ' ', $req->type) }}</span>
                </div>
                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium {{ $statusBadge }}">
                    {{ match($req->status) { 'pending' => 'Menunggu', 'in_progress' => 'Diproses', 'resolved' => 'Selesai', default => ucfirst($req->status) } }}
                </span>
            </div>
            <p class="text-sm text-stone-700">{{ $req->description }}</p>
            <p class="text-[11px] text-stone-400 mt-1">{{ $req->created_at?->diffForHumans() }}</p>
        </div>
    @empty
        <div class="bg-white rounded-2xl p-8 text-center border border-stone-100">
            <p class="text-sm text-stone-400">Belum ada permintaan</p>
        </div>
    @endforelse
</div>

@endsection
