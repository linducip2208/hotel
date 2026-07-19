@extends('portal.owner.layout')
@section('title', 'Riwayat Distribusi')
@section('content')

@php
    $fmtRupiah = function($val) { return 'Rp ' . number_format(abs($val), 0, ',', '.'); };
@endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900 font-display">Riwayat Distribusi</h1>
    <p class="text-sm text-slate-500 mt-1">{{ $property->name }} &mdash; Riwayat pembayaran distribusi laba</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 border-b border-slate-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Periode</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Pendapatan</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Biaya</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Laba Bersih</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Persentase</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Jumlah Distribusi</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Dibayar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($distributions as $d)
                @php
                    $sc = match($d->status) {
                        'paid' => 'emerald',
                        'pending' => 'amber',
                        default => 'gray'
                    };
                @endphp
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-5 py-3.5 text-sm font-medium text-slate-800">
                        {{ \Carbon\Carbon::parse($d->period_start)->isoFormat('DD MMM') }} &mdash; {{ \Carbon\Carbon::parse($d->period_end)->isoFormat('DD MMM Y') }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-slate-700">
                        {{ $fmtRupiah($d->total_revenue) }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-rose-500">
                        ({{ $fmtRupiah($d->total_expense) }})
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-slate-700">
                        {{ $fmtRupiah($d->net_profit) }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-slate-600">
                        {{ number_format($d->distribution_pct, 1) }}%
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm font-bold text-slate-900">
                        {{ $fmtRupiah($d->distribution_amount) }}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-{{ $sc }}-50 text-{{ $sc }}-700">
                            {{ $d->status === 'paid' ? 'Dibayar' : 'Pending' }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-slate-500">
                        {{ $d->paid_at ? \Carbon\Carbon::parse($d->paid_at)->format('d M Y') : '—' }}
                        @if($d->payment_method)
                            <br><span class="text-xs text-slate-400">{{ $d->payment_method }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                            <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-sm font-medium text-slate-600">Belum ada distribusi</p>
                            <p class="text-xs mt-1">Distribusi akan muncul setelah admin membuat laporan distribusi</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($distributions->hasPages())
    <div class="px-5 py-3 border-t border-slate-100">
        {{ $distributions->links() }}
    </div>
    @endif
</div>
@endsection
