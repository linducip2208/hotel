@extends('panel.layout')
@section('title', 'Reservations')
@section('content')

{{-- Page Header --}}
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Reservasi</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola semua reservasi tamu hotel</p>
        </div>
        <a href="{{ route('panel.fo.reservations.create') }}"
           class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Reservasi Baru
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4 mb-4">
    <form method="GET" class="grid sm:grid-cols-12 gap-3 items-end">
        <div class="sm:col-span-5">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Cari (Ref / Nama / Email)</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="HMS-... atau nama tamu"
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
        </div>
        <div class="sm:col-span-3">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status</label>
            <select name="status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                <option value="">Semua status</option>
                @foreach (['confirmed' => 'Confirmed', 'tentative' => 'Tentative', 'checked_in' => 'Checked In', 'checked_out' => 'Checked Out', 'cancelled' => 'Cancelled', 'no_show' => 'No Show'] as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Menginap pada</label>
            <input type="date" name="date" value="{{ request('date') }}"
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
        </div>
        <div class="sm:col-span-2 flex gap-2">
            <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition">Filter</button>
            <a href="{{ route('panel.fo.reservations.index') }}" title="Bersihkan filter"
               class="px-3 py-2 border border-gray-200 text-gray-500 hover:bg-gray-50 rounded-xl text-sm">Reset</a>
        </div>
    </form>
</div>

{{-- Table Card --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Ref</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tamu</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Check-in</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Check-out</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
                    <th class="px-5 py-3.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse ($reservations as $r)
                @php
                    $badge = match ($r->status) {
                        'confirmed'   => 'bg-emerald-100 text-emerald-700',
                        'checked_in'  => 'bg-blue-100 text-blue-700',
                        'checked_out' => 'bg-gray-100 text-gray-600',
                        'cancelled'   => 'bg-red-100 text-red-700',
                        'tentative'   => 'bg-yellow-100 text-yellow-700',
                        'no_show'     => 'bg-orange-100 text-orange-700',
                        default       => 'bg-gray-100 text-gray-500',
                    };
                    $label = match ($r->status) {
                        'confirmed'   => 'Confirmed',
                        'checked_in'  => 'Checked In',
                        'checked_out' => 'Checked Out',
                        'cancelled'   => 'Cancelled',
                        'tentative'   => 'Tentative',
                        'no_show'     => 'No Show',
                        default       => ucfirst($r->status),
                    };
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="font-mono text-xs font-semibold text-gray-700 bg-gray-100 px-2 py-1 rounded-lg">{{ $r->ref }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-primary-600">{{ strtoupper(substr($r->primaryGuest?->full_name ?? '?', 0, 1)) }}</span>
                            </div>
                            <span class="font-medium text-gray-900">{{ $r->primaryGuest?->full_name ?? '—' }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $r->check_in->format('d M Y') }}</td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $r->check_out->format('d M Y') }}</td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-right font-mono text-sm font-semibold text-gray-800">
                        Rp {{ number_format($r->grand_total, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('panel.fo.reservations.show', $r->id) }}"
                           class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-800 text-xs font-medium transition">
                            Detail
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700">
                                    {{ (request('q') || request('status') || request('date')) ? 'Tidak ada hasil untuk filter ini' : 'Belum ada data' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ (request('q') || request('status') || request('date')) ? 'Coba ubah kata kunci atau reset filter' : 'Buat reservasi pertama Anda' }}
                                </p>
                            </div>
                            @if (request('q') || request('status') || request('date'))
                                <a href="{{ route('panel.fo.reservations.index') }}"
                                   class="mt-1 inline-flex items-center gap-1.5 border border-gray-200 text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-xl text-xs font-medium transition">
                                    Reset Filter
                                </a>
                            @else
                                <a href="{{ route('panel.fo.reservations.create') }}"
                                   class="mt-1 inline-flex items-center gap-1.5 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-xs font-medium transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Buat Reservasi
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if ($reservations->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
            {{ $reservations->links() }}
        </div>
    @endif
</div>

@endsection
