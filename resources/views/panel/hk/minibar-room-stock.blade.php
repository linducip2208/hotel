@extends('panel.layout')
@section('title', 'Stok Minibar — ' . $room->room_number)
@section('content')

<div class="mb-6 flex items-center justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Stok Minibar — Kamar {{ $room->room_number }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $room->roomType?->name ?? 'Tanpa Tipe' }} · Lantai {{ $room->floor ?? '—' }}</p>
    </div>
    <a href="{{ route('panel.hk.minibar.rooms') }}" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Stock Grid --}}
    <div class="lg:col-span-2 space-y-5">
        {{-- Current Stock --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Stok Saat Ini</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Produk</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Stok Awal</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Stok Saat Ini</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Terpakai</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Harga</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($stocks as $s)
                        @php $consumed = $s->initial_qty - $s->current_qty; @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5 text-sm font-semibold text-gray-900">{{ $s->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-center text-sm text-gray-600">{{ $s->initial_qty }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full {{ $s->current_qty <= 0 ? 'text-rose-600 bg-rose-50' : ($consumed > 0 ? 'text-amber-600 bg-amber-50' : 'text-emerald-600 bg-emerald-50') }}">
                                    {{ $s->current_qty }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center text-sm {{ $consumed > 0 ? 'text-rose-600' : 'text-gray-400' }}">
                                {{ $consumed > 0 ? '-'.$consumed : '0' }}
                            </td>
                            <td class="px-4 py-3.5 text-right text-sm font-mono text-emerald-700">Rp {{ number_format($s->product?->selling_price ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-sm text-gray-400">Belum ada stok minibar di kamar ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Consumption History --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Riwayat Konsumsi</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Produk</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Qty</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tamu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($consumptions as $c)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5 text-sm text-gray-600">{{ $c->consumption_date?->format('d M Y') }}</td>
                            <td class="px-4 py-3.5 text-sm font-semibold text-gray-900">{{ $c->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-center text-sm text-gray-600">{{ $c->qty }}</td>
                            <td class="px-4 py-3.5 text-right font-mono text-sm text-emerald-700">Rp {{ number_format($c->total_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3.5 text-sm text-gray-600">{{ $c->reservation?->primaryGuest?->full_name ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-sm text-gray-400">Belum ada riwayat konsumsi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Sidebar Actions --}}
    <div class="space-y-5">
        {{-- Record Consumption --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
            <div class="px-5 py-4">
                <h2 class="text-sm font-semibold text-gray-700">Catat Konsumsi</h2>
            </div>
            <form method="POST" action="{{ route('panel.hk.minibar.consume') }}" class="p-5 space-y-3">
                @csrf
                <input type="hidden" name="room_id" value="{{ $room->id }}">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Reservasi <span class="text-red-500">*</span></label>
                    <input type="number" name="reservation_id" required placeholder="ID Reservasi"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Item</label>
                    <div class="space-y-2" id="consumeItems">
                        <div class="flex gap-2">
                            <select name="items[0][product_id]"
                                    class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                                <option value="">Pilih Produk</option>
                                @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (Rp {{ number_format($p->selling_price, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                            <input type="number" name="items[0][qty]" value="1" min="1" placeholder="Qty"
                                   class="w-20 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        </div>
                    </div>
                    <button type="button" onclick="addConsumeItem()" class="text-xs text-indigo-600 hover:text-indigo-800 mt-2">+ Tambah Item</button>
                </div>
                <button type="submit"
                        class="w-full bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                    Catat & Tagih ke Folio
                </button>
            </form>
        </div>

        {{-- Restock --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
            <div class="px-5 py-4">
                <h2 class="text-sm font-semibold text-gray-700">Isi Ulang Stok</h2>
            </div>
            <form method="POST" action="{{ route('panel.hk.minibar.restock') }}" class="p-5 space-y-3">
                @csrf
                <input type="hidden" name="room_id" value="{{ $room->id }}">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Item</label>
                    <div class="space-y-2" id="restockItems">
                        <div class="flex gap-2">
                            <select name="items[0][product_id]"
                                    class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                                <option value="">Pilih Produk</option>
                                @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="items[0][qty]" value="3" min="1" placeholder="Qty"
                                   class="w-20 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        </div>
                    </div>
                    <button type="button" onclick="addRestockItem()" class="text-xs text-indigo-600 hover:text-indigo-800 mt-2">+ Tambah Item</button>
                </div>
                <button type="submit"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                    Isi Ulang Stok
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let consumeIdx = 1;
function addConsumeItem() {
    const div = document.getElementById('consumeItems');
    const row = document.createElement('div');
    row.className = 'flex gap-2';
    row.innerHTML = `
        <select name="items[${consumeIdx}][product_id]"
                class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            <option value="">Pilih Produk</option>
            @foreach($products as $p)
            <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>
        <input type="number" name="items[${consumeIdx}][qty]" value="1" min="1" placeholder="Qty"
               class="w-20 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
    `;
    div.appendChild(row);
    consumeIdx++;
}

let restockIdx = 1;
function addRestockItem() {
    const div = document.getElementById('restockItems');
    const row = document.createElement('div');
    row.className = 'flex gap-2';
    row.innerHTML = `
        <select name="items[${restockIdx}][product_id]"
                class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            <option value="">Pilih Produk</option>
            @foreach($products as $p)
            <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>
        <input type="number" name="items[${restockIdx}][qty]" value="3" min="1" placeholder="Qty"
               class="w-20 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
    `;
    div.appendChild(row);
    restockIdx++;
}
</script>
@endpush

@endsection
