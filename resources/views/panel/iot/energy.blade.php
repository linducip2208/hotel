@extends('panel.layout')
@section('title', 'Energy Report')
@section('content')

@php
    $totalKwh = $report['totalKwh'] ?? 0;
    $totalCost = $report['totalCost'] ?? 0;
    $byRoom = $report['byRoom'] ?? [];
@endphp

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Laporan Energi IoT</h1>
        <p class="text-sm text-gray-500 mt-0.5">Konsumsi energi per kamar dari perangkat IoT</p>
    </div>
    <form method="GET" class="flex items-center gap-2">
        <input type="date" name="from" value="{{ $from }}"
               class="text-sm border border-gray-200 rounded-xl px-3 py-2 bg-white text-gray-700 hover:border-indigo-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all outline-none">
        <span class="text-sm text-gray-400">s/d</span>
        <input type="date" name="to" value="{{ $to }}"
               class="text-sm border border-gray-200 rounded-xl px-3 py-2 bg-white text-gray-700 hover:border-indigo-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all outline-none">
        <button class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Filter
        </button>
    </form>
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Total kWh</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalKwh, 2) }} <span class="text-sm font-normal text-gray-400">kWh</span></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Estimasi Biaya</p>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalCost, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Per-room breakdown --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Per-Kamar</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kamar</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">kWh</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Estimasi Biaya</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($byRoom as $row)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 font-medium text-gray-900">{{ $row['room'] }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">{{ number_format($row['kwh'], 2) }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">Rp {{ number_format($row['cost'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="py-10 text-center text-sm text-gray-400">Tidak ada data energi untuk periode ini.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-gray-50/50 border-t border-gray-100 font-semibold">
                    <td class="px-5 py-3 text-sm text-gray-700">Total</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-900">{{ number_format($totalKwh, 2) }} kWh</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-900">Rp {{ number_format($totalCost, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@endsection
