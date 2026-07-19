@extends('panel.layout')
@section('title', $booking->event_name)
@section('content')

@php
$statusLabels = [
    'inquiry' => 'Inquiry',
    'tentative' => 'Tentatif',
    'confirmed' => 'Dikonfirmasi',
    'cancelled' => 'Dibatalkan',
    'completed' => 'Selesai',
];
$statusColors = [
    'inquiry' => ['bg' => 'amber', 'hex' => '#f59e0b'],
    'tentative' => ['bg' => 'indigo', 'hex' => '#6366f1'],
    'confirmed' => ['bg' => 'emerald', 'hex' => '#10b981'],
    'cancelled' => ['bg' => 'rose', 'hex' => '#ef4444'],
    'completed' => ['bg' => 'gray', 'hex' => '#6b7280'],
];
$sc = $statusColors[$booking->status] ?? ['bg' => 'gray', 'hex' => '#6b7280'];
@endphp

<div class="mb-6">
    <a href="{{ route('panel.sales.events.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-3">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Daftar Event
    </a>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $booking->event_name }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $booking->eventType?->name ?? '-' }} · {{ $booking->event_date?->format('d F Y') }}</p>
        </div>
        <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-{{ $sc['bg'] }}-700 bg-{{ $sc['bg'] }}-50 px-3 py-1.5 rounded-full border border-{{ $sc['bg'] }}-200">
            <span class="w-2 h-2 rounded-full bg-{{ $sc['bg'] }}-500"></span>{{ $statusLabels[$booking->status] }}
        </span>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">

    {{-- Left: Main Info --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Info Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
                <p class="text-[11px] text-gray-500 uppercase tracking-wide font-semibold">Tanggal</p>
                <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $booking->event_date?->format('d M Y') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
                <p class="text-[11px] text-gray-500 uppercase tracking-wide font-semibold">Waktu</p>
                <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $booking->start_time?->format('H:i') }} - {{ $booking->end_time?->format('H:i') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
                <p class="text-[11px] text-gray-500 uppercase tracking-wide font-semibold">Tamu</p>
                <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ number_format($booking->expected_guests, 0, ',', '.') }} pax</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
                <p class="text-[11px] text-gray-500 uppercase tracking-wide font-semibold">Venue</p>
                <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $booking->venue?->room_number ?? '-' }}</p>
            </div>
        </div>

        {{-- Finansial Summary --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
            <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Ringkasan Finansial</h2>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Venue Base</p>
                        <p class="text-lg font-bold text-gray-900">Rp {{ number_format($totals['venue_base'], 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Layanan</p>
                        <p class="text-lg font-bold text-gray-900">Rp {{ number_format($totals['services_total'], 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Grand Total</p>
                        <p class="text-lg font-bold text-indigo-700">Rp {{ number_format($totals['grand_total'], 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Deposit</p>
                        <p class="text-lg font-bold text-emerald-700">Rp {{ number_format($totals['deposit_paid'], 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Sisa Tagihan</p>
                        <p class="text-lg font-bold text-rose-700">Rp {{ number_format($totals['balance_due'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Services Table --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
            <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Layanan Event</h2>
                <button onclick="document.getElementById('addServiceModal').classList.remove('hidden')"
                        class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 px-2.5 py-1.5 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah Layanan
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Layanan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Vendor</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Biaya</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Harga Jual</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Margin</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($booking->services as $svc)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3 font-medium text-gray-900">{{ $svc->service_name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $svc->vendor_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-mono text-gray-600">Rp {{ number_format($svc->cost, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono text-gray-900">Rp {{ number_format($svc->sell_price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono {{ ($svc->sell_price - $svc->cost) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                Rp {{ number_format($svc->sell_price - $svc->cost, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php $svcColors = ['pending' => 'amber', 'confirmed' => 'emerald', 'completed' => 'gray']; $svcC = $svcColors[$svc->status] ?? 'gray'; @endphp
                                <span class="text-xs font-medium text-{{ $svcC }}-700 bg-{{ $svcC }}-50 px-2 py-0.5 rounded-full border border-{{ $svcC }}-100">{{ ucfirst($svc->status) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-sm text-gray-400">Belum ada layanan. Klik "Tambah Layanan" untuk menambahkan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Setup Checklist --}}
        @if($booking->setup_requirements || $booking->catering_requirements)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
            <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Setup & Catering</h2>
            </div>
            <div class="p-5 grid md:grid-cols-2 gap-4">
                @if($booking->setup_requirements)
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Setup Requirements</h3>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($booking->setup_requirements as $item)
                        <span class="text-xs font-medium bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full border border-indigo-100">{{ is_string($item) ? $item : (is_array($item) ? json_encode($item) : $item) }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                @if($booking->catering_requirements)
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Catering Requirements</h3>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($booking->catering_requirements as $key => $val)
                        <span class="text-xs font-medium bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full border border-amber-100">{{ $key }}: {{ is_array($val) ? implode(', ', $val) : $val }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Special Requests & Internal Notes --}}
        @if($booking->special_requests || $booking->internal_notes)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
            <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Catatan</h2>
            </div>
            <div class="p-5 grid md:grid-cols-2 gap-4">
                @if($booking->special_requests)
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Permintaan Khusus</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $booking->special_requests }}</p>
                </div>
                @endif
                @if($booking->internal_notes)
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Catatan Internal</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $booking->internal_notes }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>

    {{-- Right: Actions & Timeline --}}
    <div class="space-y-6">

        {{-- Status Update --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
            <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Update Status</h2>
            </div>
            <div class="p-4">
                <form method="POST" action="{{ route('panel.sales.events.status', $booking->id) }}">
                    @csrf @method('PATCH')
                    <select name="status" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        @foreach($statusLabels as $key => $label)
                        <option value="{{ $key }}" {{ $booking->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        {{-- Guest Info --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
            <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Info Tamu</h2>
            </div>
            <div class="p-4 space-y-3 text-sm">
                <div>
                    <p class="text-xs text-gray-500">Nama</p>
                    <p class="font-semibold text-gray-900">{{ $booking->guest?->full_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Kontak</p>
                    <p class="text-gray-700">{{ $booking->guest?->phone ?? '-' }} / {{ $booking->guest?->email ?? '-' }}</p>
                </div>
                @if($booking->assignedUser)
                <div>
                    <p class="text-xs text-gray-500">Penanggung Jawab</p>
                    <p class="font-semibold text-gray-900">{{ $booking->assignedUser->name }}</p>
                </div>
                @endif
                @if($booking->folio)
                <div>
                    <p class="text-xs text-gray-500">Folio</p>
                    <a href="{{ route('panel.fo.folios.show', $booking->folio_id) }}" class="text-indigo-600 hover:text-indigo-700 font-medium">Folio #{{ $booking->folio_id }}</a>
                </div>
                @endif
            </div>
        </div>

        {{-- Edit Form --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
            <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Edit Booking</h2>
            </div>
            <div class="p-4">
                <form method="POST" action="{{ route('panel.sales.events.update', $booking->id) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <input type="text" name="event_name" value="{{ $booking->event_name }}" required
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    <input type="number" name="total_quoted" value="{{ $booking->total_quoted }}" step="1000"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                           placeholder="Total Penawaran (Rp)">
                    <input type="number" name="deposit_paid" value="{{ $booking->deposit_paid }}" step="1000"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                           placeholder="Deposit Dibayar (Rp)">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                        Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

{{-- Add Service Modal --}}
<div id="addServiceModal" class="hidden fixed inset-0 z-50 overflow-y-auto" x-data>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('addServiceModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Tambah Layanan</h3>
                <button onclick="document.getElementById('addServiceModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('panel.sales.events.services.store', $booking->id) }}" class="space-y-3">
                @csrf
                <input type="hidden" name="event_booking_id" value="{{ $booking->id }}">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Layanan <span class="text-rose-500">*</span></label>
                    <input type="text" name="service_name" required
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                           placeholder="cth: Catering, Dekorasi, Fotografi">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Vendor</label>
                    <input type="text" name="vendor_name"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                           placeholder="Nama vendor">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Biaya (Rp)</label>
                        <input type="number" name="cost" value="0" step="1000"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Harga Jual (Rp)</label>
                        <input type="number" name="sell_price" value="0" step="1000"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Catatan</label>
                    <textarea name="notes" rows="2"
                              class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"></textarea>
                </div>
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah Layanan
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
