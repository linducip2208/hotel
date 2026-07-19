@extends('panel.layout')
@section('title', 'Google Hotel Ads')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Google Hotel Ads</h1>
        <p class="text-sm text-gray-500 mt-0.5">Integrasi dengan Google Hotel Ads untuk promosi di Google Search & Maps</p>
    </div>
    <div class="flex items-center gap-2">
        <form method="POST" action="{{ route('panel.marketing.google-hotel-ads.sync') }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Sync Price Feed
            </button>
        </form>
    </div>
</div>

{{-- Status Card --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5 mb-6">
    <div class="flex items-start justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ $status['connected'] ? 'bg-emerald-50' : 'bg-gray-100' }} flex items-center justify-center">
                @if($status['connected'])
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                @else
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                @endif
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">Status Integrasi</h2>
                <p class="text-sm text-gray-500">{{ $status['connected'] ? 'Terhubung' : 'Belum terhubung' }}</p>
                @if($status['connected'] && $status['hotel_id'])
                <p class="text-xs text-gray-400 mt-0.5">Hotel ID: <span class="font-mono">{{ $status['hotel_id'] }}</span></p>
                @endif
            </div>
        </div>
        <div class="text-right">
            @if($status['last_sync'])
            <p class="text-xs text-gray-500">Sinkronisasi Terakhir</p>
            <p class="text-sm font-semibold text-gray-700">{{ \Carbon\Carbon::parse($status['last_sync'])->diffForHumans() }}</p>
            @endif
        </div>
    </div>
    @if(!$status['connected'])
    <div class="mt-4 p-3 bg-amber-50 border border-amber-100 rounded-xl">
        <p class="text-sm text-amber-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
            Tambahkan provider Google Hotel Ads di <a href="{{ route('panel.settings.integrations') }}" class="text-amber-900 underline font-medium">Pengaturan Integrasi</a>
        </p>
    </div>
    @endif
</div>

{{-- Performance Dashboard --}}
<div class="mb-6">
    <h2 class="text-sm font-semibold text-gray-700 mb-3">Performa Iklan (Estimasi)</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Impressions</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($metrics['impressions'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Klik</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ number_format($metrics['clicks'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">CTR</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $metrics['ctr'] }}%</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Booking</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($metrics['bookings'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Pendapatan</p>
            <p class="text-lg font-bold text-emerald-700 mt-1">Rp {{ number_format($metrics['revenue'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">CPA</p>
            <p class="text-lg font-bold text-amber-700 mt-1">Rp {{ number_format($metrics['cpa'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">ROAS</p>
            <p class="text-lg font-bold {{ $metrics['roas'] >= 5 ? 'text-emerald-700' : 'text-amber-700' }} mt-1">{{ $metrics['roas'] }}x</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Konversi</p>
            <p class="text-lg font-bold text-gray-900 mt-1">{{ $metrics['clicks'] > 0 ? round(($metrics['bookings'] / $metrics['clicks']) * 100, 1) : 0 }}%</p>
        </div>
    </div>
</div>

{{-- Price Feed Preview --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden mb-6">
    <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-700">Preview Price Feed (30 Hari)</h2>
        <span class="text-xs text-gray-400">{{ count($priceFeed) }} entri</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Hotel ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe Kamar</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Harga</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Tersedia</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse(array_slice($priceFeed, 0, 20) as $entry)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3 text-gray-600 font-mono text-xs">{{ $entry['hotel_id'] ?? '-' }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $entry['room_type'] }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $entry['date'] }}</td>
                    <td class="px-4 py-3 text-right font-mono text-gray-900">{{ $entry['currency'] }} {{ number_format($entry['price'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($entry['availability'] > 0)
                        <span class="text-xs font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">{{ $entry['availability'] }}</span>
                        @else
                        <span class="text-xs font-medium text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full">Habis</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-10 text-center text-sm text-gray-400">Belum ada data price feed. Klik "Sync Price Feed" untuk generate.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(count($priceFeed) > 20)
    <div class="px-5 py-2 border-t border-gray-100 bg-gray-50/50 text-xs text-gray-400">
        Menampilkan 20 dari {{ count($priceFeed) }} entri.
    </div>
    @endif
</div>

{{-- Documentation --}}
<div class="bg-gradient-to-r from-indigo-50 to-violet-50 rounded-2xl border border-indigo-100 p-6">
    <div class="flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        <div>
            <h3 class="text-sm font-semibold text-indigo-900 mb-1">Dokumentasi Google Hotel Ads</h3>
            <p class="text-sm text-indigo-700 mb-3">Integrasi ini menggunakan Google Hotel Price API untuk mengirimkan harga dan ketersediaan real-time ke Google.</p>
            <ul class="space-y-1 text-xs text-indigo-600">
                <li> Setup provider dengan tipe integrasi <code class="bg-indigo-100 px-1.5 py-0.5 rounded text-indigo-800">google_hotel_ads</code> di Pengaturan Integrasi</li>
                <li> Pastikan Hotel ID dari Google Hotel Center sudah dikonfigurasi</li>
                <li> Sinkronisasi otomatis tiap jam melalui scheduler Laravel</li>
                <li> Performance metrics adalah estimasi — hubungkan Google Ads API untuk data real</li>
            </ul>
        </div>
    </div>
</div>

@endsection
