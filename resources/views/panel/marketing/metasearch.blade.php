@extends('panel.layout')
@section('title', 'Metasearch — Pemasaran')
@section('content')

@php $prop = app('current_property'); @endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Metasearch Integration</h1>
    <p class="text-sm text-gray-500 mt-0.5">Kelola feed harga dan ketersediaan untuk Google Hotel Ads, Trivago, Kayak, dan Tripadvisor</p>
</div>

{{-- Channel Status Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach($channels as $channel)
    @php
    $connected = \App\Services\Marketing\MetasearchService::class;
    $isConnected = (new $connected)->isConnected($prop, $channel['code']);
    $perf = $performance[$channel['code']] ?? null;
    @endphp
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5 relative overflow-hidden">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="font-bold text-gray-900">{{ $channel['name'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $channel['code'] }}</p>
            </div>
            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $isConnected ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $isConnected ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                {{ $isConnected ? 'Tersambung' : 'Belum Setup' }}
            </span>
        </div>
        @if($perf)
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div><span class="text-gray-400">Klik</span><p class="font-semibold text-gray-900">{{ number_format($perf['clicks']) }}</p></div>
            <div><span class="text-gray-400">Booking</span><p class="font-semibold text-gray-900">{{ $perf['bookings'] }}</p></div>
            <div><span class="text-gray-400">CTR</span><p class="font-semibold text-gray-900">{{ $perf['ctr'] }}%</p></div>
            <div><span class="text-gray-400">Revenue</span><p class="font-semibold text-emerald-600">Rp {{ number_format($perf['revenue'], 0, ',', '.') }}</p></div>
        </div>
        @endif
    </div>
    @endforeach
</div>

{{-- Feed Generation --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-card p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Generate Feed</h2>
        <form method="GET" action="{{ route('panel.marketing.metasearch.feed') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Channel</label>
                <select name="channel" class="bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    @foreach($channels as $ch)
                    <option value="{{ $ch['code'] }}">{{ $ch['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Format</label>
                <select name="format" class="bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="csv">CSV</option>
                    <option value="xml">XML</option>
                    <option value="json">JSON</option>
                </select>
            </div>
            <button type="submit" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Preview Feed
            </button>
        </form>

        <div class="flex flex-wrap gap-2 mt-4">
            @foreach($channels as $ch)
            <a href="{{ route('panel.marketing.metasearch.download', ['channel' => $ch['code'], 'format' => 'csv']) }}"
               class="inline-flex items-center gap-1 text-xs bg-gray-50 hover:bg-gray-100 text-gray-600 font-medium px-3 py-1.5 rounded-lg border border-gray-200 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                {{ $ch['name'] }} CSV
            </a>
            <a href="{{ route('panel.marketing.metasearch.download', ['channel' => $ch['code'], 'format' => 'xml']) }}"
               class="inline-flex items-center gap-1 text-xs bg-gray-50 hover:bg-gray-100 text-gray-600 font-medium px-3 py-1.5 rounded-lg border border-gray-200 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                {{ $ch['name'] }} XML
            </a>
            @endforeach
        </div>
    </div>

    {{-- Optimization --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Optimasi Bid</h2>
        @php $bid = (new \App\Services\Marketing\MetasearchService)->optimizeBid($prop, 'google'); @endphp
        <div class="space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-gray-400">Multiplier Saat Ini</span><span class="font-bold">{{ $bid['current_bid_multiplier'] }}x</span></div>
            <div class="flex justify-between"><span class="text-gray-400">Rekomendasi</span><span class="font-bold text-indigo-600">{{ $bid['recommended_bid_multiplier'] }}x</span></div>
            <div class="flex justify-between"><span class="text-gray-400">Estimasi Perubahan Tayang</span><span class="font-bold text-emerald-600">{{ $bid['estimated_impression_change'] }}</span></div>
            <div class="flex justify-between"><span class="text-gray-400">Estimasi Perubahan Biaya</span><span class="font-bold text-amber-600">{{ $bid['estimated_cost_change'] }}</span></div>
        </div>
    </div>
</div>

{{-- Performance Table --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-lg font-bold text-gray-900">Metrik Performa per Channel</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Channel</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tayang</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Klik</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">CTR</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Booking</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Revenue</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">CPA</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($channels as $ch)
                @php $p = $performance[$ch['code']] ?? null; @endphp
                <tr class="hover:bg-indigo-50/30 transition-colors">
                    <td class="px-5 py-3 font-semibold text-gray-900">{{ $ch['name'] }}</td>
                    <td class="px-5 py-3 text-right">{{ $p ? number_format($p['impressions']) : '-' }}</td>
                    <td class="px-5 py-3 text-right">{{ $p ? number_format($p['clicks']) : '-' }}</td>
                    <td class="px-5 py-3 text-right">{{ $p ? $p['ctr'] . '%' : '-' }}</td>
                    <td class="px-5 py-3 text-right font-semibold">{{ $p['bookings'] ?? '-' }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-emerald-600">{{ $p ? 'Rp ' . number_format($p['revenue'], 0, ',', '.') : '-' }}</td>
                    <td class="px-5 py-3 text-right text-xs">{{ $p ? 'Rp ' . number_format($p['cpa'], 0, ',', '.') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
