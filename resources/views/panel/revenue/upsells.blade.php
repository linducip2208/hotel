@extends('panel.layout')
@section('title', 'Upsell & Upgrade Engine')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Upsell & Upgrade Engine</h1>
    <p class="text-sm text-gray-500 mt-0.5">Kelola penawaran tambahan dan upgrade kamar untuk meningkatkan pendapatan per reservasi</p>
</div>

<div x-data="{ tab: 'offers', editingOffer: null }" class="grid lg:grid-cols-3 gap-6">

    {{-- Left: Offer Management --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Tab bar --}}
        <div class="flex gap-0 bg-white rounded-2xl shadow-card border border-gray-100 p-1">
            <button @click="tab='offers'" :class="tab==='offers' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                class="flex-1 py-2 px-4 rounded-xl text-sm font-medium transition-all">Penawaran</button>
            <button @click="tab='activity'" :class="tab==='activity' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                class="flex-1 py-2 px-4 rounded-xl text-sm font-medium transition-all">Aktivitas</button>
        </div>

        {{-- Offers Table --}}
        <div x-show="tab==='offers'" class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">Daftar Penawaran</h2>
                <button @click="editingOffer = null; $refs.offerForm.reset(); $refs.offerForm.querySelector('[name=type]').value='room_upgrade'; $refs.offerForm.querySelector('[name=timing]').value='pre_arrival'; $refs.offerForm.querySelector('[name=min_stay_nights]').value='1'; setTimeout(()=>$refs.offerModal.showModal(), 50)"
                    class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-3.5 py-1.5 rounded-xl transition-colors shadow-sm shadow-indigo-500/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Tipe</th>
                            <th class="px-5 py-3">Harga</th>
                            <th class="px-5 py-3">Target</th>
                            <th class="px-5 py-3">Timing</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($offers as $offer)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ $offer->name }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    @switch($offer->type)
                                        @case('room_upgrade') bg-violet-100 text-violet-700 @break
                                        @case('late_checkout') bg-sky-100 text-sky-700 @break
                                        @case('spa') bg-pink-100 text-pink-700 @break
                                        @case('dinner') bg-amber-100 text-amber-700 @break
                                        @case('airport_transfer') bg-emerald-100 text-emerald-700 @break
                                        @case('package') bg-indigo-100 text-indigo-700 @break
                                        @default bg-gray-100 text-gray-600
                                    @endswitch
                                ">{{ str_replace('_', ' ', ucfirst($offer->type)) }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-gray-700 text-xs">Rp {{ number_format($offer->price, 0, ',', '.') }}</td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs font-medium
                                    @switch($offer->target_guest_tier)
                                        @case('hot') text-rose-600 @break
                                        @case('warm') text-amber-600 @break
                                        @case('cold') text-sky-600 @break
                                        @default text-gray-500
                                    @endswitch
                                ">{{ $offer->target_guest_tier ? ucfirst($offer->target_guest_tier) : 'All' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-600">{{ str_replace('_', ' ', ucfirst($offer->timing)) }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $offer->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400' }}">
                                    {{ $offer->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1">
                                    <button @click="editingOffer = {{ $offer->id }}; $refs.offerForm.reset();
                                        $refs.offerForm.querySelector('[name=name]').value='{{ addslashes($offer->name) }}';
                                        $refs.offerForm.querySelector('[name=type]').value='{{ $offer->type }}';
                                        $refs.offerForm.querySelector('[name=description]').value='{{ addslashes($offer->description ?? '') }}';
                                        $refs.offerForm.querySelector('[name=price]').value='{{ $offer->price }}';
                                        $refs.offerForm.querySelector('[name=min_stay_nights]').value='{{ $offer->min_stay_nights }}';
                                        $refs.offerForm.querySelector('[name=target_guest_tier]').value='{{ $offer->target_guest_tier ?? 'all' }}';
                                        $refs.offerForm.querySelector('[name=timing]').value='{{ $offer->timing }}';
                                        $refs.offerForm.querySelector('[name=days_before_arrival]').value='{{ $offer->days_before_arrival ?? '' }}';
                                        $refs.offerForm.querySelector('[name=upgrade_to_room_type_id]').value='{{ $offer->upgrade_to_room_type_id ?? '' }}';
                                        $refs.offerForm.querySelector('[name=is_active]').checked = {{ $offer->is_active ? 'true' : 'false' }};
                                        $refs.offerForm.action='{{ route('panel.revenue.upsells.update', $offer->id) }}';
                                        setTimeout(()=>$refs.offerModal.showModal(), 50)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('panel.revenue.upsells.destroy', $offer->id) }}" onsubmit="return confirm('Hapus penawaran ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                Belum ada penawaran upsell. Klik <strong>Tambah</strong> untuk membuat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Activity Table --}}
        <div x-show="tab==='activity'" class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">Aktivitas Upsell Terbaru</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-5 py-3">Reservasi</th>
                            <th class="px-5 py-3">Tamu</th>
                            <th class="px-5 py-3">Penawaran</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Harga</th>
                            <th class="px-5 py-3">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($recentActivity as $act)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('panel.fo.reservations.show', $act->reservation_id) }}" class="text-indigo-600 hover:underline font-medium text-xs">
                                    {{ $act->reservation->ref ?? '#' . $act->reservation_id }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-700">{{ $act->reservation->primaryGuest?->full_name ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-xs font-medium text-gray-800">{{ $act->offer?->name ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                    @switch($act->status)
                                        @case('offered') bg-blue-100 text-blue-700 @break
                                        @case('accepted') bg-emerald-100 text-emerald-700 @break
                                        @case('declined') bg-rose-100 text-rose-700 @break
                                        @case('expired') bg-gray-100 text-gray-500 @break
                                    @endswitch
                                ">{{ ucfirst($act->status) }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-gray-600">Rp {{ number_format($act->price_accepted ?? $act->price_offered, 0, ',', '.') }}</td>
                            <td class="px-5 py-3.5 text-xs text-gray-500">{{ $act->responded_at ? $act->responded_at->diffForHumans() : $act->offered_at->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-gray-400">Belum ada aktivitas upsell.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right: Quick Stats --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 text-sm mb-4">Ringkasan Upsell</h3>
            @php
                $totalOffered = \App\Models\UpsellPresentation::where('property_id', app('current_property')->id)->whereDate('offered_at', '>=', now()->subDays(30))->count();
                $totalAccepted = \App\Models\UpsellPresentation::where('property_id', app('current_property')->id)->where('status', 'accepted')->whereDate('offered_at', '>=', now()->subDays(30))->count();
                $acceptRate = $totalOffered > 0 ? round(($totalAccepted / $totalOffered) * 100) : 0;
                $totalRevenue = \App\Models\UpsellPresentation::where('property_id', app('current_property')->id)->where('status', 'accepted')->whereDate('offered_at', '>=', now()->subDays(30))->sum('price_accepted');
            @endphp
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-indigo-50 rounded-xl p-3 text-center">
                    <div class="text-2xl font-bold text-indigo-700">{{ $totalOffered }}</div>
                    <div class="text-xs text-indigo-500 font-medium mt-0.5">Ditawarkan</div>
                </div>
                <div class="bg-emerald-50 rounded-xl p-3 text-center">
                    <div class="text-2xl font-bold text-emerald-700">{{ $totalAccepted }}</div>
                    <div class="text-xs text-emerald-500 font-medium mt-0.5">Diterima</div>
                </div>
            </div>
            <div class="mt-3 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500">Acceptance Rate</span>
                    <span class="font-semibold text-gray-800">{{ $acceptRate }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full bg-emerald-500" style="width:{{ $acceptRate }}%"></div>
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs">
                <span class="text-gray-500">Revenue (30 hari)</span>
                <span class="font-bold text-gray-900 font-mono">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 text-sm mb-3">Panduan Upsell</h3>
            <ul class="space-y-2 text-xs text-gray-600">
                <li class="flex gap-2"><span class="text-indigo-500 font-bold shrink-0">1.</span> Buat penawaran upsell (upgrade kamar, spa, dinner, dll).</li>
                <li class="flex gap-2"><span class="text-indigo-500 font-bold shrink-0">2.</span> Targetkan tamu berdasarkan tier (hot/warm/cold).</li>
                <li class="flex gap-2"><span class="text-indigo-500 font-bold shrink-0">3.</span> Atur timing: pre-arrival, saat check-in, atau selama menginap.</li>
                <li class="flex gap-2"><span class="text-indigo-500 font-bold shrink-0">4.</span> Presentasikan ke reservasi spesifik dari halaman reservasi.</li>
                <li class="flex gap-2"><span class="text-indigo-500 font-bold shrink-0">5.</span> Pantau acceptance rate dan revenue dari ringkasan.</li>
            </ul>
        </div>
    </div>
</div>

{{-- Offer Form Modal --}}
<dialog x-ref="offerModal" class="backdrop:bg-gray-900/60 rounded-2xl shadow-xl border-0 p-0 w-full max-w-lg" @click.self="$refs.offerModal.close()">
    <div class="bg-white rounded-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900" x-text="editingOffer ? 'Edit Penawaran' : 'Tambah Penawaran'">Tambah Penawaran</h3>
            <button @click="$refs.offerModal.close()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" x-ref="offerForm" action="{{ route('panel.revenue.upsells.store') }}" class="p-6 space-y-4">
            @csrf
            <template x-if="editingOffer"><input type="hidden" name="_method" value="PUT"></template>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Penawaran</label>
                <input type="text" name="name" required
                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow"
                    placeholder="Contoh: Upgrade ke Suite">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tipe</label>
                    <select name="type" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="room_upgrade">Room Upgrade</option>
                        <option value="late_checkout">Late Checkout</option>
                        <option value="spa">Spa</option>
                        <option value="dinner">Dinner</option>
                        <option value="airport_transfer">Airport Transfer</option>
                        <option value="package">Package</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Harga (Rp)</label>
                    <input type="number" name="price" required min="0"
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                        placeholder="50000">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Deskripsi</label>
                <textarea name="description" rows="2"
                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                    placeholder="Deskripsi singkat penawaran..."></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Min. Malam Menginap</label>
                    <input type="number" name="min_stay_nights" required min="1" value="1"
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Target Tamu</label>
                    <select name="target_guest_tier" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="all">Semua</option>
                        <option value="hot">Hot (High Spender)</option>
                        <option value="warm">Warm (Medium)</option>
                        <option value="cold">Cold (Low Spender)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Timing</label>
                    <select name="timing" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="pre_arrival">Pre-Arrival</option>
                        <option value="checkin">Saat Check-in</option>
                        <option value="during_stay">Selama Menginap</option>
                        <option value="anytime">Kapan Saja</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Hari Sebelum Tiba</label>
                    <input type="number" name="days_before_arrival" min="0"
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                        placeholder="Opsional">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tipe Kamar Upgrade (untuk room_upgrade)</label>
                <select name="upgrade_to_room_type_id" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">-- Pilih --</option>
                    @foreach(\App\Models\RoomType::where('property_id', app('current_property')->id)->where('is_active', true)->orderBy('name')->get() as $rt)
                    <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label class="text-sm text-gray-700">Aktif</label>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="$refs.offerModal.close()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm shadow-indigo-500/25 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</dialog>

@endsection
