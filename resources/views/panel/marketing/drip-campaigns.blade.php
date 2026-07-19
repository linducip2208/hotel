@extends('panel.layout')
@section('title', 'Drip Campaign')
@section('content')

@php
$triggerLabels = [
    'booking_confirmed' => 'Booking Dikonfirmasi',
    'checkin' => 'Check-in',
    'checkout' => 'Check-out',
    'post_stay' => 'Pasca Menginap',
    'birthday' => 'Ulang Tahun',
    'inactive' => 'Tamu Non-aktif',
];
@endphp

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Drip Campaign</h1>
        <p class="text-sm text-gray-500 mt-0.5">Otomatis kirim pesan bertahap berdasarkan trigger event</p>
    </div>
    <a href="{{ route('panel.marketing.drip-campaigns.create') }}"
       class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Campaign Baru
    </a>
</div>

{{-- Queue Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Pending</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $queueStats['pending'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Terkirim</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $queueStats['sent'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Gagal</p>
        <p class="text-2xl font-bold text-rose-600 mt-1">{{ $queueStats['failed'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Total</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $queueStats['total'] }}</p>
    </div>
</div>

{{-- Campaign List --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden mb-6">
    <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-700">Daftar Campaign</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Trigger</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Steps</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Queue</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($campaigns as $c)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 font-medium text-gray-900">{{ $c->name }}</td>
                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                            {{ $triggerLabels[$c->trigger_event] ?? $c->trigger_event }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center text-gray-600">{{ $c->steps_count }}</td>
                    <td class="px-4 py-3.5 text-center text-gray-600">{{ $c->queue_items_count }}</td>
                    <td class="px-4 py-3.5 text-center">
                        @if($c->is_active)
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Aktif
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>Non-aktif
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('panel.marketing.drip-campaigns.edit', $c->id) }}"
                               class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('panel.marketing.drip-campaigns.destroy', $c->id) }}"
                                  onsubmit="return confirm('Hapus campaign ini?')">
                                @csrf @method('DELETE')
                                <button class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">Belum ada drip campaign. <a href="{{ route('panel.marketing.drip-campaigns.create') }}" class="text-indigo-600 hover:underline">Buat sekarang →</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Recent Queue --}}
@if($recentQueue->isNotEmpty())
<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
    <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-700">Antrian Terbaru</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Campaign</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tamu</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Jadwal</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($recentQueue as $q)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-2.5 text-gray-700">{{ $q->dripStep?->campaign?->name ?? '-' }}</td>
                    <td class="px-4 py-2.5 text-gray-700">{{ $q->guest?->full_name ?? '-' }}</td>
                    <td class="px-4 py-2.5 text-gray-500">{{ $q->scheduled_at?->format('d M Y H:i') }}</td>
                    <td class="px-4 py-2.5 text-center">
                        @if($q->status === 'sent')
                        <span class="text-xs text-emerald-600 font-medium">Terkirim</span>
                        @elseif($q->status === 'failed')
                        <span class="text-xs text-rose-600 font-medium">Gagal</span>
                        @else
                        <span class="text-xs text-amber-600 font-medium">Pending</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
