@extends('panel.layout')
@section('title', 'Reservasi Meja')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Reservasi Meja</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola reservasi meja restoran</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.pos.tables.floorplan') }}" class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M10 4v16M14 4v16"/></svg>
            Denah
        </a>
        <button onclick="document.getElementById('resModal').classList.remove('hidden')"
                class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Reservasi Baru
        </button>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Daftar Reservasi</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Meja</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tamu</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Jam</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Org</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($reservations as $r)
                @php
                    $stClr = match($r->status) {
                        'confirmed' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'seated' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'no_show' => 'bg-gray-100 text-gray-600 border-gray-200',
                        'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
                        default => 'bg-gray-50 text-gray-600 border-gray-200',
                    };
                    $stLabel = match($r->status) {
                        'confirmed' => 'Dikonfirmasi', 'seated' => 'Duduk',
                        'completed' => 'Selesai', 'no_show' => 'No-Show',
                        'cancelled' => 'Dibatalkan', default => $r->status,
                    };
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-4 py-3 text-sm text-gray-800">{{ \Carbon\Carbon::parse($r->reservation_date)->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $r->restaurantTable?->table_number ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $r->guest_name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ \Carbon\Carbon::parse($r->start_time)->format('H:i') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $r->party_size }}</td>
                    <td class="px-4 py-3"><span class="text-xs font-medium px-2 py-0.5 rounded-full border {{ $stClr }}">{{ $stLabel }}</span></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-1">
                            @if($r->status === 'confirmed')
                            <form method="POST" action="{{ route('panel.pos.tables.checkin', $r->id) }}" class="inline">
                                @csrf
                                <button class="text-xs bg-blue-100 text-blue-700 hover:bg-blue-200 px-2 py-1 rounded-lg font-medium">Duduk</button>
                            </form>
                            <form method="POST" action="{{ route('panel.pos.tables.cancel', $r->id) }}" class="inline">
                                @csrf
                                <button class="text-xs bg-rose-100 text-rose-700 hover:bg-rose-200 px-2 py-1 rounded-lg font-medium">Batal</button>
                            </form>
                            @endif
                            @if($r->status === 'seated')
                            <form method="POST" action="{{ route('panel.pos.tables.complete', $r->id) }}" class="inline">
                                @csrf
                                <button class="text-xs bg-emerald-100 text-emerald-700 hover:bg-emerald-200 px-2 py-1 rounded-lg font-medium">Selesai</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="py-10 text-center text-sm text-gray-400">Belum ada reservasi meja.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reservations->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $reservations->links() }}</div>
    @endif
</div>

<div id="resModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('resModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900">Reservasi Meja Baru</h3>
                <button onclick="document.getElementById('resModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" action="{{ route('panel.pos.tables.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Meja <span class="text-red-500">*</span></label>
                    <select name="restaurant_table_id" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        <option value="">-- Pilih --</option>
                        @foreach($tables as $t)
                        <option value="{{ $t->id }}">{{ $t->table_number }} ({{ $t->capacity }} kursi, {{ $t->section ?? 'Default' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Tamu <span class="text-red-500">*</span></label>
                        <input type="text" name="guest_name" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">No. HP</label>
                        <input type="text" name="guest_phone" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="reservation_date" value="{{ now()->toDateString() }}" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jam <span class="text-red-500">*</span></label>
                        <input type="time" name="start_time" value="{{ now()->format('H:00') }}" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah <span class="text-red-500">*</span></label>
                        <input type="number" name="party_size" value="2" min="1" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Durasi (menit)</label>
                    <input type="number" name="duration_minutes" value="90" min="30" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Permintaan Khusus</label>
                    <textarea name="special_requests" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tamu (dari database)</label>
                    <select name="guest_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        <option value="">-- Pilih --</option>
                        @foreach($guests as $g)
                        <option value="{{ $g->id }}">{{ $g->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('resModal').classList.add('hidden')" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
