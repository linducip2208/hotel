@extends('panel.layout')
@section('title', 'Insiden — Keamanan')
@section('content')

@php
$severityColors = ['low'=>'green','medium'=>'amber','high'=>'red','critical'=>'purple'];
$severityLabels = ['low'=>'Rendah','medium'=>'Sedang','high'=>'Tinggi','critical'=>'Kritis'];
$statusColors = ['open'=>'red','investigating'=>'amber','resolved'=>'emerald','closed'=>'gray'];
$statusLabels = ['open'=>'Terbuka','investigating'=>'Investigasi','resolved'=>'Selesai','closed'=>'Ditutup'];
$typeLabels = [
    'guest_injury'=>'Cedera Tamu','guest_illness'=>'Sakit Tamu','theft'=>'Pencurian',
    'property_damage'=>'Kerusakan Properti','staff_injury'=>'Cedera Staf','security'=>'Keamanan',
    'fire'=>'Kebakaran','flood'=>'Banjir','complaint'=>'Keluhan','other'=>'Lainnya'
];
@endphp

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Laporan Insiden</h1>
        <p class="text-sm text-gray-500 mt-0.5">Catat, investigasi, dan selesaikan insiden keamanan & keselamatan</p>
    </div>
    <a href="{{ route('panel.security.incidents.create') }}"
       class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Lapor Insiden
    </a>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4 text-center">
        <p class="text-3xl font-bold text-red-600">{{ $stats['open'] }}</p>
        <p class="text-xs text-gray-400 mt-1 uppercase tracking-wider font-semibold">Terbuka</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4 text-center">
        <p class="text-3xl font-bold text-amber-600">{{ $stats['investigating'] }}</p>
        <p class="text-xs text-gray-400 mt-1 uppercase tracking-wider font-semibold">Investigasi</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4 text-center">
        <p class="text-3xl font-bold text-emerald-600">{{ $stats['resolved_this_month'] }}</p>
        <p class="text-xs text-gray-400 mt-1 uppercase tracking-wider font-semibold">Selesai Bulan Ini</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4 text-center">
        <p class="text-3xl font-bold text-purple-600">{{ $stats['critical'] }}</p>
        <p class="text-xs text-gray-400 mt-1 uppercase tracking-wider font-semibold">Kritis</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4 text-center">
        <p class="text-3xl font-bold text-gray-700">{{ $stats['total'] }}</p>
        <p class="text-xs text-gray-400 mt-1 uppercase tracking-wider font-semibold">Total</p>
    </div>
</div>

{{-- Filters --}}
<div class="mb-6 flex flex-wrap items-center gap-3">
    <form method="GET" class="flex items-center gap-3 flex-wrap">
        <select name="severity" onchange="this.form.submit()"
                class="bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Severity</option>
            <option value="low" {{ request('severity')==='low'?'selected':'' }}>Rendah</option>
            <option value="medium" {{ request('severity')==='medium'?'selected':'' }}>Sedang</option>
            <option value="high" {{ request('severity')==='high'?'selected':'' }}>Tinggi</option>
            <option value="critical" {{ request('severity')==='critical'?'selected':'' }}>Kritis</option>
        </select>
        <select name="status" onchange="this.form.submit()"
                class="bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Status</option>
            <option value="open" {{ request('status')==='open'?'selected':'' }}>Terbuka</option>
            <option value="investigating" {{ request('status')==='investigating'?'selected':'' }}>Investigasi</option>
            <option value="resolved" {{ request('status')==='resolved'?'selected':'' }}>Selesai</option>
            <option value="closed" {{ request('status')==='closed'?'selected':'' }}>Ditutup</option>
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm">
        <span class="text-gray-400 text-xs">s/d</span>
        <input type="date" name="to" value="{{ request('to') }}" class="bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm">
        <button type="submit"
                class="inline-flex items-center gap-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Filter
        </button>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">No. Laporan</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipe</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Severity</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Lokasi</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pelapor</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($incidents as $incident)
                <tr class="hover:bg-indigo-50/30 transition-colors">
                    <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600">{{ $incident->report_number }}</td>
                    <td class="px-4 py-3"><span class="text-xs">{{ $typeLabels[$incident->incident_type] ?? $incident->incident_type }}</span></td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-{{ $severityColors[$incident->severity] }}-50 text-{{ $severityColors[$incident->severity] }}-700 border border-{{ $severityColors[$incident->severity] }}-200">
                            {{ $severityLabels[$incident->severity] ?? $incident->severity }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">{{ $incident->location ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $incident->incident_date->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3 text-xs">{{ $incident->reported_by ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-{{ $statusColors[$incident->status] }}-50 text-{{ $statusColors[$incident->status] }}-700 border border-{{ $statusColors[$incident->status] }}-200">
                            {{ $statusLabels[$incident->status] ?? $incident->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('panel.security.incidents.show', $incident->id) }}"
                           class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        <p class="text-sm">Tidak ada laporan insiden ditemukan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($incidents->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/30">
        {{ $incidents->links() }}
    </div>
    @endif
</div>
@endsection
