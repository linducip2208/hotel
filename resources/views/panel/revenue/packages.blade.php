@extends('panel.layout')
@section('title', 'Paket & Bundle')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Paket &amp; Bundle</h1>
    <p class="text-sm text-gray-500 mt-0.5">Dynamic packaging — kumpulkan kamar, spa, dining, transfer ke dalam satu paket harga</p>
</div>

<div x-data="{
    showForm: false,
    editingId: null,
    editName: '', editSlug: '', editDesc: '', editBasePrice: 0, editMinNights: 1, editMaxNights: '', editImageUrl: '', editDisplayOrder: 0,
    showItems: {},
    showItemForm: {},
    resetForm() {
        this.editingId = null;
        this.editName = ''; this.editSlug = ''; this.editDesc = ''; this.editBasePrice = 0;
        this.editMinNights = 1; this.editMaxNights = ''; this.editImageUrl = ''; this.editDisplayOrder = 0;
    },
    editPackage(pkg) {
        this.showForm = true; this.editingId = pkg.id;
        this.editName = pkg.name; this.editDesc = pkg.description || '';
        this.editBasePrice = pkg.base_price; this.editMinNights = pkg.min_nights;
        this.editMaxNights = pkg.max_nights || ''; this.editImageUrl = pkg.image_url || '';
        this.editDisplayOrder = pkg.display_order || 0;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}" class="space-y-6">

    {{-- Toggle Add/Edit Form --}}
    <button @click="showForm = !showForm; if(!showForm) resetForm()"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        <span x-text="editingId ? 'Edit Paket' : 'Tambah Paket'"></span>
    </button>

    {{-- Form --}}
    <div x-show="showForm" x-cloak x-transition class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <form :action="editingId ? '{{ route('panel.packages.index') }}/'+editingId : '{{ route('panel.packages.store') }}'"
              method="POST" class="grid md:grid-cols-2 gap-4">
            @csrf
            <template x-if="editingId">
                <input type="hidden" name="_method" value="PUT">
            </template>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Paket <span class="text-red-500">*</span></label>
                <input type="text" name="name" x-model="editName" required placeholder="Romantic Dinner Package"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Harga Dasar (Rp) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="base_price" x-model="editBasePrice" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Min Malam</label>
                <input type="number" name="min_nights" x-model="editMinNights" min="1"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Max Malam</label>
                <input type="number" name="max_nights" x-model="editMaxNights" min="1"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Deskripsi</label>
                <textarea name="description" x-model="editDesc" rows="2"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">URL Gambar</label>
                <input type="url" name="image_url" x-model="editImageUrl" placeholder="https://..."
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Urutan Tampil</label>
                <input type="number" name="display_order" x-model="editDisplayOrder" min="0"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div x-show="editingId" class="flex items-center gap-2 md:col-span-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="is_active" class="text-xs font-semibold text-gray-600">Aktif</label>
            </div>
            <div class="md:col-span-2 flex items-center gap-3">
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                    <span x-text="editingId ? 'Update Paket' : 'Simpan Paket'"></span>
                </button>
                <button type="button" @click="showForm = false; resetForm()"
                        class="text-sm text-gray-500 hover:text-gray-700 transition-colors">Batal</button>
            </div>
        </form>
    </div>

    {{-- Packages List --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        @if ($packages->isEmpty())
        <div class="flex flex-col items-center justify-center py-16">
            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-600">Belum ada paket</p>
            <p class="text-xs text-gray-400 mt-1">Klik "Tambah Paket" untuk membuat paket bundle pertama</p>
        </div>
        @else
        <div class="space-y-0 divide-y divide-gray-100">
            @foreach ($packages as $pkg)
            <div class="p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-1.5">
                            <h3 class="text-base font-semibold text-gray-900">{{ $pkg->name }}</h3>
                            @if ($pkg->is_active)
                            <span class="inline-flex items-center gap-1 text-[11px] bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full font-medium">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                            </span>
                            @else
                            <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium">Nonaktif</span>
                            @endif
                        </div>
                        @if ($pkg->description)
                        <p class="text-sm text-gray-500 mb-2 line-clamp-2">{{ $pkg->description }}</p>
                        @endif
                        <div class="flex items-center gap-4 text-xs text-gray-500 mt-2">
                            <span class="font-mono font-semibold text-indigo-700">Rp {{ number_format($pkg->base_price, 0, ',', '.') }}<span class="text-gray-400 font-normal">/malam</span></span>
                            <span class="bg-gray-100 px-2 py-0.5 rounded-md">{{ $pkg->min_nights }}–{{ $pkg->max_nights ?? '∞' }} malam</span>
                            <span class="bg-violet-50 text-violet-700 px-2 py-0.5 rounded-md font-medium">{{ $pkg->items_count }} item</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button @click="showItemForm['pkg'+{{ $pkg->id }}] = !(showItemForm['pkg'+{{ $pkg->id }}] || false)"
                                class="p-2 rounded-lg text-gray-400 hover:text-violet-600 hover:bg-violet-50 transition-colors"
                                title="Tambah Item">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        </button>
                        <button @click="editPackage({{ Js::from($pkg->only(['id','name','description','base_price','min_nights','max_nights','image_url','display_order'])) }})"
                                class="p-2 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <form action="{{ route('panel.packages.destroy', $pkg->id) }}" method="POST" onsubmit="return confirm('Hapus paket ini?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                        <button @click="showItems['pkg'+{{ $pkg->id }}] = !(showItems['pkg'+{{ $pkg->id }}] || false)"
                                class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors" title="Lihat Item">
                            <svg class="w-4 h-4 transition-transform" :class="(showItems['pkg'+{{ $pkg->id }}] || false) && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Add Item Form --}}
                <div x-show="showItemForm['pkg'+{{ $pkg->id }}] || false" x-cloak x-transition class="mt-4 ml-0 border-t border-gray-100 pt-4">
                    <form action="{{ route('panel.packages.items.store', $pkg->id) }}" method="POST" class="grid md:grid-cols-5 gap-3">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Tipe Item</label>
                            <select name="item_type" required
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                                <option value="room">Kamar</option><option value="spa">Spa</option><option value="dinner">Makan Malam</option>
                                <option value="breakfast">Sarapan</option><option value="transfer">Transfer</option>
                                <option value="activity">Aktivitas</option><option value="late_checkout">Late Checkout</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Nama Item <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required placeholder="Contoh: 60-min Balinese Massage"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Qty</label>
                            <input type="number" name="quantity" value="1" min="1"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Harga Satuan</label>
                            <input type="number" step="0.01" name="unit_price" value="0"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        </div>
                        <div class="md:col-span-5 flex items-center gap-4">
                            <label class="flex items-center gap-2 text-xs text-gray-600">
                                <input type="checkbox" name="is_included" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                Sudah termasuk harga paket
                            </label>
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Tambah Item
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Items Sub-table --}}
                <div x-show="showItems['pkg'+{{ $pkg->id }}] || false" x-cloak x-transition class="mt-4 ml-0 border-t border-gray-100 pt-3">
                    @php $pkgItems = $pkg->items; @endphp
                    @if ($pkgItems->isEmpty())
                    <p class="text-xs text-gray-400 py-3">Belum ada item. Klik <span class="text-violet-600 font-medium">+</span> untuk menambahkan.</p>
                    @else
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left py-2 font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                                <th class="text-left py-2 font-semibold text-gray-500 uppercase tracking-wide">Nama</th>
                                <th class="text-center py-2 font-semibold text-gray-500 uppercase tracking-wide">Qty</th>
                                <th class="text-right py-2 font-semibold text-gray-500 uppercase tracking-wide">Harga</th>
                                <th class="text-center py-2 font-semibold text-gray-500 uppercase tracking-wide">Termasuk</th>
                                <th class="text-right py-2 font-semibold text-gray-500 uppercase tracking-wide"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($pkgItems as $item)
                            <tr class="hover:bg-gray-50/60">
                                <td class="py-2">
                                    <span class="inline-flex text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded font-medium uppercase">{{ $item->item_type }}</span>
                                </td>
                                <td class="py-2 text-gray-800 font-medium">{{ $item->name }}</td>
                                <td class="py-2 text-center text-gray-600">{{ $item->quantity }}x</td>
                                <td class="py-2 text-right font-mono text-gray-700">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="py-2 text-center">
                                    @if ($item->is_included)
                                    <span class="text-emerald-600 font-medium">Ya</span>
                                    @else
                                    <span class="text-amber-600 font-medium">Addon</span>
                                    @endif
                                </td>
                                <td class="py-2 text-right">
                                    <form action="{{ route('panel.packages.items.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus item?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-rose-600 transition-colors p-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
