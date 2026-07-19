@extends('panel.layout')
@section('title', 'Lost & Found')
@section('content')

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Lost &amp; Found</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola barang hilang yang ditemukan di properti</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Catat Barang
        </button>
    </div>
</div>

{{-- Status Summary --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
    @php
        $found    = $statusCounts['found'] ?? 0;
        $claimed  = $statusCounts['claimed'] ?? 0;
        $returned = $statusCounts['returned'] ?? 0;
        $disposed = $statusCounts['disposed'] ?? 0;
        $donated  = $statusCounts['donated'] ?? 0;
    @endphp
    <div class="bg-white rounded-2xl p-4 border border-amber-100 shadow-sm text-center">
        <div class="text-2xl font-bold text-amber-600">{{ $found }}</div>
        <div class="text-xs text-amber-600 mt-0.5 font-medium">Ditemukan</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-blue-100 shadow-sm text-center">
        <div class="text-2xl font-bold text-blue-600">{{ $claimed }}</div>
        <div class="text-xs text-blue-600 mt-0.5 font-medium">Diklaim</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-sm text-center">
        <div class="text-2xl font-bold text-emerald-600">{{ $returned }}</div>
        <div class="text-xs text-emerald-600 mt-0.5 font-medium">Dikembalikan</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-teal-100 shadow-sm text-center">
        <div class="text-2xl font-bold text-teal-600">{{ $donated }}</div>
        <div class="text-xs text-teal-600 mt-0.5 font-medium">Disumbangkan</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-stone-100 shadow-sm text-center">
        <div class="text-2xl font-bold text-stone-500">{{ $disposed }}</div>
        <div class="text-xs text-stone-500 mt-0.5 font-medium">Dibuang</div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 mb-6">
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[120px]">
            <label class="block text-xs font-medium text-stone-500 mb-1">Status</label>
            <select name="status" class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2 bg-white text-stone-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Semua Status</option>
                <option value="found" @selected(request('status') === 'found')>Ditemukan</option>
                <option value="claimed" @selected(request('status') === 'claimed')>Diklaim</option>
                <option value="returned" @selected(request('status') === 'returned')>Dikembalikan</option>
                <option value="donated" @selected(request('status') === 'donated')>Disumbangkan</option>
                <option value="disposed" @selected(request('status') === 'disposed')>Dibuang</option>
            </select>
        </div>
        <div class="flex-1 min-w-[120px]">
            <label class="block text-xs font-medium text-stone-500 mb-1">Kategori</label>
            <select name="category" class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2 bg-white text-stone-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Semua Kategori</option>
                <option value="electronics" @selected(request('category') === 'electronics')>Elektronik</option>
                <option value="clothing" @selected(request('category') === 'clothing')>Pakaian</option>
                <option value="jewelry" @selected(request('category') === 'jewelry')>Perhiasan</option>
                <option value="documents" @selected(request('category') === 'documents')>Dokumen</option>
                <option value="toys" @selected(request('category') === 'toys')>Mainan</option>
                <option value="keys" @selected(request('category') === 'keys')>Kunci</option>
                <option value="other" @selected(request('category') === 'other')>Lainnya</option>
            </select>
        </div>
        <div class="flex-1 min-w-[140px]">
            <label class="block text-xs font-medium text-stone-500 mb-1">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / No. Barang / Lokasi..."
                   class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2 bg-white text-stone-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="flex-1 min-w-[120px]">
            <label class="block text-xs font-medium text-stone-500 mb-1">Dari</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2 bg-white text-stone-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="flex-1 min-w-[120px]">
            <label class="block text-xs font-medium text-stone-500 mb-1">Sampai</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2 bg-white text-stone-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Filter
            </button>
            <a href="{{ route('panel.hk.lost-found.index') }}" class="inline-flex items-center gap-1.5 bg-stone-100 hover:bg-stone-200 text-stone-600 px-4 py-2 rounded-lg text-sm font-medium transition">Reset</a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-stone-50/80 border-b border-stone-100">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">No. Barang</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Nama</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Kategori</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Lokasi</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Tgl Ditemukan</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Status</th>
                    <th class="px-5 py-3.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
            @forelse ($items as $item)
                <tr class="hover:bg-stone-50 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="font-mono text-xs text-indigo-600 font-medium">{{ $item->item_number }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="font-medium text-stone-900">{{ $item->name }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $item->categoryColor() }}">{{ $item->categoryLabel() }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-stone-600">
                        {{ $item->location_found ?: '—' }}
                        @if ($item->room)
                            <span class="text-xs text-stone-400 block">Kamar {{ $item->room->number }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-stone-600">
                        {{ $item->found_at?->format('d M Y') ?: '—' }}
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $item->statusColor() }}">{{ $item->statusLabel() }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('panel.hk.lost-found.show', $item->id) }}"
                           class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 text-xs font-medium transition">
                            Detail
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 rounded-2xl bg-stone-100 flex items-center justify-center">
                                <svg class="w-7 h-7 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            </div>
                            <p class="text-sm font-medium text-stone-700">Belum ada barang hilang</p>
                            <p class="text-xs text-stone-400">Catat barang yang ditemukan untuk mulai</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if ($items->hasPages())
        <div class="px-5 py-4 border-t border-stone-100 bg-stone-50">
            {{ $items->links() }}
        </div>
    @endif
</div>

{{-- Add Modal --}}
<div id="addModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="add-modal-title" role="dialog">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm transition-opacity" onclick="document.getElementById('addModal').classList.add('hidden')"></div>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form method="POST" action="{{ route('panel.hk.lost-found.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="bg-white px-6 pt-6 pb-4">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-bold text-stone-900">Catat Barang Hilang Baru</h3>
                        <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="text-stone-400 hover:text-stone-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-stone-500 mb-1">Nama Barang *</label>
                            <input type="text" name="name" required
                                   class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2 bg-white text-stone-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">Kategori *</label>
                            <select name="category" required class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2 bg-white text-stone-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Pilih...</option>
                                <option value="electronics">Elektronik</option>
                                <option value="clothing">Pakaian</option>
                                <option value="jewelry">Perhiasan</option>
                                <option value="documents">Dokumen</option>
                                <option value="toys">Mainan</option>
                                <option value="keys">Kunci</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">Lokasi Ditemukan</label>
                            <input type="text" name="location_found" placeholder="cth: Lobby, Restoran"
                                   class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2 bg-white text-stone-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">Kamar</label>
                            <select name="room_id" class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2 bg-white text-stone-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Tidak di kamar</option>
                                @foreach($rooms as $r)
                                    <option value="{{ $r->id }}">Kamar {{ $r->number }} - Lt. {{ $r->floor }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">Tanggal Ditemukan</label>
                            <input type="date" name="found_at" value="{{ now()->toDateString() }}"
                                   class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2 bg-white text-stone-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">Lokasi Penyimpanan</label>
                            <input type="text" name="storage_location" placeholder="cth: Lemari A-3"
                                   class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2 bg-white text-stone-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">Batas Simpan (hari)</label>
                            <input type="number" name="disposal_days" value="90" min="1" max="365"
                                   class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2 bg-white text-stone-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-stone-500 mb-1">Deskripsi</label>
                            <textarea name="description" rows="2" class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2 bg-white text-stone-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-stone-500 mb-1">Foto</label>
                            <input type="file" name="photos_upload[]" multiple accept="image/*"
                                   class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2 bg-white text-stone-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </div>
                </div>
                <div class="bg-stone-50 px-6 py-4 flex justify-end gap-3 border-t border-stone-100">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                            class="px-4 py-2 text-sm font-medium text-stone-600 bg-white border border-stone-200 rounded-lg hover:bg-stone-100 transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
