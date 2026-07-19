@extends('panel.layout')
@section('title', 'Restriksi Channel')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.channel.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Restriksi Channel</h1>
        <p class="text-sm text-gray-500 mt-0.5">Atur CTA, CTD, dan MinLOS per channel room mapping</p>
    </div>
</div>

@if($mappings->isEmpty())
<div class="bg-white rounded-2xl shadow-card border border-gray-100 flex flex-col items-center justify-center py-20">
    <div class="w-16 h-16 rounded-2xl bg-violet-50 flex items-center justify-center mb-5">
        <svg class="w-8 h-8 text-violet-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
        </svg>
    </div>
    <p class="text-base font-semibold text-gray-700">Belum ada mapping ruangan</p>
    <p class="text-sm text-gray-400 mt-1.5 text-center max-w-sm">Silakan tambahkan room mapping terlebih dahulu di halaman Room Mapping sebelum mengatur restriksi.</p>
    <a href="{{ route('panel.channel.mapping') }}" class="mt-5 inline-flex items-center gap-1.5 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
        Buka Room Mapping &rarr;
    </a>
</div>
@else
<form method="POST" action="{{ route('panel.channel.restrictions.store') }}">
    @csrf
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex flex-wrap items-center gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ old('date_from', now()->toDateString()) }}"
                       class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500" required>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ old('date_to', now()->addMonths(3)->toDateString()) }}"
                       class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500" required>
            </div>
            <div class="ml-auto">
                <button type="submit" class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold px-5 py-2 rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Simpan Restriksi
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-5 py-3">Channel</th>
                        <th class="px-5 py-3">Room Type</th>
                        <th class="px-5 py-3">Rate Plan</th>
                        <th class="px-5 py-3">CTA (hari)</th>
                        <th class="px-5 py-3">CTD (hari)</th>
                        <th class="px-5 py-3">Min LOS</th>
                        <th class="px-5 py-3">Max LOS</th>
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
                            <input type="number" name="cta_days[]"
                                   value="{{ old("cta_days.{$loop->index}", $mapping->restrictions['cta_days'] ?? '') }}"
                                   placeholder="—" min="0" max="365"
                                   class="w-20 border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                        </td>
                        <td class="px-5 py-3">
                            <input type="number" name="ctd_days[]"
                                   value="{{ old("ctd_days.{$loop->index}", $mapping->restrictions['ctd_days'] ?? '') }}"
                                   placeholder="—" min="0" max="365"
                                   class="w-20 border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                        </td>
                        <td class="px-5 py-3">
                            <input type="number" name="min_los[]"
                                   value="{{ old("min_los.{$loop->index}", $mapping->restrictions['min_los'] ?? '') }}"
                                   placeholder="—" min="1" max="30"
                                   class="w-20 border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                        </td>
                        <td class="px-5 py-3">
                            <input type="number" name="max_los[]"
                                   value="{{ old("max_los.{$loop->index}", $mapping->restrictions['max_los'] ?? '') }}"
                                   placeholder="—" min="1" max="30"
                                   class="w-20 border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-gray-50 border-t border-gray-100 text-xs text-gray-500">
            <p><strong>CTA</strong> (Close to Arrival): X hari sebelum check-in, booking tidak bisa dilakukan.</p>
            <p><strong>CTD</strong> (Close to Departure): X hari sebelum check-out, booking tidak bisa dilakukan.</p>
            <p><strong>Min LOS</strong>: Minimum lama menginap (malam). <strong>Max LOS</strong>: Maksimum lama menginap (malam).</p>
        </div>
    </div>
</form>
@endif

@endsection
