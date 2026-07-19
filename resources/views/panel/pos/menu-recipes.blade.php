@extends('panel.layout')
@section('title', 'Resep Menu')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Resep Menu</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola resep, bahan, dan kalkulasi biaya makanan</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.pos.menu-engineering.matrix') }}" class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M3 8h18M3 12h18M3 16h18"/></svg>
            Matrix
        </a>
        <button onclick="document.getElementById('recipeModal').classList.remove('hidden')"
                class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Resep Baru
        </button>
    </div>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($recipes as $recipe)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-900">{{ $recipe->name }}</h3>
                <p class="text-xs text-gray-500">{{ $recipe->category ?? '-' }} · {{ $recipe->portion_size ?? '-' }}</p>
            </div>
            <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">{{ $recipe->ingredients->count() }} bahan</span>
        </div>
        <div class="px-5 py-3 space-y-2">
            <div class="flex justify-between text-xs">
                <span class="text-gray-500">Harga Jual</span>
                <span class="font-bold text-gray-800 font-mono">Rp {{ number_format($recipe->selling_price, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-gray-500">Food Cost</span>
                <span class="font-bold text-rose-600 font-mono">Rp {{ number_format($recipe->food_cost, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-gray-500">Cost %</span>
                <span class="font-bold {{ $recipe->food_cost_pct > 35 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $recipe->food_cost_pct }}%</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-gray-500">Gross Profit</span>
                <span class="font-bold text-emerald-600 font-mono">Rp {{ number_format($recipe->gross_profit, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="px-5 py-3 border-t border-gray-50 flex items-center gap-2">
            <a href="{{ route('panel.pos.menu-engineering.recipe', $recipe->id) }}"
               class="flex-1 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 py-2 text-center rounded-lg transition-colors">
                Detail
            </a>
            <form method="POST" action="{{ route('panel.pos.menu-engineering.recipe.destroy', $recipe->id) }}" class="inline"
                  onsubmit="return confirm('Hapus resep ini?')">
                @csrf @method('DELETE')
                <button class="text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 px-3 py-2 rounded-lg transition-colors">Hapus</button>
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white rounded-2xl shadow-card border border-gray-100 p-12 flex flex-col items-center text-center">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <p class="text-base font-semibold text-gray-600">Belum ada resep</p>
        <p class="text-sm text-gray-400 mt-1">Tambahkan resep menu untuk mulai menghitung food cost.</p>
    </div>
    @endforelse
</div>

<div id="recipeModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('recipeModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900">Resep Baru</h3>
                <button onclick="document.getElementById('recipeModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" action="{{ route('panel.pos.menu-engineering.recipe.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Menu <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="selling_price" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Porsi</label>
                        <input type="text" name="portion_size" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all" placeholder="1 porsi, 200g">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kategori</label>
                        <select name="category" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            <option value="">-- Pilih --</option>
                            <option value="Makanan Pembuka">Makanan Pembuka</option>
                            <option value="Makanan Utama">Makanan Utama</option>
                            <option value="Makanan Penutup">Makanan Penutup</option>
                            <option value="Minuman">Minuman</option>
                            <option value="Snack">Snack</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Terkait Menu POS</label>
                        <select name="menu_item_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            <option value="">-- Pilih --</option>
                            @foreach($menuItems as $mi)
                            <option value="{{ $mi->id }}">{{ $mi->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('recipeModal').classList.add('hidden')" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
