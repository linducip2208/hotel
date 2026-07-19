@extends('panel.layout')
@section('title', 'Vendor')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Vendor</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manajemen vendor, kontrak, dan riwayat pembelian</p>
    </div>
    <button onclick="document.getElementById('addVendorModal').__x.$data.open=true"
            class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-sm transition-colors flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Tambah Vendor
    </button>
</div>

@if (session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show=false, 4000)"
     class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 mb-5 text-sm flex items-center gap-2">
    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Filter & Search --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 px-5 py-3 mb-5 flex items-center gap-4">
    <form method="GET" class="flex-1 relative">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari vendor..."
               class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-9 pr-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </form>
    @if(request('category'))
    <a href="?" class="text-xs text-primary-600 hover:underline">Hapus filter</a>
    @endif
</div>

{{-- Category Quick Filters --}}
<div class="flex flex-wrap gap-2 mb-5">
    @foreach(['maintenance' => 'Perawatan', 'supplies' => 'Suplai', 'fnb' => 'F&B', 'laundry' => 'Laundry', 'cleaning' => 'Kebersihan', 'it' => 'IT', 'other' => 'Lainnya'] as $cat => $label)
    <a href="?category={{ $cat }}"
       class="text-xs font-medium px-3 py-1.5 rounded-lg {{ request('category') === $cat ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} transition-colors">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- Vendor Table --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-700">Daftar Vendor</h2>
        <span class="text-xs text-gray-400">{{ $vendors->total() }} vendor</span>
    </div>
    <div class="divide-y divide-gray-50">
        @forelse($vendors as $v)
        @php
            $catColors = [
                'maintenance' => 'bg-amber-50 text-amber-700',
                'supplies' => 'bg-blue-50 text-blue-700',
                'fnb' => 'bg-emerald-50 text-emerald-700',
                'laundry' => 'bg-violet-50 text-violet-700',
                'cleaning' => 'bg-sky-50 text-sky-700',
                'it' => 'bg-indigo-50 text-indigo-700',
                'other' => 'bg-gray-50 text-gray-600',
            ];
            $catLabel = [
                'maintenance' => 'Perawatan', 'supplies' => 'Suplai', 'fnb' => 'F&B',
                'laundry' => 'Laundry', 'cleaning' => 'Kebersihan', 'it' => 'IT', 'other' => 'Lainnya',
            ];
        @endphp
        <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/60 transition-colors">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold shrink-0">
                {{ strtoupper(substr($v->name, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <a href="{{ route('panel.inventory.vendors.show', $v->id) }}" class="text-sm font-semibold text-gray-900 hover:text-primary-600 transition-colors">{{ $v->name }}</a>
                    @if(!$v->is_active)
                    <span class="text-[10px] bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded-full">Nonaktif</span>
                    @endif
                </div>
                <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-400">
                    <span>{{ $v->contact_person ?: '-' }}</span>
                    @if($v->phone)
                    <span>&middot;</span>
                    <span>{{ $v->phone }}</span>
                    @endif
                </div>
            </div>
            <div>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $catColors[$v->category] ?? 'bg-gray-50 text-gray-500' }}">
                    {{ $catLabel[$v->category] ?? $v->category }}
                </span>
            </div>
            <div class="flex items-center gap-1 shrink-0">
                <a href="{{ route('panel.inventory.vendors.show', $v->id) }}"
                   class="text-xs font-medium text-primary-600 bg-primary-50 px-2.5 py-1 rounded-lg hover:bg-primary-100 transition-colors">Detail</a>
                <button onclick="openEditModal({{ $v->id }}, '{{ $v->name }}', '{{ $v->category }}', '{{ $v->contact_person }}', '{{ $v->phone }}', '{{ $v->email }}', '{{ $v->address }}', '{{ $v->tax_id }}', '{{ $v->payment_terms_days }}', {{ $v->is_active ? 'true' : 'false' }})"
                        class="text-xs font-medium text-amber-600 bg-amber-50 px-2.5 py-1 rounded-lg hover:bg-amber-100 transition-colors">Edit</a>
                <form method="POST" action="{{ route('panel.inventory.vendors.toggle', $v->id) }}" class="inline">
                    @csrf
                    <button class="text-xs text-gray-400 hover:text-gray-600 px-1.5 py-1">{{ $v->is_active ? '⏸' : '▶' }}</button>
                </form>
                <form method="POST" action="{{ route('panel.inventory.vendors.destroy', $v->id) }}" onsubmit="return confirm('Hapus vendor ini?')" class="inline">
                    @csrf @method('DELETE')
                    <button class="text-xs font-medium text-red-600 bg-red-50 px-2.5 py-1 rounded-lg hover:bg-red-100 transition-colors">Hapus</button>
                </form>
            </div>
        </div>
        @empty
        <div class="flex flex-col items-center justify-center py-10 text-gray-400">
            <svg class="w-8 h-8 mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <p class="text-sm text-gray-500">Belum ada vendor</p>
        </div>
        @endforelse
    </div>
    @if($vendors->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
        {{ $vendors->links() }}
    </div>
    @endif
</div>

{{-- Add Vendor Modal --}}
<div id="addVendorModal" x-data="{ open: false }" x-show="open" x-cloak
     x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open=false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Tambah Vendor</h3>
            <form method="POST" action="{{ route('panel.inventory.vendors.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Vendor <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                    <select name="category" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        <option value="maintenance">Perawatan</option>
                        <option value="supplies">Suplai</option>
                        <option value="fnb">F&B</option>
                        <option value="laundry">Laundry</option>
                        <option value="cleaning">Kebersihan</option>
                        <option value="it">IT</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kontak Person</label>
                        <input type="text" name="contact_person" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Telepon</label>
                        <input type="text" name="phone" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email</label>
                    <input type="email" name="email" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Alamat</label>
                    <textarea name="address" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">NPWP</label>
                        <input type="text" name="tax_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Term Pembayaran (hari)</label>
                        <input type="number" name="payment_terms_days" value="30" min="0" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="button" @click="open=false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-2.5 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Vendor Modal --}}
<div id="editVendorModal" x-data="{ open: false, vid: null, vactive: true }" x-show="open" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open=false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Edit Vendor</h3>
            <form method="POST" :action="'/panel/inventory/vendors/' + vid" class="space-y-3">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Vendor <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="edit_name" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                    <select name="category" id="edit_category" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        <option value="maintenance">Perawatan</option>
                        <option value="supplies">Suplai</option>
                        <option value="fnb">F&B</option>
                        <option value="laundry">Laundry</option>
                        <option value="cleaning">Kebersihan</option>
                        <option value="it">IT</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kontak Person</label>
                        <input type="text" name="contact_person" id="edit_contact" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Telepon</label>
                        <input type="text" name="phone" id="edit_phone" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email</label>
                    <input type="email" name="email" id="edit_email" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Alamat</label>
                    <textarea name="address" id="edit_address" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">NPWP</label>
                        <input type="text" name="tax_id" id="edit_tax_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Term Pembayaran (hari)</label>
                        <input type="number" name="payment_terms_days" id="edit_terms" min="0" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                </div>
                <div>
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="is_active" value="1" id="edit_active" class="rounded">
                        Vendor Aktif
                    </label>
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="button" @click="open=false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-2.5 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(id, name, category, contact, phone, email, address, taxId, terms, isActive) {
    const modal = document.getElementById('editVendorModal');
    modal.__x.$data.vid = id;
    modal.__x.$data.vactive = isActive;
    modal.__x.$data.open = true;
    setTimeout(() => {
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_category').value = category;
        document.getElementById('edit_contact').value = contact;
        document.getElementById('edit_phone').value = phone;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_address').value = address;
        document.getElementById('edit_tax_id').value = taxId;
        document.getElementById('edit_terms').value = terms;
        document.getElementById('edit_active').checked = isActive;
    }, 50);
}
</script>

@endsection
