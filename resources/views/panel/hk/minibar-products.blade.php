@extends('panel.layout')
@section('title', 'Produk Minibar')
@section('content')

<div class="mb-6 flex items-center justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Produk Minibar</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola daftar produk minibar yang tersedia di setiap kamar</p>
    </div>
    <a href="{{ route('panel.hk.minibar.rooms') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        Stok Per Kamar
    </a>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Product Table --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Produk</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">SKU</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Harga Jual</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Harga Beli</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($products as $p)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5 text-sm font-semibold text-gray-900">{{ $p->name }}</td>
                            <td class="px-4 py-3.5 text-sm text-gray-600">
                                @if($p->category === 'beverage') Minuman
                                @elseif($p->category === 'snack') Camilan
                                @elseif($p->category === 'alcohol') Alkohol
                                @else Lainnya
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-sm font-mono text-gray-500">{{ $p->sku ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-right text-sm font-mono text-emerald-700">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3.5 text-right text-sm font-mono text-gray-500">Rp {{ number_format($p->cost_price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3.5 text-center">
                                @if($p->is_active)
                                <span class="inline-flex items-center gap-1 text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                                @else
                                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <button onclick="editProduct({{ $p->id }}, '{{ addslashes($p->name) }}', '{{ $p->category }}', {{ $p->selling_price }}, {{ $p->cost_price }}, '{{ $p->sku }}', {{ $p->is_active ? 'true' : 'false' }})"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium mr-2">Edit</button>
                                <form method="POST" action="{{ route('panel.hk.minibar.products.destroy', $p->id) }}" class="inline" onsubmit="return confirm('Hapus produk ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-rose-600 hover:text-rose-800 font-medium">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-sm text-gray-400">Belum ada produk minibar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add/Edit Form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700" id="formTitle">Tambah Produk</h2>
        </div>
        <form id="productForm" method="POST" action="{{ route('panel.hk.minibar.products.store') }}" class="p-5 space-y-3">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="id" id="productId" value="">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Produk <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="prodName" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kategori</label>
                <select name="category" id="prodCategory"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="beverage">Minuman</option>
                    <option value="snack">Camilan</option>
                    <option value="alcohol">Alkohol</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="selling_price" id="prodPrice" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Harga Beli (Rp)</label>
                <input type="number" step="0.01" name="cost_price" id="prodCost"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">SKU</label>
                <input type="text" name="sku" id="prodSku"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div id="activeField" class="hidden">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="prodActive" value="1" checked class="rounded border-gray-300 text-indigo-600">
                    <span class="text-xs font-semibold text-gray-600">Aktif</span>
                </label>
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Simpan
            </button>
            <button type="button" onclick="resetForm()"
                    class="w-full bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold py-2.5 rounded-xl transition-colors hidden" id="btnCancel">
                Batal
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editProduct(id, name, category, price, cost, sku, active) {
    document.getElementById('formTitle').textContent = 'Edit Produk';
    document.getElementById('productForm').action = '{{ route('panel.hk.minibar.products.update', '') }}/' + id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('productId').value = id;
    document.getElementById('prodName').value = name;
    document.getElementById('prodCategory').value = category;
    document.getElementById('prodPrice').value = price;
    document.getElementById('prodCost').value = cost;
    document.getElementById('prodSku').value = sku;
    document.getElementById('prodActive').checked = active;
    document.getElementById('activeField').classList.remove('hidden');
    document.getElementById('btnCancel').classList.remove('hidden');
}

function resetForm() {
    document.getElementById('formTitle').textContent = 'Tambah Produk';
    document.getElementById('productForm').action = '{{ route('panel.hk.minibar.products.store') }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('productId').value = '';
    document.getElementById('prodName').value = '';
    document.getElementById('prodCategory').value = 'beverage';
    document.getElementById('prodPrice').value = '';
    document.getElementById('prodCost').value = '';
    document.getElementById('prodSku').value = '';
    document.getElementById('activeField').classList.add('hidden');
    document.getElementById('btnCancel').classList.add('hidden');
}
</script>
@endpush

@endsection
