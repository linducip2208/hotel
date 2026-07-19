@extends('panel.layout')
@section('title', 'Guest LTV Detail')
@section('content')

@php
    $guest = $profile->guest;
@endphp

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.guests.ltv') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $guest?->full_name ?? 'Unknown' }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $guest?->email ?? '-' }} · {{ $guest?->phone ?? '-' }} · {{ $guest?->country ?? '-' }}</p>
    </div>
</div>

@php
    $segLabel = match(true) {
        $profile->total_stays >= 5 && $profile->total_lifetime_value >= 5000000 => 'Champion',
        $profile->total_stays >= 3 => 'Loyal',
        $profile->total_stays >= 2 => 'Potential',
        default => 'New',
    };
    $segColor = match($segLabel) {
        'Champion' => 'violet',
        'Loyal' => 'indigo',
        'Potential' => 'emerald',
        default => 'sky',
    };
@endphp

{{-- Stats grid --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-1">Total Stay</p>
        <p class="text-2xl font-bold text-gray-900">{{ $profile->total_stays }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-1">Total Malam</p>
        <p class="text-2xl font-bold text-gray-900">{{ $profile->total_nights }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-indigo-100 p-4">
        <p class="text-xs text-gray-500 mb-1">LTV</p>
        <p class="text-2xl font-bold text-indigo-700">Rp {{ number_format($profile->total_lifetime_value, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-1">ADR</p>
        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($profile->avg_daily_rate, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-1">Rata2 Lama Stay</p>
        <p class="text-2xl font-bold text-gray-900">{{ $profile->avg_stay_length }} <span class="text-sm font-normal text-gray-400">malam</span></p>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-1">Rata2 Lead Days</p>
        <p class="text-2xl font-bold text-gray-900">{{ $profile->avg_lead_days }} <span class="text-sm font-normal text-gray-400">hari</span></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Behavioral preferences --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Preferensi Perilaku</h2>
        </div>
        <div class="p-5 grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Tipe Kamar Favorit</p>
                <p class="text-sm font-semibold text-gray-900">{{ $profile->preferredRoomType?->name ?? $profile->preferred_room_type_id ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Lantai Favorit</p>
                <p class="text-sm font-semibold text-gray-900">{{ $profile->preferred_floor ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Jenis Bed</p>
                <p class="text-sm font-semibold text-gray-900">{{ $profile->preferred_bed_type ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Hari Check-in</p>
                <p class="text-sm font-semibold text-gray-900">{{ $profile->preferred_check_in_day ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Rata2 Party Size</p>
                <p class="text-sm font-semibold text-gray-900">{{ $profile->avg_party_size }} orang</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Sumber Booking</p>
                <p class="text-sm font-semibold text-gray-900">{{ $profile->primary_booking_source ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Scores --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Skor &amp; Prediksi</h2>
        </div>
        <div class="p-5 space-y-4">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs text-gray-500">Upsell Score</span>
                    <span class="text-xs font-semibold {{ $profile->upsell_score >= 70 ? 'text-amber-700' : ($profile->upsell_score >= 40 ? 'text-blue-700' : 'text-gray-500') }}">{{ $profile->upsell_score }}/100</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full {{ $profile->upsell_score >= 70 ? 'bg-amber-500' : ($profile->upsell_score >= 40 ? 'bg-blue-500' : 'bg-gray-300') }}" style="width: {{ $profile->upsell_score }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs text-gray-500">Churn Risk</span>
                    <span class="text-xs font-semibold {{ $profile->churn_risk_score >= 60 ? 'text-rose-700' : ($profile->churn_risk_score >= 30 ? 'text-amber-700' : 'text-emerald-700') }}">{{ $profile->churn_risk_score }}/100</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full {{ $profile->churn_risk_score >= 60 ? 'bg-rose-500' : ($profile->churn_risk_score >= 30 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $profile->churn_risk_score }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs text-gray-500">Loyalty Score</span>
                    <span class="text-xs font-semibold {{ $profile->loyalty_score >= 70 ? 'text-violet-700' : ($profile->loyalty_score >= 40 ? 'text-indigo-700' : 'text-gray-500') }}">{{ $profile->loyalty_score }}/100</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full {{ $profile->loyalty_score >= 70 ? 'bg-violet-500' : ($profile->loyalty_score >= 40 ? 'bg-indigo-500' : 'bg-gray-300') }}" style="width: {{ $profile->loyalty_score }}%"></div>
                </div>
            </div>
            @if ($profile->avg_review_score)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs text-gray-500">Avg Review Score</span>
                    <span class="text-xs font-semibold text-yellow-700">⭐ {{ number_format($profile->avg_review_score, 1) }}</span>
                </div>
            </div>
            @endif
            <div class="flex items-center gap-2 pt-2">
                <span class="text-xs font-semibold bg-{{ $segColor }}-50 text-{{ $segColor }}-700 px-2.5 py-1 rounded-full">{{ $segLabel }}</span>
                @if ($profile->sentiment)
                <span class="text-xs font-semibold bg-{{ $profile->sentiment === 'positive' ? 'emerald' : ($profile->sentiment === 'neutral' ? 'gray' : 'rose') }}-50 text-{{ $profile->sentiment === 'positive' ? 'emerald' : ($profile->sentiment === 'neutral' ? 'gray' : 'rose') }}-700 px-2.5 py-1 rounded-full capitalize">{{ $profile->sentiment }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Financial breakdown --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 mb-6">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Revenue Breakdown (per stay average)</h2>
    </div>
    <div class="p-5">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @php
                $roomRev = $profile->avg_daily_rate * $profile->avg_stay_length;
            @endphp
            <div class="bg-indigo-50 rounded-xl p-4">
                <p class="text-xs text-indigo-600 mb-0.5">Room Revenue</p>
                <p class="text-lg font-bold text-indigo-800">Rp {{ number_format($roomRev, 0, ',', '.') }}</p>
            </div>
            <div class="bg-orange-50 rounded-xl p-4">
                <p class="text-xs text-orange-600 mb-0.5">F&amp;B Spend</p>
                <p class="text-lg font-bold text-orange-800">Rp {{ number_format($profile->avg_fnb_spend_per_stay, 0, ',', '.') }}</p>
            </div>
            <div class="bg-pink-50 rounded-xl p-4">
                <p class="text-xs text-pink-600 mb-0.5">Spa Spend</p>
                <p class="text-lg font-bold text-pink-800">Rp {{ number_format($profile->avg_spa_spend_per_stay, 0, ',', '.') }}</p>
            </div>
            <div class="bg-teal-50 rounded-xl p-4">
                <p class="text-xs text-teal-600 mb-0.5">Ancillary</p>
                <p class="text-lg font-bold text-teal-800">Rp {{ number_format($profile->avg_ancillary_spend, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Recent stays --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Riwayat Stay Terakhir</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Ref</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Check-in</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Check-out</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Malam</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($guest->reservations ?? [] as $r)
                @php $st = match($r->status) { 'confirmed' => 'blue', 'checked_in' => 'indigo', 'checked_out' => 'gray', 'cancelled' => 'red', 'no_show' => 'orange', default => 'gray' }; @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <a href="{{ route('panel.fo.reservations.show', $r->id) }}" class="font-mono text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">{{ $r->ref }}</a>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-700">{{ $r->check_in?->format('d M Y') ?? '-' }}</td>
                    <td class="px-4 py-3.5 text-sm text-gray-700">{{ $r->check_out?->format('d M Y') ?? '-' }}</td>
                    <td class="px-4 py-3.5 text-center text-sm text-gray-700">{{ $r->check_in && $r->check_out ? $r->check_in->diffInDays($r->check_out) : '-' }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">Rp {{ number_format($r->grand_total, 0, ',', '.') }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-{{ $st }}-50 text-{{ $st }}-700 px-2.5 py-1 rounded-full capitalize">{{ str_replace('_', ' ', $r->status) }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-10 text-center text-sm text-gray-400">Belum ada riwayat stay.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
