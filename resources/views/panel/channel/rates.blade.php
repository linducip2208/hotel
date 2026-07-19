@extends('panel.layout')
@section('title', 'Tarif Channel')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.channel.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Tarif Channel</h1>
        <p class="text-sm text-gray-500 mt-0.5">Atur dan kirim tarif ke channel OTA per rentang tanggal</p>
    </div>
</div>

@if($mappings->isEmpty())
<div class="bg-white rounded-2xl shadow-card border border-gray-100 flex flex-col items-center justify-center py-20">
    <div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center mb-5">
        <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
    </div>
    <p class="text-base font-semibold text-gray-700">Belum ada mapping ruangan</p>
    <p class="text-sm text-gray-400 mt-1.5 text-center max-w-sm">Silakan tambahkan room mapping terlebih dahulu di halaman Room Mapping sebelum mengatur tarif channel.</p>
    <a href="{{ route('panel.channel.mapping') }}" class="mt-5 inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
        Buka Room Mapping &rarr;
    </a>
</div>
@else
<form method="POST" action="{{ route('panel.channel.rates.update') }}">
    @csrf @method('PATCH')
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex flex-wrap items-center gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ old('date_from', now()->toDateString()) }}"
                       class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ old('date_to', now()->addMonths(3)->toDateString()) }}"
                       class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div class="ml-auto">
                <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2 rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 0 4 9 9H4m0 0l4-4m-4 4V9"/></svg>
                    Simpan & Push Tarif
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-5 py-3">Channel</th>
                        <th class="px-5 py-3">Tipe Kamar</th>
                        <th class="px-5 py-3">Rate Plan</th>
                        <th class="px-5 py-3">Tarif Dasar (Rp)</th>
                        <th class="px-5 py-3">Tarif Tersimpan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($mappings as $mapping)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-3 font-medium text-gray-900">
                            <input type="hidden" name="mapping_id[]" value="{{ $mapping->id }}">
                            {{ $mapping->channel?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $mapping->roomType?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $mapping->ratePlan?->name ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <div class="relative">
                                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Rp</span>
                                <input type="number" name="base_rate[]"
                                       value="{{ old("base_rate.{$loop->index}", $mapping->config['base_rate'] ?? '') }}"
                                       placeholder="0" min="0" step="1000"
                                       class="w-36 pl-9 border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500">
                            @if(isset($mapping->config['base_rate']))
                            Rp {{ number_format($mapping->config['base_rate'], 0, ',', '.') }}
                            <br><span class="text-gray-400">s.d. {{ $mapping->config['rate_date_to'] ?? '—' }}</span>
                            @else
                            —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</form>
@endif

@endsection
