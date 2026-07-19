@extends('panel.layout')
@section('title', 'Kamar')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Inventaris Kamar</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $rooms->count() }} kamar terdaftar di properti ini</p>
    </div>
    <div class="flex items-center gap-2">
        <button onclick="document.getElementById('addSingleForm').classList.toggle('hidden');document.getElementById('addBulkForm').classList.add('hidden')"
                class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Kamar
        </button>
        <button onclick="document.getElementById('addBulkForm').classList.toggle('hidden');document.getElementById('addSingleForm').classList.add('hidden')"
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7M4 7c0-2 1-3 3-3h10c2 0 3 1 3 3M4 7h16"/></svg>
            Tambah Massal
        </button>
    </div>
</div>

{{-- Add Single Room Form --}}
<div id="addSingleForm" class="hidden mb-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Tambah Kamar Tunggal</h2>
        </div>
        <form method="POST" action="{{ route('panel.settings.rooms.store') }}" class="p-5">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nomor Kamar <span class="text-red-500">*</span></label>
                    <input type="text" name="number" required placeholder="e.g. 101, 201A"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Lantai</label>
                    <input type="number" name="floor" min="0" placeholder="1"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tipe Kamar <span class="text-red-500">*</span></label>
                    <select name="room_type_id" required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                        <option value="">— pilih —</option>
                        @foreach($roomTypes as $rt)
                        <option value="{{ $rt->id }}">{{ $rt->code }} — {{ $rt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Catatan</label>
                    <input type="text" name="notes" placeholder="e.g. dekat lift"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
            </div>
            <div class="flex items-center gap-3 mt-4">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Simpan Kamar
                </button>
                <button type="button" onclick="document.getElementById('addSingleForm').classList.add('hidden')"
                        class="text-sm text-gray-500 hover:text-gray-700 transition-colors">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- Add Bulk Rooms Form --}}
<div id="addBulkForm" class="hidden mb-6">
    <div class="bg-white rounded-2xl shadow-card border border-emerald-200 divide-y divide-gray-50">
        <div class="px-5 py-4 bg-emerald-50/40 flex items-center gap-3">
            <span class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-sm">⚡</span>
            <div>
                <h2 class="text-sm font-semibold text-gray-700">Tambah Kamar Massal</h2>
                <p class="text-xs text-gray-500 mt-0.5">Generate banyak kamar sekaligus — cocok untuk 50-200 kamar</p>
            </div>
        </div>
        <form method="POST" action="{{ route('panel.settings.rooms.bulk-store') }}" class="p-5">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Prefix <span class="text-red-500">*</span></label>
                    <input type="text" name="prefix" required placeholder="e.g. 10 (→ 1001)"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 font-mono focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all outline-none">
                    <p class="text-[10px] text-gray-400 mt-0.5">Prefix angka depan</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Mulai <span class="text-red-500">*</span></label>
                    <input type="number" name="start" required value="1" min="1"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Sampai <span class="text-red-500">*</span></label>
                    <input type="number" name="end" required value="10" min="2"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Zero Padding</label>
                    <select name="padding"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all outline-none">
                        <option value="1">1 digit (1→9)</option>
                        <option value="2" selected>2 digit (01→99)</option>
                        <option value="3">3 digit (001→999)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Lantai <span class="text-red-500">*</span></label>
                    <input type="number" name="floor" required min="0" placeholder="1"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tipe Kamar <span class="text-red-500">*</span></label>
                    <select name="room_type_id" required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all outline-none">
                        <option value="">— pilih —</option>
                        @foreach($roomTypes as $rt)
                        <option value="{{ $rt->id }}">{{ $rt->code }} — {{ $rt->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-3 mt-4">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7M4 7c0-2 1-3 3-3h10c2 0 3 1 3 3M4 7h16"/></svg>
                    Generate Kamar
                </button>
                <button type="button" onclick="document.getElementById('addBulkForm').classList.add('hidden')"
                        class="text-sm text-gray-500 hover:text-gray-700 transition-colors">Batal</button>
            </div>
            <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
                <strong>Contoh:</strong> Prefix <code class="font-mono bg-amber-100 px-1 rounded">10</code>, Mulai <code class="font-mono bg-amber-100 px-1 rounded">1</code>, Sampai <code class="font-mono bg-amber-100 px-1 rounded">10</code>, Padding <code class="font-mono bg-amber-100 px-1 rounded">2</code> → menghasilkan kamar <strong>1001, 1002, ..., 1010</strong>
            </div>
        </form>
    </div>
</div>

{{-- Filter bar --}}
<div class="mb-4 flex items-center gap-3 flex-wrap">
    <span class="text-xs font-semibold text-gray-500">Filter:</span>
    <select id="filterType" onchange="filterTable()"
            class="rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none">
        <option value="">Semua Tipe</option>
        @foreach($roomTypes as $rt)
        <option value="type-{{ $rt->id }}">{{ $rt->code }} — {{ $rt->name }}</option>
        @endforeach
    </select>
    <select id="filterFloor" onchange="filterTable()"
            class="rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none">
        <option value="">Semua Lantai</option>
        @foreach($rooms->pluck('floor')->unique()->sort()->filter() as $f)
        <option value="floor-{{ $f }}">Lantai {{ $f }}</option>
        @endforeach
    </select>
    <button onclick="clearFilter()" class="text-xs text-primary-600 hover:underline">Reset</button>
</div>

{{-- Rooms Table --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="roomsTable">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-5 py-3 w-10">#</th>
                    <th class="px-5 py-3">Nomor</th>
                    <th class="px-5 py-3">Lantai</th>
                    <th class="px-5 py-3">Tipe</th>
                    <th class="px-5 py-3 text-center">FO Status</th>
                    <th class="px-5 py-3 text-center">HK Status</th>
                    <th class="px-5 py-3 text-center">Aktif</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($rooms as $i => $room)
                @php
                    $foColors = [
                        'vacant'    => 'bg-slate-100 text-slate-700',
                        'occupied'  => 'bg-blue-100 text-blue-700',
                        'reserved'  => 'bg-amber-100 text-amber-700',
                    ];
                    $hkColors = [
                        'clean'      => 'bg-emerald-100 text-emerald-700',
                        'dirty'      => 'bg-rose-100 text-rose-700',
                        'inspected'  => 'bg-violet-100 text-violet-700',
                        'out_of_order' => 'bg-gray-300 text-gray-700',
                    ];
                    $foLabel = ['vacant'=>'Kosong','occupied'=>'Isi','reserved'=>'Dipesan'];
                    $hkLabel = ['clean'=>'Bersih','dirty'=>'Kotor','inspected'=>'Inspeksi','out_of_order'=>'Rusak'];
                @endphp
                <tr class="hover:bg-gray-50/50 room-row"
                    data-type="type-{{ $room->room_type_id }}"
                    data-floor="floor-{{ $room->floor }}">
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $loop->iteration }}</td>
                    <td class="px-5 py-3">
                        <span class="font-mono font-semibold text-gray-900">{{ $room->number }}</span>
                        @if($room->notes)
                        <span class="block text-[10px] text-gray-400 truncate max-w-[120px]">{{ $room->notes }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs font-medium text-gray-600">{{ $room->floor ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-medium text-gray-800">{{ $room->roomType?->name ?? '—' }}</span>
                        <span class="ml-1 text-[10px] text-gray-400 font-mono">{{ $room->roomType?->code }}</span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold rounded-full px-2.5 py-0.5 {{ $foColors[$room->fo_status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $foLabel[$room->fo_status] ?? $room->fo_status }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold rounded-full px-2.5 py-0.5 {{ $hkColors[$room->hk_status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $hkLabel[$room->hk_status] ?? $room->hk_status }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        @if($room->is_active)
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-full px-2.5 py-0.5">Aktif</span>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-rose-700 bg-rose-50 rounded-full px-2.5 py-0.5">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-1.5">
                            <button onclick="openRoomEdit({{ Js::from([
                                'id' => $room->id, 'number' => $room->number,
                                'floor' => $room->floor, 'room_type_id' => $room->room_type_id,
                                'is_active' => $room->is_active, 'view' => $room->view,
                                'notes' => $room->notes,
                            ]) }})"
                                    class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('panel.settings.rooms.destroy', $room->id) }}"
                                  onsubmit="return confirm('Hapus kamar #{{ $room->number }}?')">
                                @csrf @method('DELETE')
                                <button class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-16 text-center text-sm text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        @if($roomTypes->isEmpty())
                        <strong>Tambah tipe kamar terlebih dahulu</strong> di halaman Tipe Kamar sebelum menambahkan kamar.
                        @else
                        Belum ada kamar. Klik <strong>Tambah Kamar</strong> atau <strong>Tambah Massal</strong> untuk mulai.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rooms->count() > 0)
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 text-xs text-gray-500">
        Total: <strong>{{ $rooms->count() }}</strong> kamar &middot;
        FO Kosong: <strong>{{ $rooms->where('fo_status', 'vacant')->count() }}</strong> &middot;
        FO Isi: <strong>{{ $rooms->where('fo_status', 'occupied')->count() }}</strong> &middot;
        HK Bersih: <strong>{{ $rooms->where('hk_status', 'clean')->count() }}</strong> &middot;
        HK Kotor: <strong>{{ $rooms->where('hk_status', 'dirty')->count() }}</strong>
    </div>
    @endif
</div>

{{-- Edit Room Modal --}}
<div id="editRoomModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md mx-4"
         onclick="event.stopPropagation()">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">Edit Kamar</h2>
            <button onclick="document.getElementById('editRoomModal').classList.add('hidden')"
                    class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="editRoomForm" method="POST" class="p-5 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nomor Kamar <span class="text-red-500">*</span></label>
                <input type="text" name="number" id="editRoomNumber" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Lantai</label>
                    <input type="number" name="floor" id="editRoomFloor" min="0"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tipe Kamar <span class="text-red-500">*</span></label>
                    <select name="room_type_id" id="editRoomType" required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                        @foreach($roomTypes as $rt)
                        <option value="{{ $rt->id }}">{{ $rt->code }} — {{ $rt->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">View</label>
                <input type="text" name="view" id="editRoomView"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Catatan</label>
                <input type="text" name="notes" id="editRoomNotes"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status</label>
                <div class="flex items-center gap-3 pt-2.5">
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="radio" name="is_active" value="1" id="editRoomActive1" class="w-4 h-4 text-primary-600 focus:ring-primary-400"> Aktif
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="radio" name="is_active" value="0" id="editRoomActive0" class="w-4 h-4 text-rose-500 focus:ring-rose-400"> Nonaktif
                    </label>
                </div>
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan
                </button>
                <button type="button" onclick="document.getElementById('editRoomModal').classList.add('hidden')"
                        class="text-sm text-gray-500 hover:text-gray-700 transition-colors">Batal</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function filterTable() {
        const typeVal = document.getElementById('filterType').value;
        const floorVal = document.getElementById('filterFloor').value;
        document.querySelectorAll('.room-row').forEach(function(row) {
            let show = true;
            if (typeVal && row.dataset.type !== typeVal) show = false;
            if (floorVal && row.dataset.floor !== floorVal) show = false;
            row.style.display = show ? '' : 'none';
        });
    }

    function clearFilter() {
        document.getElementById('filterType').value = '';
        document.getElementById('filterFloor').value = '';
        document.querySelectorAll('.room-row').forEach(function(row) {
            row.style.display = '';
        });
    }

    function openRoomEdit(data) {
        const modal = document.getElementById('editRoomModal');
        document.getElementById('editRoomForm').action = '{{ route("panel.settings.rooms.update", "") }}/' + data.id;
        document.getElementById('editRoomNumber').value = data.number;
        document.getElementById('editRoomFloor').value = data.floor ?? '';
        document.getElementById('editRoomType').value = data.room_type_id;
        document.getElementById('editRoomView').value = data.view ?? '';
        document.getElementById('editRoomNotes').value = data.notes ?? '';
        document.getElementById('editRoomActive1').checked = data.is_active ? true : false;
        document.getElementById('editRoomActive0').checked = !data.is_active;
        modal.style.display = 'flex';
    }

    document.getElementById('editRoomModal').addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
</script>
@endpush

@endsection
