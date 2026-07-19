@extends('panel.layout')
@section('title', 'New Laundry Order')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.pos.laundry.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">New Laundry Order</h1>
        <p class="text-sm text-gray-500">Create a laundry order for a guest</p>
    </div>
</div>

<form method="POST" action="{{ route('panel.pos.laundry.store') }}" x-data="laundryForm()" class="space-y-6 max-w-3xl">
    @csrf

    {{-- Room / Guest selector --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Guest & Room</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Room</label>
                <select name="room_id" required x-on:change="selectRoom($el.value)"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-100 focus:border-primary-400 outline-none">
                    <option value="">Select room...</option>
                    @foreach ($rooms as $r)
                    <option value="{{ $r->id }}" @selected($selectedRoom && $selectedRoom->id === $r->id)>Room {{ $r->number }} — {{ $r->roomType?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Guest</label>
                <select name="guest_id" required
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-100 focus:border-primary-400 outline-none">
                    <option value="">Select guest...</option>
                    @foreach ($guests as $g)
                    <option value="{{ $g->id }}">{{ $g->full_name ?? $g->first_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Laundry items --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-900">Laundry Items</h2>
            <button type="button" @click="addRow()" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg font-medium transition-colors">
                + Add Item
            </button>
        </div>

        <div class="space-y-2">
            <template x-for="(item, idx) in items" :key="idx">
                <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <select :name="'items['+idx+'][item]'" required
                            class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
                        @foreach ($laundryItems as $li)
                        <option value="{{ $li['key'] }}">{{ $li['name'] }}</option>
                        @endforeach
                    </select>
                    <select :name="'items['+idx+'][service]'" required
                            class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white w-32">
                        <option value="wash">Wash</option>
                        <option value="dry_clean">Dry Clean</option>
                        <option value="iron">Iron</option>
                    </select>
                    <input type="number" :name="'items['+idx+'][qty]'" value="1" min="1" max="999"
                           class="w-16 border border-gray-200 rounded-lg px-2 py-2 text-sm text-center bg-white">
                    <button type="button" @click="removeRow(idx)" class="text-red-400 hover:text-red-600 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
            <div x-show="items.length === 0" class="py-4 text-center text-gray-400 text-sm">
                <p>Add laundry items to this order.</p>
            </div>
        </div>
    </div>

    {{-- Pricing reference --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <h2 class="text-base font-semibold text-gray-900 mb-3">Price Reference</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-3 py-2 font-semibold text-gray-600">Item</th>
                        <th class="text-center px-3 py-2 font-semibold text-gray-600">Wash</th>
                        <th class="text-center px-3 py-2 font-semibold text-gray-600">Dry Clean</th>
                        <th class="text-center px-3 py-2 font-semibold text-gray-600">Iron</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($laundryItems as $li)
                    <tr>
                        <td class="px-3 py-2 font-medium text-gray-900">{{ $li['name'] }}</td>
                        <td class="px-3 py-2 text-center">{{ $li['wash'] ? 'Rp ' . number_format($li['wash'], 0, ',', '.') : '—' }}</td>
                        <td class="px-3 py-2 text-center">{{ $li['dry_clean'] ? 'Rp ' . number_format($li['dry_clean'], 0, ',', '.') : '—' }}</td>
                        <td class="px-3 py-2 text-center">{{ $li['iron'] ? 'Rp ' . number_format($li['iron'], 0, ',', '.') : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Notes + Submit --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" rows="2" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-100 focus:border-primary-400 outline-none" placeholder="Special instructions..."></textarea>
        </div>
        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-5 py-2.5 rounded-xl text-sm shadow-sm transition-colors"
                x-bind:disabled="items.length === 0">
            Create Laundry Order
        </button>
    </div>
</form>

<script>
function laundryForm() {
    return {
        items: [{ item: 'shirt', service: 'wash', qty: 1 }],
        addRow() { this.items.push({ item: 'shirt', service: 'wash', qty: 1 }); },
        removeRow(idx) { if (this.items.length > 1) this.items.splice(idx, 1); },
        selectRoom(val) {
            // Could AJAX fetch guests in room
        }
    };
}
</script>

@endsection
