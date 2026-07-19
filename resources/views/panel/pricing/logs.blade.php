@extends('panel.layout')
@section('title', 'Log Perubahan Harga')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Log Perubahan Harga</h1>
        <p class="text-sm text-gray-500 mt-0.5">Riwayat perubahan harga dinamis oleh sistem</p>
    </div>
    <a href="{{ route('panel.pricing.rules') }}"
       class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 px-3.5 py-2 rounded-xl hover:bg-gray-50 shadow-card transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Aturan Harga
    </a>
</div>

{{-- Filter Bar --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4 mb-5">
    <form method="GET" action="{{ route('panel.pricing.logs') }}" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Dari Tanggal</label>
            <input type="date" name="from_date" value="{{ request('from_date') }}"
                   class="rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Sampai Tanggal</label>
            <input type="date" name="to_date" value="{{ request('to_date') }}"
                   class="rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
        </div>
        <button type="submit"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Filter
        </button>
        @if(request('from_date') || request('to_date'))
        <a href="{{ route('panel.pricing.logs') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-xl hover:bg-gray-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            Reset
        </a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    @if(($logs ?? collect())->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Target Tgl</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe Kamar</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Channel</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Aturan</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Harga Awal</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Harga Akhir</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Perubahan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Pemicu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($logs as $log)
                @php
                    $change = ($log->price_after ?? 0) - ($log->price_before ?? 0);
                    $changePct = ($log->price_before ?? 0) > 0 ? round(($change / $log->price_before) * 100, 1) : 0;
                    $isIncrease = $change > 0;
                    $isNeutral = $change == 0;
                    $deltaColor = $isNeutral ? 'gray' : ($isIncrease ? 'emerald' : 'red');
                    $deltaIcon = $isNeutral ? '—' : ($isIncrease ? '↑' : '↓');
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 text-xs text-gray-500">{{ $log->created_at?->format('d M Y, H:i') ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">{{ $log->target_date ? $log->target_date->format('d M Y') : '—' }}</td>
                    <td class="px-4 py-3.5 text-sm text-gray-800">{{ $log->roomType?->name ?? '—' }}</td>
                    <td class="px-4 py-3.5">
                        @if($log->channel)
                        <span class="text-xs font-medium text-gray-700 bg-gray-100 px-2 py-0.5 rounded-md">{{ $log->channel->name }}</span>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-700">{{ $log->rule_name ?? $log->rule?->name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-right text-sm text-gray-600 tabular-nums">
                        Rp {{ number_format($log->price_before ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-right text-sm font-semibold text-gray-800 tabular-nums">
                        Rp {{ number_format($log->price_after ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-{{ $deltaColor }}-50 text-{{ $deltaColor }}-700 tabular-nums">
                            {{ $deltaIcon }} Rp {{ number_format(abs($change), 0, ',', '.') }}
                            @if(!$isNeutral)
                            <span class="text-[10px] opacity-70">({{ $changePct }}%)</span>
                            @endif
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-500 max-w-[200px] truncate">
                        {{ $log->trigger_reason ?? '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(($logs ?? collect())->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
        {{ $logs->links() }}
    </div>
    @endif
    @else
    <div class="flex flex-col items-center justify-center py-20">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mb-5">
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-base font-semibold text-gray-600">Belum ada log perubahan harga</p>
        <p class="text-sm text-gray-400 mt-1.5 text-center max-w-sm">Log akan tercatat secara otomatis setiap kali aturan harga dinamis mengubah harga kamar.</p>
    </div>
    @endif
</div>

@endsection
