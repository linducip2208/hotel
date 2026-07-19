@extends('panel.layout')
@section('title', 'Add Linen Item')
@section('content')

<div class="max-w-lg">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('panel.hk.linen.index') }}"
           class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add Linen Item</h1>
            <p class="text-sm text-gray-500">Register new linen type for inventory tracking</p>
        </div>
    </div>

    <form method="POST" action="{{ route('panel.hk.linen.store') }}" class="bg-white rounded-2xl shadow-card border border-gray-100 p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. White King Sheet" required
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-100 focus:border-primary-400 outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select name="type" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-100 focus:border-primary-400 outline-none">
                <option value="">Select type...</option>
                <option value="bed_sheet" @selected(old('type') === 'bed_sheet')>Bed Sheet</option>
                <option value="pillow_case" @selected(old('type') === 'pillow_case')>Pillow Case</option>
                <option value="towel" @selected(old('type') === 'towel')>Towel</option>
                <option value="bathrobe" @selected(old('type') === 'bathrobe')>Bathrobe</option>
                <option value="blanket" @selected(old('type') === 'blanket')>Blanket</option>
                <option value="table_cloth" @selected(old('type') === 'table_cloth')>Table Cloth</option>
            </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Initial Stock (Par Base)</label>
                <input type="number" name="initial_stock" value="{{ old('initial_stock', 0) }}" min="0" required
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-100 focus:border-primary-400 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Stock</label>
                <input type="number" name="current_stock" value="{{ old('current_stock', 0) }}" min="0" required
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-100 focus:border-primary-400 outline-none">
            </div>
        </div>
        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-5 py-2.5 rounded-xl text-sm shadow-sm transition-colors">
            Save Linen Item
        </button>
    </form>
</div>

@endsection
