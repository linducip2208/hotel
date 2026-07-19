@extends('panel.layout')
@section('title', 'Tipe Kamar')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Tipe Kamar</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola tipe kamar, harga dasar, dan fasilitas</p>
    </div>
    <button onclick="document.getElementById('addForm').classList.toggle('hidden')"
            class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Tambah Tipe
    </button>
</div>

{{-- Inline Add Form --}}
<div id="addForm" class="hidden mb-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Tambah Tipe Kamar Baru</h2>
        </div>
        <form method="POST" action="{{ route('panel.settings.room-types.store') }}" class="p-5">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kode <span class="text-red-500">*</span></label>
                    <input type="text" name="code" required placeholder="e.g. SUP, DLX"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 font-mono focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Harga Dasar <span class="text-red-500">*</span></label>
                    <input type="number" name="base_rate" required min="0"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Max Huni <span class="text-red-500">*</span></label>
                    <input type="number" name="max_occupancy" required value="2" min="1" max="20"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Konfigurasi Bed</label>
                    <input type="text" name="bed_config" placeholder="e.g. 1 King, 2 Single"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Ukuran (m²)</label>
                    <input type="number" name="size_sqm" min="0"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
            </div>
            <div class="flex items-center gap-3 mt-4">
                <label class="block text-xs font-semibold text-gray-600">Fasilitas</label>
                <div class="flex flex-wrap gap-2">
                    @php $amenityOptions = ['AC', 'TV LED', 'WiFi', 'Mini Bar', 'Hair Dryer', 'Safe Deposit Box', 'Bathtub', 'Shower', 'Coffee Maker', 'Iron', 'Balkon', 'Sofa Bed']; @endphp
                    @foreach($amenityOptions as $am)
                    <label class="flex items-center gap-1.5 text-xs text-gray-600 bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 cursor-pointer hover:border-primary-300 transition-colors">
                        <input type="checkbox" name="amenities[]" value="{{ $am }}" class="w-3.5 h-3.5 rounded text-primary-600 focus:ring-primary-400">
                        {{ $am }}
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="mt-4">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Simpan Tipe Kamar
                </button>
                <button type="button" onclick="document.getElementById('addForm').classList.add('hidden')"
                        class="text-sm text-gray-500 hover:text-gray-700 transition-colors ml-3">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- Room Types Table --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-5 py-3">Kode</th>
                    <th class="px-5 py-3">Nama</th>
                    <th class="px-5 py-3 text-right">Harga Dasar</th>
                    <th class="px-5 py-3 text-center">Max Huni</th>
                    <th class="px-5 py-3">Bed Config</th>
                    <th class="px-5 py-3 text-center">Aktif</th>
                    <th class="px-5 py-3 text-center">Urutan</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($roomTypes as $rt)
                @php $rtData = [
                    'id'             => $rt->id,
                    'name'           => e($rt->name),
                    'code'           => e($rt->code),
                    'base_rate'      => $rt->base_rate,
                    'max_occupancy'  => $rt->max_occupancy,
                    'max_adults'     => $rt->max_adults,
                    'max_children'   => $rt->max_children,
                    'size_sqm'       => $rt->size_sqm,
                    'view'           => e($rt->view ?? ''),
                    'bed_config'     => e($rt->bed_config ?? ''),
                    'description'    => e($rt->description ?? ''),
                    'display_order'  => $rt->display_order,
                    'is_active'      => $rt->is_active,
                    'amenities'      => $rt->amenities ?? [],
                ]; @endphp
                <tr class="hover:bg-gray-50/50">
                    <td class="px-5 py-3 font-mono font-semibold text-primary-700">{{ $rt->code }}</td>
                    <td class="px-5 py-3 font-medium text-gray-900">{{ $rt->name }}</td>
                    <td class="px-5 py-3 text-right font-mono text-sm font-medium">{{ number_format($rt->base_rate, 0, ',', '.') }}</td>
                    <td class="px-5 py-3 text-center">{{ $rt->max_occupancy }}</td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $rt->bed_config }}</td>
                    <td class="px-5 py-3 text-center">
                        @if($rt->is_active)
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-full px-2.5 py-0.5">Aktif</span>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-rose-700 bg-rose-50 rounded-full px-2.5 py-0.5">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-center text-xs text-gray-400">{{ $rt->display_order }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-1.5">
                            <button onclick="openEditModal({{ Js::from($rtData) }})"
                                    class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                                    title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('panel.settings.room-types.destroy', $rt->id) }}"
                                  onsubmit="return confirm('Hapus tipe kamar {{ $rt->name }}?')">
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
                        Belum ada tipe kamar. Klik <strong>Tambah Tipe</strong> untuk mulai.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-data>
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-xl mx-4 max-h-[90vh] overflow-y-auto"
         @click.outside="document.getElementById('editModal').classList.add('hidden')">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">Edit Tipe Kamar</h2>
            <button onclick="document.getElementById('editModal').classList.add('hidden')"
                    class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="editForm" method="POST" class="p-5 space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="editName" required
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Harga Dasar <span class="text-red-500">*</span></label>
                    <input type="number" name="base_rate" id="editBaseRate" required min="0"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Max Huni <span class="text-red-500">*</span></label>
                    <input type="number" name="max_occupancy" id="editOcc" required min="1" max="20"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Max Dewasa</label>
                    <input type="number" name="max_adults" id="editAdults" min="1" max="20"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Max Anak</label>
                    <input type="number" name="max_children" id="editChildren" min="0" max="20"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Ukuran (m²)</label>
                    <input type="number" name="size_sqm" id="editSize" min="0"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">View</label>
                    <input type="text" name="view" id="editView"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Konfigurasi Bed</label>
                    <input type="text" name="bed_config" id="editBed"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Deskripsi</label>
                    <textarea name="description" id="editDesc" rows="2"
                              class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none resize-none"></textarea>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Fasilitas</label>
                    <div id="editAmenities" class="flex flex-wrap gap-2"></div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Urutan Tampil</label>
                    <input type="number" name="display_order" id="editOrder" min="0"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status</label>
                    <div class="flex items-center gap-3 pt-2.5">
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="radio" name="is_active" value="1" id="editActive1" class="w-4 h-4 text-primary-600 focus:ring-primary-400"> Aktif
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="radio" name="is_active" value="0" id="editActive0" class="w-4 h-4 text-rose-500 focus:ring-rose-400"> Nonaktif
                        </label>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan
                </button>
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                        class="text-sm text-gray-500 hover:text-gray-700 transition-colors">Batal</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const amenityOptions = ['AC', 'TV LED', 'WiFi', 'Mini Bar', 'Hair Dryer', 'Safe Deposit Box', 'Bathtub', 'Shower', 'Coffee Maker', 'Iron', 'Balkon', 'Sofa Bed'];

    function openEditModal(rt) {
        const modal = document.getElementById('editModal');
        document.getElementById('editForm').action = '{{ route("panel.settings.room-types.update", "") }}/' + rt.id;
        document.getElementById('editName').value = rt.name;
        document.getElementById('editBaseRate').value = rt.base_rate;
        document.getElementById('editOcc').value = rt.max_occupancy;
        document.getElementById('editAdults').value = rt.max_adults ?? '';
        document.getElementById('editChildren').value = rt.max_children ?? '';
        document.getElementById('editSize').value = rt.size_sqm ?? '';
        document.getElementById('editView').value = rt.view ?? '';
        document.getElementById('editBed').value = rt.bed_config ?? '';
        document.getElementById('editDesc').value = rt.description ?? '';
        document.getElementById('editOrder').value = rt.display_order ?? 0;
        document.getElementById('editActive1').checked = rt.is_active ? true : false;
        document.getElementById('editActive0').checked = !rt.is_active;

        const amContainer = document.getElementById('editAmenities');
        amContainer.innerHTML = '';
        const selectedAmenities = Array.isArray(rt.amenities) ? rt.amenities : [];
        amenityOptions.forEach(function(am) {
            const checked = selectedAmenities.includes(am) ? 'checked' : '';
            amContainer.innerHTML +=
                '<label class="flex items-center gap-1.5 text-xs text-gray-600 bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 cursor-pointer hover:border-primary-300 transition-colors">' +
                '<input type="checkbox" name="amenities[]" value="' + am + '" ' + checked + ' class="w-3.5 h-3.5 rounded text-primary-600 focus:ring-primary-400"> ' +
                am + '</label>';
        });

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
</script>
@endpush

@endsection
