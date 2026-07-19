@extends('portal.layout')
@section('title', 'Tagihan Saya — Portal Tamu')

@section('content')
<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-display text-2xl font-bold text-slate-900 mb-1">Tagihan Saya</h1>
            <p class="text-slate-500 text-sm">Semua tagihan dan status pembayaran.</p>
        </div>
    </div>

    @if($folios->isEmpty())
        <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-slate-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-slate-500 text-lg mb-2">Belum ada tagihan</p>
            <p class="text-slate-400 text-sm">Tagihan akan muncul setelah Anda melakukan reservasi.</p>
        </div>
    @else
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left">
                            <th class="px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Folio</th>
                            <th class="px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider hidden sm:table-cell">Reservasi</th>
                            <th class="px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Total Tagihan</th>
                            <th class="px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Dibayar</th>
                            <th class="px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Sisa</th>
                            <th class="px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($folios as $folio)
                            <tr class="hover:bg-slate-50 transition-colors border-t border-slate-100">
                                <td class="px-5 py-4">
                                    <a href="{{ route('customer.invoices.show', $folio->id) }}" class="font-mono text-indigo-600 hover:text-indigo-700 font-medium">
                                        {{ $folio->folio_no ?? 'Folio #' . $folio->id }}
                                    </a>
                                </td>
                                <td class="px-5 py-4 text-slate-700 hidden sm:table-cell">
                                    {{ $folio->reservation?->ref ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-4 font-medium text-slate-800">
                                    Rp {{ number_format($folio->total_charges, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 text-slate-700">
                                    Rp {{ number_format($folio->total_payments, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 font-bold {{ $folio->balance > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                    Rp {{ number_format($folio->balance, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $folio->balance > 0 ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">
                                        {{ $folio->balance > 0 ? 'Belum Lunas' : 'Lunas' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('customer.invoices.show', $folio->id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">
                                        {{ $folio->balance > 0 ? 'Bayar' : 'Lihat' }} &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($folios->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">
                    {{ $folios->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
