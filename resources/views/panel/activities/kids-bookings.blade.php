@extends('panel.layout')
@section('title', 'Booking Kids Club')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Booking Kids Club</h1>
        <p class="text-sm text-gray-500 mt-0.5">Semua booking aktivitas anak</p>
    </div>
    <a href="{{ route('panel.kids-club.index') }}" class="inline-flex items-center gap-1.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Daftar Aktivitas
    </a>
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

{{-- New Booking Form --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 mb-6">
    <h2 class="text-sm font-semibold text-gray-700 mb-4">Buat Booking Baru</h2>
    <form method="POST" action="{{ route('panel.kids-club.book') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @csrf
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Aktivitas</label>
            <select name="kids_activity_id" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">-- Pilih --</option>
                @foreach ($activities as $act)
                <option value="{{ $act->id }}">{{ $act->name }} ({{ $act->age_range }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Nama Anak</label>
            <input type="text" name="child_name" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Nama anak">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Usia</label>
            <input type="number" name="child_age" min="0" max="18" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Tahun">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal</label>
            <input type="date" name="booking_date" value="{{ today()->toDateString() }}" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Jam Mulai</label>
            <input type="time" name="start_time" value="09:00" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium py-2.5 rounded-xl transition-colors shadow-sm">
                Buat Booking
            </button>
        </div>
    </form>
</div>

{{-- Bookings Table --}}
@if ($bookings->isEmpty())
<div class="bg-white rounded-2xl shadow-card border border-gray-100 px-5 py-16 text-center">
    <div class="flex flex-col items-center gap-3">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-700">Belum ada booking</p>
        <p class="text-xs text-gray-400">Buat booking pertama di form di atas.</p>
    </div>
</div>
@else
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Anak</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Aktivitas</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Jam</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($bookings as $booking)
                @php
                    $sc = match($booking->status) {
                        'booked' => 'emerald',
                        'completed' => 'blue',
                        'cancelled' => 'red',
                        'no_show' => 'amber',
                        default => 'gray'
                    };
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-semibold text-gray-800">{{ $booking->child_name }}</p>
                        <p class="text-xs text-gray-400">{{ $booking->child_age }} tahun</p>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-700">{{ $booking->activity?->name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-sm text-gray-700">{{ $booking->booking_date->format('d M Y') }}</td>
                    <td class="px-4 py-3.5 text-sm text-gray-700">{{ $booking->start_time instanceof \Carbon\Carbon ? $booking->start_time->format('H:i') : $booking->start_time }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-0.5 rounded-full capitalize">{{ $booking->status }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        @if ($booking->status === 'booked')
                        <form method="POST" action="{{ route('panel.kids-club.cancel', $booking->id) }}" class="inline" onsubmit="return confirm('Batalkan booking ini?')">
                            @csrf
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium transition-colors">Batalkan</button>
                        </form>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">
        {{ $bookings->links() }}
    </div>
</div>
@endif

@endsection
