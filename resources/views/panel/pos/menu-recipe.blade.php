@extends('panel.layout')
@section('title', 'Detail Resep')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <div class="flex items-center gap-3">
            <a href="{{ route('panel.pos.menu-engineering.matrix') }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $recipe->name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $recipe->category ?? 'Tanpa Kategori' }} · {{ $recipe->portion_size ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Harga Jual</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($recipe->selling_price, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Food Cost</p>
        <p class="text-2xl font-bold text-rose-600 mt-1">Rp {{ number_format($recipe->food_cost, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Cost %</p>
        <p class="text-2xl font-bold {{ $recipe->food_cost_pct > 35 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">{{ $recipe->food_cost_pct }}%</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Gross Profit</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($recipe->gross_profit, 0, ',', '.') }}</p>
    </div>
</div>

@if($recommendedPrice > 0)
<div class="bg-blue-50 rounded-2xl border border-blue-100 p-4 mb-6">
    <p class="text-sm font-semibold text-blue-800">Rekomendasi Harga Jual (target food cost 30%): <span class="text-lg">Rp {{ number_format($recommendedPrice, 0, ',', '.') }}</span></p>
    <p class="text-xs text-blue-600 mt-0.5">Harga saat ini: Rp {{ number_format($recipe->selling_price, 0, ',', '.') }} {{ $recipe->selling_price < $recommendedPrice ? '(di bawah rekomendasi)' : '(di atas rekomendasi)' }}</p>
</div>
@endif

<div class="grid md:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">Bahan / Ingredients</h2>
            <button onclick="document.getElementById('ingredientModal').classList.remove('hidden')"
                    class="text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">
                + Tambah Bahan
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Bahan</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Qty</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Unit</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Cost/Unit</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($recipe->ingredients as $ing)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $ing->ingredient_name }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $ing->quantity }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $ing->unit }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-700 font-mono">Rp {{ number_format($ing->cost_per_unit, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-900 font-bold font-mono">Rp {{ number_format($ing->total_cost, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <form method="POST" action="{{ route('panel.pos.menu-engineering.ingredient.destroy', $ing->id) }}" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-xs text-rose-500 hover:text-rose-700 font-medium">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-8 text-center text-sm text-gray-400">Belum ada bahan. Tambahkan bahan untuk menghitung food cost.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($recipe->performances->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Riwayat Performa</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Periode</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Terjual</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Revenue</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Profit</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Margin %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recipe->performances as $perf)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $perf->period_start->format('d M') }} - {{ $perf->period_end->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $perf->units_sold }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-700 font-mono">Rp {{ number_format($perf->total_revenue, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-700 font-mono">Rp {{ number_format($perf->gross_profit, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $perf->profit_margin_pct }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<div id="ingredientModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('ingredientModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900">Tambah Bahan</h3>
                <button onclick="document.getElementById('ingredientModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" action="{{ route('panel.pos.menu-engineering.ingredient.store', $recipe->id) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Bahan <span class="text-red-500">*</span></label>
                    <input type="text" name="ingredient_name" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah <span class="text-red-500">*</span></label>
                        <input type="number" step="0.001" name="quantity" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Satuan <span class="text-red-500">*</span></label>
                        <select name="unit" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            <option value="gram">gram</option>
                            <option value="kg">kg</option>
                            <option value="ml">ml</option>
                            <option value="liter">liter</option>
                            <option value="pcs">pcs</option>
                            <option value="sdm">sdm</option>
                            <option value="sdt">sdt</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Harga per Satuan (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="cost_per_unit" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('ingredientModal').classList.add('hidden')" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
