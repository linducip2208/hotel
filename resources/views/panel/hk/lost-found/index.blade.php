@extends('panel.layout')
@section('title', 'Lost & Found')
@section('content')

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Lost & Found</h1>
            <p class="text-sm text-gray-500 mt-1">Track and manage lost items found on property</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('panel.hk.lost-found.export') }}"
               class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm font-medium transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export CSV
            </a>
            <a href="{{ route('panel.hk.lost-found.create') }}"
               class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Record Item
            </a>
        </div>
    </div>
</div>

{{-- Status Summary --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    @php
        $found    = $statusCounts['found'] ?? 0;
        $claimed  = $statusCounts['claimed'] ?? 0;
        $returned = $statusCounts['returned'] ?? 0;
        $disposed = $statusCounts['disposed'] ?? 0;
    @endphp
    <div class="bg-white rounded-2xl p-4 border border-amber-100 shadow-card text-center">
        <div class="text-2xl font-bold text-amber-600">{{ $found }}</div>
        <div class="text-xs text-amber-600 mt-0.5 font-medium">Found</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-blue-100 shadow-card text-center">
        <div class="text-2xl font-bold text-blue-600">{{ $claimed }}</div>
        <div class="text-xs text-blue-600 mt-0.5 font-medium">Claimed</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-card text-center">
        <div class="text-2xl font-bold text-emerald-600">{{ $returned }}</div>
        <div class="text-xs text-emerald-600 mt-0.5 font-medium">Returned</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-card text-center">
        <div class="text-2xl font-bold text-gray-500">{{ $disposed }}</div>
        <div class="text-xs text-gray-500 mt-0.5 font-medium">Disposed</div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4 mb-6">
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[140px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-700 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                <option value="">All Status</option>
                <option value="found" @selected(request('status') === 'found')>Found</option>
                <option value="claimed" @selected(request('status') === 'claimed')>Claimed</option>
                <option value="returned" @selected(request('status') === 'returned')>Returned</option>
                <option value="disposed" @selected(request('status') === 'disposed')>Disposed</option>
            </select>
        </div>
        <div class="flex-1 min-w-[140px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Location</label>
            <input type="text" name="location" value="{{ request('location') }}" placeholder="Filter by location..."
                   class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-700 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
        </div>
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Date From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-700 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
        </div>
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Date To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-700 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center gap-1.5 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Filter
            </button>
            <a href="{{ route('panel.hk.lost-found.index') }}" class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium transition">Clear</a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Item</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Location</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date Found</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Found By</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-5 py-3.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse ($items as $item)
                @php
                    $badge = match ($item->status) {
                        'found'    => 'bg-amber-100 text-amber-700',
                        'claimed'  => 'bg-blue-100 text-blue-700',
                        'returned' => 'bg-emerald-100 text-emerald-700',
                        'disposed' => 'bg-gray-100 text-gray-500',
                        default    => 'bg-gray-100 text-gray-500',
                    };
                    $label = match ($item->status) {
                        'found'    => 'Found',
                        'claimed'  => 'Claimed',
                        'returned' => 'Returned',
                        'disposed' => 'Disposed',
                        default    => ucfirst($item->status),
                    };
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            @if ($item->photo_path)
                            <img src="{{ Storage::url($item->photo_path) }}" class="w-10 h-10 rounded-lg object-cover border border-gray-200">
                            @else
                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            </div>
                            @endif
                            <div>
                                <span class="font-medium text-gray-900 block truncate max-w-[200px]">{{ $item->description }}</span>
                                @if ($item->room)
                                <span class="text-xs text-gray-400">Room {{ $item->room->number }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $item->found_location ?: '—' }}</td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $item->found_date->format('d M Y') }}</td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $item->foundByUser?->name ?: '—' }}</td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('panel.hk.lost-found.show', $item->id) }}"
                           class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-800 text-xs font-medium transition">
                            Detail
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            </div>
                            <p class="text-sm font-medium text-gray-700">No lost & found items</p>
                            <p class="text-xs text-gray-400">Record a new found item to get started</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if ($items->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
            {{ $items->links() }}
        </div>
    @endif
</div>

@endsection
