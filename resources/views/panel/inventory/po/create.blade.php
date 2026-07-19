@extends('panel.layout')
@section('title', 'Create Purchase Order')
@section('content')

<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Create Purchase Order @if($pr) <span class="text-sm font-normal text-gray-500">from {{ $pr->pr_number }}</span> @endif</h1>

    <form method="POST" action="{{ route('panel.inventory.po.store') }}" class="bg-white rounded-2xl shadow-card border border-gray-100 p-6 space-y-4" x-data="{ lines: @if($pr) @json($pr->lines->map(fn($l) => ['description' => $l->description, 'quantity' => $l->quantity, 'unit_price' => $l->estimated_price ?? 0, 'stock_item_id' => $l->stock_item_id])) @else [{ description: '', quantity: 1, unit_price: 0 }] @endif }">
        @csrf
        @if($pr)<input type="hidden" name="pr_id" value="{{ $pr->id }}">@endif
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Vendor <span class="text-red-500">*</span></label>
                <select name="vendor_id" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
                    <option value="">Select vendor</option>
                    @foreach ($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Order Date <span class="text-red-500">*</span></label>
                <input type="date" name="order_date" required value="{{ now()->toDateString() }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Expected Date</label>
                <input type="date" name="expected_date" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
            </div>
        </div>

        <div class="border-t pt-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Line Items</h3>
                <button type="button" @click="lines.push({ description: '', quantity: 1, unit_price: 0 })" class="text-xs text-primary-600 font-medium hover:underline">+ Add Line</button>
            </div>
            <template x-for="(line, i) in lines" :key="i">
                <div class="flex gap-2 mb-2">
                    <input type="text" :name="'lines['+i+'][description]'" placeholder="Item description" required class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
                    <input type="number" :name="'lines['+i+'][quantity]'" step="0.001" min="0.001" class="w-20 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm" x-model="line.quantity">
                    <input type="number" :name="'lines['+i+'][unit_price]'" step="0.01" min="0" placeholder="Price" class="w-28 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm" x-model="line.unit_price">
                    <span class="text-xs text-gray-500 self-center px-2 font-mono" x-text="'Rp '+(line.quantity * line.unit_price).toLocaleString()"></span>
                    <button type="button" @click="lines.splice(i,1)" x-show="lines.length > 1" class="text-red-400 hover:text-red-600 text-sm">✕</button>
                </div>
            </template>
        </div>

        <div><label class="block text-xs font-semibold text-gray-600 mb-1">Notes</label><textarea name="notes" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm"></textarea></div>

        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-3 rounded-xl shadow-sm transition-colors">Create PO</button>
    </form>
</div>

@endsection
