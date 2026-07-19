@extends('panel.layout')
@section('title', 'Guest LTV Dashboard')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Guest Lifetime Value</h1>
        <p class="text-sm text-gray-500 mt-0.5">Analisis nilai tamu & prediksi perilaku</p>
    </div>
</div>

{{-- Stats cards --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-1">Total Tamu</p>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_guests'], 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-1">Total LTV</p>
        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($stats['total_ltv'], 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-1">Rata-rata LTV</p>
        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($stats['avg_ltv'], 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-amber-100 p-4">
        <p class="text-xs text-amber-600 mb-1">VIP (Upsell &ge;70)</p>
        <p class="text-2xl font-bold text-amber-700">{{ $stats['vip_count'] }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-rose-100 p-4">
        <p class="text-xs text-rose-600 mb-1">At Risk (Churn &ge;60)</p>
        <p class="text-2xl font-bold text-rose-700">{{ $stats['at_risk_count'] }}</p>
    </div>
</div>

{{-- RFM segments --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $segments = [
            'champions' => ['name' => 'Champions', 'icon' => '🏆', 'color' => 'violet'],
            'loyal' => ['name' => 'Loyal', 'icon' => '💎', 'color' => 'indigo'],
            'potential' => ['name' => 'Potential', 'icon' => '🌱', 'color' => 'emerald'],
            'new' => ['name' => 'New', 'icon' => '🆕', 'color' => 'sky'],
        ];
    @endphp
    @foreach ($segments as $key => $seg)
    <a href="?segment={{ $key }}" class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 hover:shadow-md hover:border-{{ $seg['color'] }}-300 transition-all group">
        <div class="flex items-center justify-between mb-2">
            <div class="w-10 h-10 rounded-xl bg-{{ $seg['color'] }}-50 flex items-center justify-center text-xl">{{ $seg['icon'] }}</div>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-{{ $seg['color'] }}-500 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </div>
        <p class="text-sm font-semibold text-gray-900">{{ $seg['name'] }}</p>
        <p class="text-3xl font-bold text-{{ $seg['color'] }}-700 mt-1">{{ $rfm[$key] }}</p>
        <p class="text-xs text-gray-400 mt-0.5">tamu</p>
    </a>
    @endforeach
</div>

{{-- Filter tabs --}}
<div class="flex items-center gap-2 mb-6">
    <a href="?" class="text-xs font-semibold px-3.5 py-2 rounded-full transition-colors {{ !$segment ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">Semua</a>
    <a href="?segment=vip" class="text-xs font-semibold px-3.5 py-2 rounded-full transition-colors {{ $segment === 'vip' ? 'bg-amber-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">VIP</a>
    <a href="?segment=at_risk" class="text-xs font-semibold px-3.5 py-2 rounded-full transition-colors {{ $segment === 'at_risk' ? 'bg-rose-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">At Risk</a>
    <a href="?segment=champions" class="text-xs font-semibold px-3.5 py-2 rounded-full transition-colors {{ $segment === 'champions' ? 'bg-violet-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">Champions</a>
    <a href="?segment=loyal" class="text-xs font-semibold px-3.5 py-2 rounded-full transition-colors {{ $segment === 'loyal' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">Loyal</a>
    <a href="?segment=potential" class="text-xs font-semibold px-3.5 py-2 rounded-full transition-colors {{ $segment === 'potential' ? 'bg-emerald-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">Potential</a>
    <a href="?segment=new" class="text-xs font-semibold px-3.5 py-2 rounded-full transition-colors {{ $segment === 'new' ? 'bg-sky-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">New</a>
</div>

{{-- Guest table --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tamu</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Stay</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">LTV</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">ADR</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Upsell</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Churn Risk</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Segmen</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($profiles as $p)
                @php
                    $segLabel = match(true) {
                        $p->total_stays >= 5 && $p->total_lifetime_value >= 5000000 => 'Champion',
                        $p->total_stays >= 3 => 'Loyal',
                        $p->total_stays >= 2 => 'Potential',
                        default => 'New',
                    };
                    $segColor = match($segLabel) {
                        'Champion' => 'violet',
                        'Loyal' => 'indigo',
                        'Potential' => 'emerald',
                        default => 'sky',
                    };
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $p->guest?->full_name ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-400">{{ $p->guest?->email ?? '-' }}</p>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-center font-semibold text-gray-900">{{ $p->total_stays }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">Rp {{ number_format($p->total_lifetime_value, 0, ',', '.') }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">Rp {{ number_format($p->avg_daily_rate, 0, ',', '.') }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-semibold {{ $p->upsell_score >= 70 ? 'bg-amber-50 text-amber-700' : ($p->upsell_score >= 40 ? 'bg-blue-50 text-blue-700' : 'bg-gray-50 text-gray-600') }} px-2 py-0.5 rounded-full">{{ $p->upsell_score }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-semibold {{ $p->churn_risk_score >= 60 ? 'bg-rose-50 text-rose-700' : ($p->churn_risk_score >= 30 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700') }} px-2 py-0.5 rounded-full">{{ $p->churn_risk_score }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-semibold bg-{{ $segColor }}-50 text-{{ $segColor }}-700 px-2 py-0.5 rounded-full">{{ $segLabel }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <a href="{{ route('panel.guests.ltv.show', $p->id) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-10 text-center text-sm text-gray-400">Tidak ada data tamu untuk segmen ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($profiles->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">
        {{ $profiles->links() }}
    </div>
    @endif
</div>

@endsection
