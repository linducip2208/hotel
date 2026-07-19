@extends('panel.layout')
@section('title', 'Inventory')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Stock Inventory</h1>
    <p class="text-sm text-gray-500 mt-0.5">Linen, amenities, cleaning supplies & F&B raw materials</p>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Items table --}}
    <div class="md:col-span-2 space-y-4">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Stock Items</h2>
                @php $lowStock = $items->filter(fn($i) => $i->current_qty <= $i->reorder_point)->count(); @endphp
                @if ($lowStock > 0)
                <span class="text-xs font-medium bg-amber-50 text-amber-700 px-2.5 py-1 rounded-full">
                    {{ $lowStock }} low stock
                </span>
                @endif
            </div>
            <div class="divide-y divide-gray-50">
                @forelse ($items as $i)
                @php
                    $isLow = $i->current_qty <= $i->reorder_point;
                    $catColors = ['linen' => 'blue', 'amenity' => 'violet', 'cleaning' => 'emerald', 'fnb_raw' => 'orange', 'other' => 'gray'];
                    $cc = $catColors[$i->category] ?? 'gray';
                @endphp
                <div class="flex items-center gap-4 px-5 py-3.5 {{ $isLow ? 'bg-amber-50/50' : 'hover:bg-gray-50/60' }} transition-colors">
                    <div class="w-9 h-9 rounded-xl bg-{{ $cc }}-50 flex items-center justify-center shrink-0">
                        <span class="text-xs font-bold text-{{ $cc }}-600">{{ strtoupper(substr($i->category, 0, 2)) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-900">{{ $i->name }}</span>
                            @if ($isLow)
                            <span class="text-xs font-medium bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Low Stock</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-400">
                            <span class="font-mono">{{ $i->sku }}</span>
                            <span>·</span>
                            <span class="capitalize">{{ str_replace('_', ' ', $i->category) }}</span>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-sm font-semibold {{ $isLow ? 'text-amber-700' : 'text-gray-900' }} tabular-nums">
                            {{ number_format($i->current_qty, 2) }} <span class="text-xs font-normal text-gray-500">{{ $i->uom }}</span>
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5">reorder at {{ number_format($i->reorder_point, 2) }}</div>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                    <svg class="w-8 h-8 mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <p class="text-sm text-gray-500">No stock items</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Sidebar forms --}}
    <div class="space-y-4">

        {{-- Add item --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
            <div class="px-5 py-4">
                <h2 class="text-sm font-semibold text-gray-700">Add Item</h2>
            </div>
            <form method="POST" action="{{ route('panel.inventory.store') }}" class="p-5 space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">SKU <span class="text-red-500">*</span></label>
                    <input type="text" name="sku" required placeholder="e.g. LIN-001"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Bath Towel"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Category <span class="text-red-500">*</span></label>
                    <select name="category" required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        <option value="linen">Linen</option>
                        <option value="amenity">Amenity</option>
                        <option value="cleaning">Cleaning</option>
                        <option value="fnb_raw">F&B Raw</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">UOM</label>
                        <input type="text" name="uom" value="pcs" placeholder="pcs"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Reorder At</label>
                        <input type="number" step="0.01" name="reorder_point" placeholder="10"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                </div>
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2 rounded-xl transition-colors">Add Item</button>
            </form>
        </div>

        {{-- Movement --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
            <div class="px-5 py-4">
                <h2 class="text-sm font-semibold text-gray-700">Log Movement</h2>
            </div>
            <form method="POST" action="{{ route('panel.inventory.movements.store') }}" class="p-5 space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Item ID <span class="text-red-500">*</span></label>
                    <input type="number" name="stock_item_id" required placeholder="Enter item ID"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Type <span class="text-red-500">*</span></label>
                    <select name="movement_type" required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        <option value="in">In (purchase)</option>
                        <option value="out">Out (used)</option>
                        <option value="adjust">Adjust</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Quantity <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="qty" required placeholder="0"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2 rounded-xl transition-colors">Save Movement</button>
            </form>
        </div>

    </div>

</div>

@endsection
