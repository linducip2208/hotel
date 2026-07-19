@extends('panel.layout')
@section('title', 'Packages')
@section('content')
<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-800">Packages & Bundles</h2>
    <p class="text-sm text-slate-500">Kelola package bundling dan dynamic packaging</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($packages as $package)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all overflow-hidden">
        @if($package->image_url)
        <img src="{{ $package->image_url }}" class="w-full h-40 object-cover">
        @else
        <div class="w-full h-40 bg-gradient-to-br from-indigo-50 to-violet-50 flex items-center justify-center">
            <svg class="w-12 h-12 text-indigo-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        @endif
        <div class="p-4">
            <h3 class="font-semibold text-slate-800">{{ $package->name }}</h3>
            <p class="text-sm text-slate-500 mt-1">{{ $package->description }}</p>
            <div class="flex items-center justify-between mt-3">
                <span class="text-lg font-bold text-indigo-700">Rp {{ number_format($package->base_price, 0, ',', '.') }}</span>
                @if($package->is_dynamic)
                <a href="{{ route('panel.packages.builder', $package->id) }}" class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700">Build</a>
                @else
                <span class="text-xs bg-slate-100 text-slate-500 px-3 py-1.5 rounded-lg">Fixed</span>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
