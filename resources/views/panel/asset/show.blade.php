@extends('panel.layout')
@section('title', 'Asset Details')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.asset.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900">{{ $asset->name }}</h1>
            @php
                $sc = match($asset->status) { 'operational' => 'emerald', 'under_maintenance' => 'amber', 'decommissioned' => 'red', default => 'gray' };
            @endphp
            <span class="text-xs font-semibold bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize shrink-0">
                {{ str_replace('_', ' ', $asset->status) }}
            </span>
        </div>
        <p class="text-sm text-gray-500 mt-0.5">
            <span class="font-mono">{{ $asset->asset_no }}</span> · {{ $asset->category }}
        </p>
    </div>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Asset details --}}
    <div class="md:col-span-2 space-y-5">

        {{-- Financial info --}}
        <div class="grid grid-cols-2 gap-5">
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Cost & Depreciation</div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Purchase Cost</span>
                        <span class="font-mono text-gray-800">Rp {{ number_format($asset->purchase_cost ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Useful Life</span>
                        <span class="text-gray-800">{{ $asset->useful_life_years }} years</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Monthly Dep.</span>
                        <span class="font-mono text-gray-800">Rp {{ number_format($asset->monthlyDepreciation(), 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-gray-100">
                        <span class="text-gray-500">Accumulated</span>
                        <span class="font-mono font-semibold text-red-600">Rp {{ number_format($asset->accumulated_depreciation, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Book Value</div>
                @php $bookValue = ($asset->purchase_cost ?? 0) - $asset->accumulated_depreciation; @endphp
                <div class="text-3xl font-bold text-gray-900 font-mono mb-1">
                    Rp {{ number_format(max(0, $bookValue), 0, ',', '.') }}
                </div>
                <div class="text-xs text-gray-400">Current estimated value</div>
            </div>
        </div>

        {{-- Work orders --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Work Orders</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse ($asset->workOrders as $wo)
                @php $wsc = match($wo->status) { 'open' => 'amber', 'in_progress' => 'blue', 'completed' => 'emerald', 'verified' => 'primary', default => 'gray' }; @endphp
                <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/60 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs text-gray-500">{{ $wo->wo_no }}</span>
                            <span class="text-sm font-medium text-gray-800 capitalize">{{ $wo->type }}</span>
                        </div>
                    </div>
                    <span class="text-xs font-medium bg-{{ $wsc }}-50 text-{{ $wsc }}-700 px-2.5 py-1 rounded-full capitalize shrink-0">
                        {{ str_replace('_', ' ', $wo->status) }}
                    </span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-sm text-gray-400">No work orders for this asset.</div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Quick info --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 h-fit">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Asset Info</div>
        <div class="space-y-3 text-sm">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Asset No</div>
                <div class="font-mono text-gray-800">{{ $asset->asset_no }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Category</div>
                <div class="text-gray-800 capitalize">{{ $asset->category }}</div>
            </div>
            @if ($asset->location)
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Location</div>
                <div class="text-gray-800">{{ $asset->location }}</div>
            </div>
            @endif
            @if ($asset->purchase_date)
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Purchased</div>
                <div class="text-gray-800">{{ $asset->purchase_date->format('d M Y') }}</div>
            </div>
            @endif
        </div>
    </div>

</div>

@endsection
