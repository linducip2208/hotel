@extends('panel.layout')
@section('title', 'Stok Minibar Per Kamar')
@section('content')

<div class="mb-6 flex items-center justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Stok Minibar Per Kamar</h1>
        <p class="text-sm text-gray-500 mt-0.5">Pantau dan kelola stok minibar di setiap kamar</p>
    </div>
    <a href="{{ route('panel.hk.minibar.products') }}" class="inline-flex items-center gap-2 bg-slate-600 hover:bg-slate-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
        Produk
    </a>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kamar</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                    @foreach($products as $prod)
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $prod->name }}</th>
                    @endforeach
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($rooms as $room)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <a href="{{ route('panel.hk.minibar.room-stock', $room->id) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                            {{ $room->room_number }}
                        </a>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">{{ $room->roomType?->name ?? '—' }}</td>
                    @foreach($products as $prod)
                    @php
                        $stock = $room->minibarStocks->firstWhere('minibar_product_id', $prod->id);
                        $current = $stock ? $stock->current_qty : 0;
                        $initial = $stock ? $stock->initial_qty : 3;
                        $color = $current <= 0 ? 'text-rose-600 bg-rose-50' : ($current < $initial ? 'text-amber-600 bg-amber-50' : 'text-emerald-600 bg-emerald-50');
                    @endphp
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full {{ $color }}">
                            {{ $current }}
                        </span>
                    </td>
                    @endforeach
                    <td class="px-4 py-3.5 text-center">
                        <a href="{{ route('panel.hk.minibar.room-stock', $room->id) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                            Kelola
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ 3 + $products->count() }}" class="py-10 text-center text-sm text-gray-400">Belum ada kamar terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
