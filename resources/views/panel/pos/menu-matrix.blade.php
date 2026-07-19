@extends('panel.layout')
@section('title', 'Menu Engineering Matrix')
@section('content')

@php
$quadrantColors = [
    'star' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700', 'label' => 'STAR'],
    'plowhorse' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-700', 'label' => 'PLOWHORSE'],
    'puzzle' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-700', 'label' => 'PUZZLE'],
    'dog' => ['bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'text' => 'text-rose-700', 'label' => 'DOG'],
];
@endphp

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Menu Engineering</h1>
        <p class="text-sm text-gray-500 mt-0.5">Analisis profitabilitas & popularitas menu</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.pos.menu-engineering.recipes') }}" class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            Resep
        </a>
        <form method="POST" action="{{ route('panel.pos.menu-engineering.calculate') }}" class="inline">
            @csrf
            <button class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Hitung Ulang
            </button>
        </form>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-emerald-50 rounded-2xl border border-emerald-100 p-4">
        <p class="text-xs text-emerald-600 uppercase tracking-wide font-semibold">STAR</p>
        <p class="text-lg font-bold text-emerald-800 mt-0.5">{{ count($stars) }} item</p>
        <p class="text-xs text-emerald-600">High Profit · High Popularity</p>
    </div>
    <div class="bg-amber-50 rounded-2xl border border-amber-100 p-4">
        <p class="text-xs text-amber-600 uppercase tracking-wide font-semibold">PLOWHORSE</p>
        <p class="text-lg font-bold text-amber-800 mt-0.5">{{ count($plowhorses) }} item</p>
        <p class="text-xs text-amber-600">Low Profit · High Popularity</p>
    </div>
    <div class="bg-blue-50 rounded-2xl border border-blue-100 p-4">
        <p class="text-xs text-blue-600 uppercase tracking-wide font-semibold">PUZZLE</p>
        <p class="text-lg font-bold text-blue-800 mt-0.5">{{ count($puzzles) }} item</p>
        <p class="text-xs text-blue-600">High Profit · Low Popularity</p>
    </div>
    <div class="bg-rose-50 rounded-2xl border border-rose-100 p-4">
        <p class="text-xs text-rose-600 uppercase tracking-wide font-semibold">DOG</p>
        <p class="text-lg font-bold text-rose-800 mt-0.5">{{ count($dogs) }} item</p>
        <p class="text-xs text-rose-600">Low Profit · Low Popularity</p>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">STAR &amp; PUZZLE</h2>
            <p class="text-xs text-gray-400 mt-0.5">High profit margin</p>
        </div>
        <div class="p-4 grid gap-3">
            @foreach(array_merge($stars, $puzzles) as $item)
            @php $qc = $quadrantColors[$item['quadrant']]; @endphp
            <a href="{{ route('panel.pos.menu-engineering.recipe', $item['recipe']->id) }}"
               class="flex items-center justify-between rounded-xl border {{ $qc['border'] }} {{ $qc['bg'] }} p-3 hover:shadow-md transition-shadow">
                <div>
                    <p class="text-sm font-bold text-gray-900">{{ $item['recipe']->name }}</p>
                    <p class="text-xs text-gray-500">Rp {{ number_format($item['recipe']->selling_price, 0, ',', '.') }} · Food Cost {{ $item['food_cost_pct'] }}%</p>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold {{ $qc['text'] }} px-2 py-1 bg-white rounded-full border {{ $qc['border'] }}">{{ $qc['label'] }}</span>
                    <p class="text-xs text-gray-500 mt-1">Margin {{ $item['margin'] }}% · Pop {{ $item['popularity'] }}%</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">PLOWHORSE &amp; DOG</h2>
            <p class="text-xs text-gray-400 mt-0.5">Low profit margin</p>
        </div>
        <div class="p-4 grid gap-3">
            @foreach(array_merge($plowhorses, $dogs) as $item)
            @php $qc = $quadrantColors[$item['quadrant']]; @endphp
            <a href="{{ route('panel.pos.menu-engineering.recipe', $item['recipe']->id) }}"
               class="flex items-center justify-between rounded-xl border {{ $qc['border'] }} {{ $qc['bg'] }} p-3 hover:shadow-md transition-shadow">
                <div>
                    <p class="text-sm font-bold text-gray-900">{{ $item['recipe']->name }}</p>
                    <p class="text-xs text-gray-500">Rp {{ number_format($item['recipe']->selling_price, 0, ',', '.') }} · Food Cost {{ $item['food_cost_pct'] }}%</p>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold {{ $qc['text'] }} px-2 py-1 bg-white rounded-full border {{ $qc['border'] }}">{{ $qc['label'] }}</span>
                    <p class="text-xs text-gray-500 mt-1">Margin {{ $item['margin'] }}% · Pop {{ $item['popularity'] }}%</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mt-6">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Semua Item</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Harga Jual</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Food Cost</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Cost %</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Margin %</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Pop %</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Kuadran</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($items as $item)
                @php $qc = $quadrantColors[$item['quadrant']]; @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $item['recipe']->name }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-700 font-mono">Rp {{ number_format($item['recipe']->selling_price, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-700 font-mono">Rp {{ number_format($item['food_cost'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $item['food_cost_pct'] }}%</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $item['margin'] }}%</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $item['popularity'] }}%</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-bold {{ $qc['text'] }} px-2 py-0.5 rounded-full border {{ $qc['border'] }}">{{ $qc['label'] }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
