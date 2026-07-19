@extends('panel.layout')
@section('title', 'Create Purchase Request')
@section('content')

<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Create Purchase Request</h1>

    <form method="POST" action="{{ route('panel.inventory.pr.store') }}" class="bg-white rounded-2xl shadow-card border border-gray-100 p-6 space-y-4" x-data="{ lines: [{ description: '', quantity: 1, unit: 'pcs' }] }">
        @csrf
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Department</label>
                <input type="text" name="department" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Required Date</label>
                <input type="date" name="required_date" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
            </div>
        </div>

        <div class="border-t pt-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Line Items</h3>
                <button type="button" @click="lines.push({ description: '', quantity: 1, unit: 'pcs' })" class="text-xs text-primary-600 font-medium hover:underline">+ Add Line</button>
            </div>
            <template x-for="(line, i) in lines" :key="i">
                <div class="flex gap-2 mb-2">
                    <input type="text" :name="'lines['+i+'][description]'" placeholder="Description" required class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
                    <input type="number" :name="'lines['+i+'][quantity]'" step="0.001" min="0.001" x-model="line.quantity" class="w-20 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
                    <input type="text" :name="'lines['+i+'][unit]'" class="w-16 rounded-xl border border-gray-200 bg-gray-50 px-2 py-2 text-sm" x-model="line.unit">
                    <input type="number" :name="'lines['+i+'][estimated_price]'" step="0.01" placeholder="Price" class="w-24 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
                    <button type="button" @click="lines.splice(i,1)" x-show="lines.length > 1" class="text-red-400 hover:text-red-600 text-sm">✕</button>
                </div>
            </template>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Notes</label>
            <textarea name="notes" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm"></textarea>
        </div>

        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-3 rounded-xl shadow-sm transition-colors">Submit PR</button>
    </form>
</div>

@endsection
