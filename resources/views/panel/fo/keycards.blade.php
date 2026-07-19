@extends('panel.layout')
@section('title', 'Key Card Inventory')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Key Card Inventory</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manajemen kartu kunci — pengeluaran, pengembalian, dan audit</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.fo.keycard-types') }}" class="inline-flex items-center gap-1.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066"/></svg>
            Tipe Kartu
        </a>
    </div>
</div>

@if (session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-5 text-sm font-medium">
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm font-medium">
    {{ session('error') }}
</div>
@endif

{{-- Status Count Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-card text-center">
        <div class="text-2xl font-bold text-emerald-700">{{ $overview['available'] }}</div>
        <div class="text-xs text-emerald-600 mt-0.5 font-semibold">Tersedia</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-blue-100 shadow-card text-center">
        <div class="text-2xl font-bold text-blue-700">{{ $overview['assigned'] }}</div>
        <div class="text-xs text-blue-600 mt-0.5 font-semibold">Aktif</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-red-100 shadow-card text-center">
        <div class="text-2xl font-bold text-red-700">{{ $overview['lost'] }}</div>
        <div class="text-xs text-red-600 mt-0.5 font-semibold">Hilang</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-amber-100 shadow-card text-center">
        <div class="text-2xl font-bold text-amber-700">{{ $overview['damaged'] }}</div>
        <div class="text-xs text-amber-600 mt-0.5 font-semibold">Rusak</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

    {{-- Issue Key Card --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Keluarkan Kartu</h2>
        <form method="POST" action="{{ route('panel.fo.keycards.issue') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Kartu</label>
                <select name="card_id" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Pilih Kartu Tersedia --</option>
                    @foreach ($availableCards as $card)
                    <option value="{{ $card->id }}">{{ $card->card_number }} ({{ $card->keycardType?->name ?? '-' }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Reservasi</label>
                <select name="reservation_id" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Pilih --</option>
                    @foreach ($reservations as $r)
                    <option value="{{ $r->id }}">{{ $r->ref }} — {{ $r->primaryGuest?->full_name ?? '-' }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium py-2.5 rounded-xl transition-colors shadow-sm">
                Keluarkan Kartu
            </button>
        </form>
    </div>

    {{-- Available Cards Quick List --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Kartu Tersedia</h2>
        </div>
        <div class="overflow-x-auto max-h-48 overflow-y-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100 sticky top-0">
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">No. Kartu</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">RFID</th>
                        <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Reuse</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($availableCards as $card)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-2.5 font-mono text-sm font-semibold text-gray-800">{{ $card->card_number }}</td>
                        <td class="px-4 py-2.5 text-sm text-gray-600">{{ $card->keycardType?->name ?? '-' }}</td>
                        <td class="px-4 py-2.5 font-mono text-xs text-gray-400">{{ $card->rfid_uid ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right text-sm text-gray-500">{{ $card->times_reused }}x</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-sm text-gray-400">Tidak ada kartu tersedia</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Active Assignments --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Kartu Aktif — Sedang Digunakan</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">No. Kartu</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Reservasi</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tamu</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kamar</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Dikeluarkan</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($activeAssignments as $card)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 font-mono text-sm font-semibold text-gray-800">{{ $card->card_number }}</td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">{{ $card->keycardType?->name ?? '-' }}</td>
                    <td class="px-4 py-3.5">
                        <a href="{{ route('panel.fo.reservations.show', $card->assigned_to_reservation_id) }}" class="text-sm font-medium text-primary-600 hover:text-primary-800">
                            {{ $card->assignedReservation?->ref ?? '—' }}
                        </a>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">{{ $card->currentGuest?->full_name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">{{ $card->assignedRoom?->room_number ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-xs text-gray-400">{{ $card->issued_at?->diffForHumans() ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <form method="POST" action="{{ route('panel.fo.keycards.return', $card->id) }}">
                                @csrf
                                <button type="submit" class="text-xs text-emerald-600 hover:text-emerald-800 font-medium transition-colors">Kembalikan</button>
                            </form>
                            <form method="POST" action="{{ route('panel.fo.keycards.lost', $card->id) }}" onsubmit="return confirm('Tandai kartu ini hilang?')">
                                @csrf
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium transition-colors">Hilang</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-10 text-center text-sm text-gray-400">Tidak ada kartu yang sedang digunakan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
