@extends('panel.layout')
@section('title', 'Detail Lost & Found')
@section('content')

@php
    $propertyId = app('current_property')->id;
    use App\Models\Guest;$guests = Guest::where('property_id', $propertyId)->orderBy('first_name')->get();
@endphp

<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('panel.hk.lost-found.index') }}" class="inline-flex items-center gap-1.5 text-sm text-stone-500 hover:text-stone-700 mb-2 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Lost &amp; Found
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-stone-100 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs text-indigo-600 font-medium">{{ $item->item_number }}</span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $item->statusColor() }}">{{ $item->statusLabel() }}</span>
                </div>
                <h1 class="text-xl font-bold text-stone-900 mt-1">{{ $item->name }}</h1>
            </div>
            <button onclick="document.getElementById('editModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 bg-stone-100 hover:bg-stone-200 text-stone-600 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </button>
        </div>

        {{-- Photos --}}
        @if ($item->photos)
        <div class="px-6 py-4 border-b border-stone-100 flex gap-3 overflow-x-auto">
            @foreach($item->photos as $photo)
                <img src="{{ Storage::url($photo) }}" class="max-w-[200px] max-h-48 rounded-xl object-cover border border-stone-200">
            @endforeach
        </div>
        @endif

        {{-- Details --}}
        <div class="px-6 py-5">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs font-medium text-stone-400 uppercase tracking-wide">Kategori</dt>
                    <dd class="text-stone-900 font-medium mt-0.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs {{ $item->categoryColor() }}">{{ $item->categoryLabel() }}</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-stone-400 uppercase tracking-wide">Lokasi Ditemukan</dt>
                    <dd class="text-stone-900 font-medium mt-0.5">{{ $item->location_found ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-stone-400 uppercase tracking-wide">Tanggal Ditemukan</dt>
                    <dd class="text-stone-900 font-medium mt-0.5">{{ $item->found_at?->format('d M Y H:i') ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-stone-400 uppercase tracking-wide">Kamar</dt>
                    <dd class="text-stone-900 font-medium mt-0.5">{{ $item->room ? 'Kamar ' . $item->room->number . ' — Lt. ' . $item->room->floor : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-stone-400 uppercase tracking-wide">Ditemukan Oleh</dt>
                    <dd class="text-stone-900 font-medium mt-0.5">{{ $item->foundByUser?->name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-stone-400 uppercase tracking-wide">Lokasi Penyimpanan</dt>
                    <dd class="text-stone-900 font-medium mt-0.5">{{ $item->storage_location ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-stone-400 uppercase tracking-wide">Batas Simpan</dt>
                    <dd class="text-stone-900 font-medium mt-0.5">{{ $item->disposal_days }} hari
                        @if ($item->isExpired())
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-red-100 text-red-700 ml-1">Kedaluwarsa</span>
                        @endif
                    </dd>
                </div>
                @if ($item->status === 'claimed' || $item->status === 'returned')
                <div>
                    <dt class="text-xs font-medium text-stone-400 uppercase tracking-wide">Diklaim Oleh</dt>
                    <dd class="text-stone-900 font-medium mt-0.5">{{ $item->claimedByGuest?->full_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-stone-400 uppercase tracking-wide">Tanggal Diklaim</dt>
                    <dd class="text-stone-900 font-medium mt-0.5">{{ $item->claimed_at?->format('d M Y H:i') ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-stone-400 uppercase tracking-wide">Diverifikasi Oleh</dt>
                    <dd class="text-stone-900 font-medium mt-0.5">{{ $item->claim_verified_by ?: '—' }}</dd>
                </div>
                @endif
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium text-stone-400 uppercase tracking-wide">Deskripsi</dt>
                    <dd class="text-stone-700 mt-0.5">{{ $item->description ?: '—' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Actions --}}
        @if ($item->status === 'found')
        <div class="px-6 py-4 border-t border-stone-100 bg-stone-50/50">
            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('panel.hk.lost-found.claim', $item->id) }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-stone-500 mb-1">Tamu (ID)</label>
                        <input type="number" name="claimed_by_guest_id" placeholder="ID Tamu" class="w-32 text-sm border border-stone-200 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-stone-500 mb-1">Diverifikasi Oleh</label>
                        <input type="text" name="claim_verified_by" placeholder="Nama petugas" value="{{ auth()->user()->name }}" class="w-40 text-sm border border-stone-200 rounded-lg px-3 py-2">
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Klaim</button>
                </form>
                <form method="POST" action="{{ route('panel.hk.lost-found.return', $item->id) }}">
                    @csrf
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Kembalikan ke Pemilik</button>
                </form>
                <form method="POST" action="{{ route('panel.hk.lost-found.donate', $item->id) }}" onsubmit="return confirm('Sumbangkan barang ini?')">
                    @csrf
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Sumbangkan</button>
                </form>
                <form method="POST" action="{{ route('panel.hk.lost-found.dispose', $item->id) }}" onsubmit="return confirm('Buang barang ini?')">
                    @csrf
                    <button type="submit" class="bg-stone-200 hover:bg-stone-300 text-stone-600 text-sm font-medium px-4 py-2 rounded-lg transition">Buang</button>
                </form>
            </div>
        </div>
        @elseif ($item->status === 'claimed')
        <div class="px-6 py-4 border-t border-stone-100 bg-stone-50/50 flex gap-2">
            <form method="POST" action="{{ route('panel.hk.lost-found.return', $item->id) }}">
                @csrf
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Kembalikan ke Pemilik</button>
            </form>
        </div>
        @endif
    </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm" onclick="document.getElementById('editModal').classList.add('hidden')"></div>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form method="POST" action="{{ route('panel.hk.lost-found.update', $item->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="bg-white px-6 pt-6 pb-4">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-bold text-stone-900">Edit Barang</h3>
                        <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="text-stone-400 hover:text-stone-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-stone-500 mb-1">Nama Barang *</label>
                            <input type="text" name="name" value="{{ $item->name }}" required
                                   class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">Kategori *</label>
                            <select name="category" required class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2">
                                <option value="electronics" @selected($item->category === 'electronics')>Elektronik</option>
                                <option value="clothing" @selected($item->category === 'clothing')>Pakaian</option>
                                <option value="jewelry" @selected($item->category === 'jewelry')>Perhiasan</option>
                                <option value="documents" @selected($item->category === 'documents')>Dokumen</option>
                                <option value="toys" @selected($item->category === 'toys')>Mainan</option>
                                <option value="keys" @selected($item->category === 'keys')>Kunci</option>
                                <option value="other" @selected($item->category === 'other')>Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">Lokasi Ditemukan</label>
                            <input type="text" name="location_found" value="{{ $item->location_found }}"
                                   class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">Kamar</label>
                            <select name="room_id" class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2">
                                <option value="">Tidak di kamar</option>
                                @php $allRooms = \App\Models\Room::where('property_id', app('current_property')->id)->orderBy('floor')->orderBy('number')->get(); @endphp
                                @foreach($allRooms as $r)
                                    <option value="{{ $r->id }}" @selected($item->room_id === $r->id)>Kamar {{ $r->number }} - Lt. {{ $r->floor }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">Tanggal Ditemukan</label>
                            <input type="date" name="found_at" value="{{ $item->found_at?->toDateString() }}"
                                   class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">Lokasi Penyimpanan</label>
                            <input type="text" name="storage_location" value="{{ $item->storage_location }}"
                                   class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">Batas Simpan (hari)</label>
                            <input type="number" name="disposal_days" value="{{ $item->disposal_days }}" min="1" max="365"
                                   class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-stone-500 mb-1">Deskripsi</label>
                            <textarea name="description" rows="2" class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2">{{ $item->description }}</textarea>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-stone-500 mb-1">Tambah Foto</label>
                            <input type="file" name="photos_upload[]" multiple accept="image/*"
                                   class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700">
                        </div>
                    </div>
                </div>
                <div class="bg-stone-50 px-6 py-4 flex justify-end gap-3 border-t border-stone-100">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                            class="px-4 py-2 text-sm font-medium text-stone-600 bg-white border border-stone-200 rounded-lg hover:bg-stone-100 transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
