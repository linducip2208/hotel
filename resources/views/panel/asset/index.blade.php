@extends('panel.layout')
@section('title', 'Asset Register')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Asset Register</h1>
    <p class="text-sm text-gray-500 mt-0.5">Property fixtures, equipment, and maintenance tracking</p>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Asset table --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Assets</h2>
                <div class="flex gap-2">
                    <a href="{{ route('panel.asset.work-orders') }}"
                       class="text-xs font-medium text-amber-600 bg-amber-50 px-2.5 py-1 rounded-lg hover:bg-amber-100 transition-colors">Work Orders</a>
                    <a href="{{ route('panel.asset.ppm') }}"
                       class="text-xs font-medium text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg hover:bg-blue-100 transition-colors">PPM</a>
                </div>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse ($assets as $a)
                @php
                    $statusColors = ['active' => 'emerald', 'maintenance' => 'amber', 'disposed' => 'gray', 'inactive' => 'gray'];
                    $sc = $statusColors[$a->status ?? ''] ?? 'gray';
                @endphp
                <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/60 transition-colors">
                    <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('panel.asset.show', $a->id) }}"
                               class="text-sm font-semibold text-gray-900 hover:text-primary-600 transition-colors">{{ $a->name }}</a>
                        </div>
                        <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-400">
                            <span class="font-mono">{{ $a->asset_no }}</span>
                            <span>·</span>
                            <span>{{ $a->category }}</span>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-xs font-mono font-medium text-gray-700">Rp {{ number_format($a->purchase_cost ?? 0, 0, ',', '.') }}</div>
                        <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2 py-0.5 rounded-full capitalize mt-0.5 inline-block">{{ $a->status ?? 'active' }}</span>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                    <svg class="w-8 h-8 mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                    <p class="text-sm text-gray-500">No assets registered</p>
                </div>
                @endforelse
            </div>
            @if ($assets->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $assets->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Add asset form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Add Asset</h2>
        </div>
        <form method="POST" action="{{ route('panel.asset.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Split AC 1.5 PK"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Category <span class="text-red-500">*</span></label>
                <input type="text" name="category" value="{{ old('category') }}" required placeholder="AC, TV, Furniture…"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Room ID</label>
                <input type="number" name="room_id" value="{{ old('room_id') }}" placeholder="Optional"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Purchase Date</label>
                <input type="date" name="purchased_at" value="{{ old('purchased_at') }}"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Purchase Cost (Rp)</label>
                <input type="number" step="0.01" name="purchase_cost" value="{{ old('purchase_cost') }}" placeholder="0"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Useful Life (years)</label>
                <input type="number" name="useful_life_years" value="{{ old('useful_life_years') }}" placeholder="5"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Add Asset
            </button>
        </form>
    </div>

</div>

@endsection
