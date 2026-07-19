@extends('panel.layout')
@section('title', 'Package Builder — ' . $package->name)
@section('content')
<div class="mb-6">
    <a href="{{ route('panel.packages.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali</a>
    <h2 class="text-xl font-bold text-slate-800 mt-1">{{ $package->name }}</h2>
    <p class="text-sm text-slate-500">Price: Rp {{ number_format($priceRange['min'], 0, ',', '.') }} — Rp {{ number_format($priceRange['max'], 0, ',', '.') }}</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
        <h3 class="font-semibold text-slate-800 mb-4">Included Items</h3>
        <div class="space-y-3">
            @foreach($package->items as $item)
            <div class="flex items-center justify-between py-2 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-700">{{ $item->name }}</p>
                        <p class="text-xs text-slate-400">{{ $item->item_type }} x{{ $item->quantity }}</p>
                    </div>
                </div>
                <span class="text-sm text-emerald-600 font-medium">{{ $item->is_included ? 'Included' : 'Rp ' . number_format($item->unit_price, 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
        <h3 class="font-semibold text-slate-800 mb-4">Customization Options</h3>
        @if(!empty($options) && $package->is_dynamic)
        <form id="customize-form" class="space-y-4">
            @foreach($options as $index => $opt)
            <label class="flex items-center justify-between py-2 border-b border-slate-100 cursor-pointer">
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ $opt['name'] ?? 'Option ' . ($index+1) }}</p>
                    <p class="text-xs text-slate-400">+Rp {{ number_format($opt['price_modifier'] ?? 0, 0, ',', '.') }}</p>
                </div>
                <input type="checkbox" name="options[{{ $index }}]" value="1" class="rounded border-slate-300 text-indigo-600">
            </label>
            @endforeach
        </form>
        <button onclick="applyCustomizations()" class="mt-4 w-full bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700">Apply Customizations</button>
        @else
        <p class="text-sm text-slate-400">This package is not dynamic. Contact admin for custom packages.</p>
        @endif
    </div>
</div>

@if($package->is_dynamic)
<script>
function applyCustomizations() {
    const form = document.getElementById('customize-form');
    const checkboxes = form.querySelectorAll('input[type="checkbox"]');
    const customizations = [];
    const options = @json($options);
    checkboxes.forEach((cb, i) => {
        if (cb.checked) {
            customizations.push({ type: options[i].type ?? 'addon', name: options[i].name, price_modifier: options[i].price_modifier ?? 0 });
        }
    });
    alert('Selected ' + customizations.length + ' customization(s). Total will be calculated on checkout.');
}
</script>
@endif
@endsection
