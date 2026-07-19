@extends('portal.layout')
@section('title', 'Pesanan Saya — Portal Tamu')

@section('content')
<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-display text-2xl font-bold text-slate-900 mb-1">Pesanan Saya</h1>
            <p class="text-slate-500 text-sm">Semua riwayat reservasi Anda.</p>
        </div>
    </div>

    @if($reservations->isEmpty())
        <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-slate-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <p class="text-slate-500 text-lg mb-2">Belum ada pemesanan</p>
            <p class="text-slate-400 text-sm mb-6">Saat Anda melakukan reservasi, semua pesanan akan muncul di sini.</p>
            <a href="/booking" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-6 py-3 rounded-xl shadow-lg shadow-indigo-500/25 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Buat Reservasi Baru
            </a>
        </div>
    @else
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left">
                            <th class="px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Ref</th>
                            <th class="px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Check-in</th>
                            <th class="px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Check-out</th>
                            <th class="px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider hidden sm:table-cell">Tamu</th>
                            <th class="px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Total</th>
                            <th class="px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reservations as $res)
                            <tr class="hover:bg-slate-50 transition-colors border-t border-slate-100">
                                <td class="px-5 py-4">
                                    <a href="{{ route('customer.bookings.show', $res->id) }}" class="font-mono text-indigo-600 hover:text-indigo-700 font-medium">{{ $res->ref }}</a>
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ $res->check_in->format('d M Y') }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $res->check_out->format('d M Y') }}</td>
                                <td class="px-5 py-4 text-slate-700 hidden sm:table-cell">{{ $res->primaryGuest?->full_name ?? $guest->name }}</td>
                                <td class="px-5 py-4 font-medium text-slate-800">Rp {{ number_format($res->grand_total, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
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
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('customer.bookings.show', $res->id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">Detail &rarr;</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($reservations->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">
                    {{ $reservations->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
