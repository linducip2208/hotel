@extends('portal.layout')
@section('title', 'Dashboard — Portal Tamu')

@section('content')
<div>
    <h1 class="font-display text-2xl font-bold text-slate-900 mb-1">Selamat Datang, {{ $guest->name }}</h1>
    <p class="text-slate-500 text-sm mb-8">Kelola pemesanan dan tagihan Anda di sini.</p>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 hover:shadow-lg hover:border-indigo-200 transition-all duration-300 card-lift cursor-default">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Booking Aktif</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900 tracking-tight">{{ $activeBookings }}</p>
            <p class="text-xs text-slate-400 mt-1">Reservasi berjalan</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 hover:shadow-lg hover:border-emerald-200 transition-all duration-300 card-lift cursor-default">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Pengeluaran</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900 tracking-tight">Rp {{ number_format($totalSpent, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Total seluruh masa inap</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 hover:shadow-lg hover:border-amber-200 transition-all duration-300 card-lift cursor-default">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Check-in Mendatang</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900 tracking-tight">
                @if($upcomingCheckin)
                    {{ $upcomingCheckin->check_in->format('d M Y') }}
                @else
                    &mdash;
                @endif
            </p>
            <p class="text-xs text-slate-400 mt-1">
                @if($upcomingCheckin)
                    {{ $upcomingCheckin->ref }}
                @else
                    Tidak ada
                @endif
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 hover:shadow-lg hover:border-sky-200 transition-all duration-300 card-lift cursor-default">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Masa Inap Lalu</span>
                <div class="w-9 h-9 rounded-xl bg-sky-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900 tracking-tight">{{ $pastStays }}</p>
            <p class="text-xs text-slate-400 mt-1">Riwayat menginap</p>
        </div>
    </div>

    {{-- Recent Reservations --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-slate-800">Reservasi Terbaru</h2>
                <a href="{{ route('customer.bookings') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">Lihat Semua &rarr;</a>
            </div>

            @if($recentReservations->isEmpty())
                <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center">
                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="text-slate-500 text-sm">Belum ada reservasi.</p>
                </div>
            @else
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                    @foreach($recentReservations as $res)
                        <a href="{{ route('customer.bookings.show', $res->id) }}" class="flex items-center justify-between px-5 py-4 hover:bg-slate-50 transition-colors {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                            <div>
                                <p class="font-medium text-slate-800 text-sm">{{ $res->ref }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    {{ $res->check_in->format('d M Y') }} &rarr; {{ $res->check_out->format('d M Y') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ match($res->status) {
                                        'confirmed' => 'bg-blue-50 text-blue-700',
                                        'checked_in' => 'bg-emerald-50 text-emerald-700',
                                        'checked_out', 'completed' => 'bg-slate-100 text-slate-600',
                                        'cancelled' => 'bg-red-50 text-red-600',
                                        default => 'bg-stone-50 text-stone-600'
                                    } }}">
                                    {{ match($res->status) {
                                        'confirmed' => 'Dikonfirmasi',
                                        'checked_in' => 'Check-in',
                                        'checked_out' => 'Check-out',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                        default => ucfirst($res->status)
                                    } }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Recent Folios / Invoices --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-slate-800">Tagihan Terbaru</h2>
                <a href="{{ route('customer.invoices') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">Lihat Semua &rarr;</a>
            </div>

            @if($recentFolios->isEmpty())
                <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center">
                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-slate-500 text-sm">Belum ada tagihan.</p>
                </div>
            @else
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                    @foreach($recentFolios as $folio)
                        <a href="{{ route('customer.invoices.show', $folio->id) }}" class="flex items-center justify-between px-5 py-4 hover:bg-slate-50 transition-colors {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                            <div>
                                <p class="font-medium text-slate-800 text-sm">{{ $folio->folio_no ?? 'Folio #' . $folio->id }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $folio->reservation?->ref ?? 'N/A' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-slate-800 text-sm">Rp {{ number_format($folio->balance, 0, ',', '.') }}</p>
                                <span class="text-xs
                                    {{ $folio->balance > 0 ? 'text-red-500' : 'text-emerald-600' }}">
                                    {{ $folio->balance > 0 ? 'Belum Lunas' : 'Lunas' }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
