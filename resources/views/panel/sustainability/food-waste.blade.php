@extends('panel.layout')
@section('title', 'Food Waste Tracking')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Food Waste</h1>
        <p class="text-sm text-gray-500 mt-0.5">Lacak dan kurangi limbah makanan</p>
    </div>
    <button onclick="document.getElementById('logModal').classList.remove('hidden')"
            class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Catat Waste
    </button>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Total Hari Ini</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($today->total_kg ?? 0, 2, ',', '.') }} kg</p>
        <p class="text-xs text-gray-500 mt-0.5">Rp {{ number_format($today->total_cost ?? 0, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-blue-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Minggu Ini</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($this_week->total_kg ?? 0, 2, ',', '.') }} kg</p>
        <p class="text-xs text-gray-500 mt-0.5">Rp {{ number_format($this_week->total_cost ?? 0, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-indigo-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Bulan Ini</p>
        <p class="text-2xl font-bold text-indigo-600 mt-1">{{ number_format($this_month->total_kg ?? 0, 2, ',', '.') }} kg</p>
        <p class="text-xs text-gray-500 mt-0.5">Rp {{ number_format($this_month->total_cost ?? 0, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl border {{ $month_change_pct > 0 ? 'border-rose-100' : 'border-emerald-100' }} shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">vs Bulan Lalu</p>
        <p class="text-2xl font-bold {{ $month_change_pct > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">{{ $month_change_pct > 0 ? '+' : '' }}{{ $month_change_pct }}%</p>
        <p class="text-xs text-gray-500 mt-0.5">{{ $month_change_pct > 0 ? 'Meningkat' : 'Menurun' }}</p>
    </div>
</div>

@if($target_progress)
<div class="bg-amber-50 rounded-2xl border border-amber-100 p-5 mb-6">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h3 class="text-sm font-bold text-amber-800">Target Pengurangan</h3>
            <p class="text-xs text-amber-600">Target: {{ $target_progress['target_pct'] }}% · Periode: {{ \Carbon\Carbon::parse($target_progress['target']->period_start)->format('d M') }} - {{ \Carbon\Carbon::parse($target_progress['target']->period_end)->format('d M Y') }}</p>
        </div>
        <span class="text-xs font-bold text-amber-700 bg-white px-3 py-1 rounded-full border border-amber-200">{{ $target_progress['reduction_pct'] }}% tercapai</span>
    </div>
    <div class="w-full bg-amber-200 rounded-full h-3">
        <div class="bg-amber-600 h-3 rounded-full transition-all" style="width: {{ min(100, max(0, ($target_progress['reduction_pct'] / $target_progress['target_pct']) * 100)) }}%"></div>
    </div>
    <div class="flex justify-between text-xs text-amber-600 mt-1.5">
        <span>Baseline: {{ number_format($target_progress['baseline_kg'], 2, ',', '.') }} kg</span>
        <span>Aktual: {{ number_format($target_progress['achieved_kg'], 2, ',', '.') }} kg</span>
    </div>
</div>
@endif

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Waste per Kategori (Bulan Ini)</h2>
            </div>
            <div class="p-4">
                <div class="space-y-3">
                    @php $maxKg = $by_category->max('total_kg') ?: 1; @endphp
                    @forelse($by_category as $cat)
                    @php
                        $catLabels = ['prep' => 'Persiapan', 'spoilage' => 'Busuk', 'plate_return' => 'Sisa Piring', 'overproduction' => 'Overproduksi', 'expired' => 'Kadaluarsa'];
                        $catColors = ['prep' => 'blue', 'spoilage' => 'amber', 'plate_return' => 'emerald', 'overproduction' => 'violet', 'expired' => 'rose'];
                        $cc = $catColors[$cat->waste_category] ?? 'gray';
                    @endphp
                    <div>
                        <div class="flex justify-between text-xs font-medium mb-1">
                            <span class="text-gray-700">{{ $catLabels[$cat->waste_category] ?? $cat->waste_category }}</span>
                            <span class="text-gray-500">{{ number_format($cat->total_kg, 2, ',', '.') }} kg · Rp {{ number_format($cat->total_cost, 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-{{ $cc }}-500 h-2 rounded-full" style="width: {{ ($cat->total_kg / $maxKg) * 100 }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 text-center py-4">Belum ada data kategori.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Log Terbaru</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Makanan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategori</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Qty (kg)</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Biaya</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($logs as $log)
                        @php
                            $catLabels = ['prep' => 'Persiapan', 'spoilage' => 'Busuk', 'plate_return' => 'Sisa Piring', 'overproduction' => 'Overproduksi', 'expired' => 'Kadaluarsa'];
                            $mpLabels = ['breakfast' => 'Sarapan', 'lunch' => 'Makan Siang', 'dinner' => 'Makan Malam', 'snack' => 'Snack'];
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-700">{{ \Carbon\Carbon::parse($log->logged_date)->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $log->food_name }}</td>
                            <td class="px-4 py-3"><span class="text-xs font-medium bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full">{{ $catLabels[$log->waste_category] ?? $log->waste_category }}</span></td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ number_format($log->quantity_kg, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700 font-mono">Rp {{ number_format($log->estimated_cost, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $mpLabels[$log->meal_period] ?? $log->meal_period }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="py-10 text-center text-sm text-gray-400">Belum ada log food waste.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Target</h2>
            </div>
            <div class="p-4 space-y-3">
                @forelse($targets as $t)
                <div class="rounded-xl border border-gray-100 p-3 bg-gray-50/50">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-gray-600">{{ \Carbon\Carbon::parse($t->period_start)->format('d M') }} - {{ \Carbon\Carbon::parse($t->period_end)->format('d M Y') }}</span>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $t->status === 'active' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-gray-100 text-gray-500' }}">{{ $t->status === 'active' ? 'Aktif' : 'Selesai' }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Target Reduksi: <strong class="text-gray-800">{{ $t->target_reduction_pct }}%</strong></span>
                    </div>
                    <div class="flex justify-between text-xs mt-1">
                        <span class="text-gray-500">Baseline: {{ number_format($t->baseline_kg, 2, ',', '.') }} kg</span>
                        <span class="text-gray-500">Aktual: {{ number_format($t->actual_kg, 2, ',', '.') }} kg</span>
                    </div>
                    @if($t->status === 'active')
                    <form method="POST" action="{{ route('panel.sustainability.food-waste.targets.complete', $t->id) }}" class="mt-2">
                        @csrf
                        <button class="text-xs text-gray-500 hover:text-emerald-600 font-medium">Tandai Selesai</button>
                    </form>
                    @endif
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">Belum ada target.</p>
                @endforelse

                <button onclick="document.getElementById('targetModal').classList.remove('hidden')"
                        class="w-full text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 py-2 rounded-lg transition-colors">
                    + Tambah Target
                </button>
            </div>
        </div>
    </div>
</div>

<div id="logModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('logModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900">Catat Food Waste</h3>
                <button onclick="document.getElementById('logModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" action="{{ route('panel.sustainability.food-waste.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Makanan <span class="text-red-500">*</span></label>
                    <input type="text" name="food_name" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                        <select name="waste_category" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            <option value="prep">Persiapan (Trim)</option>
                            <option value="spoilage">Busuk / Rusak</option>
                            <option value="plate_return">Sisa Piring</option>
                            <option value="overproduction">Overproduksi</option>
                            <option value="expired">Kadaluarsa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Waktu Makan <span class="text-red-500">*</span></label>
                        <select name="meal_period" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            <option value="breakfast">Sarapan</option>
                            <option value="lunch">Makan Siang</option>
                            <option value="dinner">Makan Malam</option>
                            <option value="snack">Snack</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah (kg) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.001" name="quantity_kg" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Estimasi Biaya (Rp)</label>
                        <input type="number" name="estimated_cost" value="0" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Outlet (opsional)</label>
                    <select name="outlet_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        <option value="">-- Pilih --</option>
                        @foreach($outlets as $o)
                        <option value="{{ $o->id }}">{{ $o->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal</label>
                    <input type="date" name="logged_date" value="{{ now()->toDateString() }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('logModal').classList.add('hidden')" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="targetModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('targetModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900">Tambah Target Pengurangan</h3>
                <button onclick="document.getElementById('targetModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" action="{{ route('panel.sustainability.food-waste.targets.store') }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="period_start" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Selesai <span class="text-red-500">*</span></label>
                        <input type="date" name="period_end" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Target Reduksi (%) <span class="text-red-500">*</span></label>
                        <input type="number" name="target_reduction_pct" value="10" min="1" max="100" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Baseline (kg) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.001" name="baseline_kg" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('targetModal').classList.add('hidden')" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
