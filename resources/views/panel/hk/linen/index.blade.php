@extends('panel.layout')
@section('title', 'Linen & Supply')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Linen & Supply Tracker</h1>
        <p class="text-sm text-gray-500 mt-0.5">Inventory per jenis linen, par level, dan audit tracking</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.hk.linen.audit') }}" class="inline-flex items-center gap-1.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            Physical Audit
        </a>
        <a href="{{ route('panel.hk.linen.create') }}" class="inline-flex items-center gap-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Linen
        </a>
    </div>
</div>

{{-- Summary stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-card text-center">
        <div class="text-2xl font-bold text-gray-900">{{ $totalStock }}</div>
        <div class="text-xs text-gray-500 mt-0.5 font-medium">Total Stock</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-red-100 shadow-card text-center">
        <div class="text-2xl font-bold text-red-600">{{ $totalDamaged }}</div>
        <div class="text-xs text-red-600 mt-0.5 font-medium">Damaged</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-amber-100 shadow-card text-center">
        <div class="text-2xl font-bold text-amber-600">{{ $deficitCount }}</div>
        <div class="text-xs text-amber-600 mt-0.5 font-medium">Below Par</div>
    </div>
</div>

{{-- Linen grid cards --}}
@if ($items->isEmpty())
<div class="bg-white rounded-2xl shadow-card border border-gray-100 px-5 py-16 text-center">
    <div class="flex flex-col items-center gap-3">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-700">No linen items yet</p>
        <p class="text-xs text-gray-400">Add linen types to start tracking.</p>
    </div>
</div>
@else
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @foreach ($items as $item)
    @php
        $pct = $item->par_level > 0 ? min(100, round(($item->current_stock / $item->par_level) * 100)) : 0;
        $barColor = match($item->status) {
            'deficit' => 'bg-red-500',
            'low' => 'bg-amber-500',
            default => 'bg-emerald-500',
        };
        $borderColor = match($item->status) {
            'deficit' => 'border-red-200 bg-red-50/30',
            'low' => 'border-amber-200 bg-amber-50/30',
            default => 'border-emerald-200 bg-white',
        };
    @endphp
    <div class="rounded-2xl border shadow-card p-4 {{ $borderColor }}">
        <div class="flex items-start justify-between mb-3">
            <div>
                <h3 class="text-base font-semibold text-gray-900">{{ $item->name }}</h3>
                <span class="text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full capitalize mt-1 inline-block">
                    {{ str_replace('_', ' ', $item->type) }}
                </span>
            </div>
            <div class="flex items-center gap-1.5">
                @if ($item->status === 'deficit')
                <span class="text-xs font-bold text-red-600 bg-red-100 px-2 py-0.5 rounded-full">⚠ Deficit</span>
                @elseif ($item->status === 'low')
                <span class="text-xs font-medium text-amber-600 bg-amber-100 px-2 py-0.5 rounded-full">Low</span>
                @endif
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="mb-2">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>{{ $item->current_stock }} / {{ $item->par_level }} (par)</span>
                <span>{{ $pct }}%</span>
            </div>
            <div class="w-full h-2.5 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full {{ $barColor }} rounded-full transition-all duration-300" style="width:{{ $pct }}%"></div>
            </div>
        </div>

        <div class="flex items-center gap-3 text-xs text-gray-500 mt-3">
            <span>Initial: <strong>{{ $item->initial_stock }}</strong></span>
            <span>Damaged: <strong class="text-red-600">{{ $item->damaged }}</strong></span>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100">
            <form method="POST" action="{{ route('panel.hk.linen.in', $item->id) }}" class="flex items-center gap-2 flex-1">
                @csrf
                <input type="number" name="quantity" value="1" min="1" max="999"
                       class="w-16 px-2 py-1 text-xs border border-gray-200 rounded-lg text-center">
                <input type="text" name="reference" placeholder="Ref" class="w-20 px-2 py-1 text-xs border border-gray-200 rounded-lg" required>
                <button class="text-xs bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-2.5 py-1 rounded-lg font-medium transition-colors">In</button>
            </form>
            <form method="POST" action="{{ route('panel.hk.linen.out', $item->id) }}" class="flex items-center gap-2">
                @csrf
                <input type="number" name="quantity" value="1" min="1" max="999"
                       class="w-16 px-2 py-1 text-xs border border-gray-200 rounded-lg text-center">
                <select name="type" class="text-xs border border-gray-200 rounded-lg py-1 px-1.5">
                    <option value="out">Out</option>
                    <option value="damaged">Damaged</option>
                    <option value="discarded">Discarded</option>
                </select>
                <button class="text-xs bg-red-50 text-red-700 hover:bg-red-100 px-2.5 py-1 rounded-lg font-medium transition-colors">Out</button>
            </form>
            <a href="{{ route('panel.hk.linen.history', $item->id) }}" class="text-xs text-primary-600 hover:text-primary-700 font-medium whitespace-nowrap">History</a>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
