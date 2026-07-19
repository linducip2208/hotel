@extends('panel.layout')
@section('title', 'Denah Meja')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Denah Meja</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($date)->format('d M Y') }} · Visual status meja restoran</p>
    </div>
    <div class="flex items-center gap-2">
        <form method="GET" class="flex items-center gap-2">
            <input type="date" name="date" value="{{ $date }}" class="rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            <select name="outlet_id" class="rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                <option value="">Semua Outlet</option>
                @foreach($outlets as $o)
                <option value="{{ $o->id }}" {{ $selectedOutletId == $o->id ? 'selected' : '' }}>{{ $o->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">Filter</button>
        </form>
        <a href="{{ route('panel.pos.tables.index') }}" class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            List
        </a>
    </div>
</div>

<div class="flex items-center gap-4 mb-5 text-xs font-medium flex-wrap">
    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-400"></span>Tersedia</span>
    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-400"></span>Terisi (Seated)</span>
    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-400"></span>Direservasi</span>
    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-gray-300"></span>Non-Aktif</span>
</div>

@if(empty($plan))
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-12 flex flex-col items-center text-center">
    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M10 4v16M14 4v16"/></svg>
    </div>
    <p class="text-base font-semibold text-gray-600">Tidak ada meja</p>
    <p class="text-sm text-gray-400 mt-1">Tambahkan meja restoran terlebih dahulu.</p>
</div>
@else
@foreach($plan->groupBy('table.section') as $section => $sectionPlan)
<div class="mb-6 last:mb-0">
    @if($section)
    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
        {{ $section ?: 'Default' }}
    </h3>
    @endif
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
        @foreach($sectionPlan as $item)
        @php
            $table = $item['table'];
            $status = $item['status'];
            $reservation = $item['current_reservation'];

            $borderCls = match($status) {
                'available' => 'border-emerald-200 bg-white hover:border-emerald-300',
                'occupied' => 'border-rose-200 bg-rose-50 hover:border-rose-300',
                'reserved' => 'border-amber-200 bg-amber-50 hover:border-amber-300',
                default => 'border-gray-200 bg-gray-100',
            };
            $dotCls = match($status) {
                'available' => 'bg-emerald-400',
                'occupied' => 'bg-rose-400',
                'reserved' => 'bg-amber-400',
                default => 'bg-gray-400',
            };
            $textCls = match($status) {
                'available' => 'text-emerald-700',
                'occupied' => 'text-rose-700',
                'reserved' => 'text-amber-700',
                default => 'text-gray-500',
            };
        @endphp
        <div x-data="{ open: false }" class="relative rounded-2xl border-2 {{ $borderCls }} p-4 text-center shadow-card hover:shadow-card-hover transition-all cursor-pointer group">
            <div class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full {{ $dotCls }}"></div>
            <button @click="open = !open" class="w-full text-left">
                <div class="text-lg font-bold text-gray-900 mb-0.5">{{ $table->table_number }}</div>
                <div class="text-xs {{ $textCls }} font-medium capitalize">{{ $status === 'available' ? 'Tersedia' : ($status === 'occupied' ? 'Terisi' : 'Direservasi') }}</div>
                @if($status !== 'available' && $reservation)
                <div class="text-[11px] text-gray-500 mt-1 truncate">{{ $reservation->guest_name }} · {{ $reservation->party_size }} org</div>
                @endif
                <div class="text-[10px] text-gray-400 capitalize mt-0.5">{{ $table->shape }} · {{ $table->capacity }} kursi</div>
            </button>
            @if($status === 'available')
            <div class="mt-2 pt-2 border-t border-gray-100">
                <button onclick="quickBook('{{ $table->table_number }}', {{ $table->id }})"
                        class="w-full text-[11px] font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 py-1.5 rounded-lg transition-colors">
                    Quick Book
                </button>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endforeach
@endif

<div id="quickBookModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('quickBookModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900">Reservasi Cepat</h3>
                <button onclick="document.getElementById('quickBookModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" action="{{ route('panel.pos.tables.store') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="restaurant_table_id" id="qbTableId">
                <input type="hidden" name="reservation_date" value="{{ $date }}">
                <div>
                    <label class="text-xs text-gray-500">Meja: <span class="font-bold text-gray-800" id="qbTableName">-</span></label>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Tamu <span class="text-red-500">*</span></label>
                    <input type="text" name="guest_name" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">No. HP</label>
                    <input type="text" name="guest_phone" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah <span class="text-red-500">*</span></label>
                        <input type="number" name="party_size" value="2" min="1" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jam <span class="text-red-500">*</span></label>
                        <input type="time" name="start_time" value="{{ now()->format('H:00') }}" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Permintaan Khusus</label>
                    <textarea name="special_requests" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('quickBookModal').classList.add('hidden')" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">Reservasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function quickBook(tableName, tableId) {
    document.getElementById('qbTableName').textContent = tableName;
    document.getElementById('qbTableId').value = tableId;
    document.getElementById('quickBookModal').classList.remove('hidden');
}
</script>

@endsection
