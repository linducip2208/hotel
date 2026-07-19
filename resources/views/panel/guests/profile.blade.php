@extends('panel.layout')
@section('title', 'Guest 360 Profile — ' . $guest->full_name)
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.guests.show', $guest->id) }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900">{{ $guest->full_name }}</h1>
            @if ($guest->is_vip)
            <span class="text-xs font-semibold bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full">VIP</span>
            @endif
            <span class="text-xs font-medium bg-violet-50 text-violet-700 px-2.5 py-1 rounded-full">360° Profile</span>
        </div>
        <p class="text-sm text-gray-500 mt-0.5">{{ $guest->email }} · {{ $guest->phone }} · {{ $guest->nationality }}</p>
    </div>
    <form method="POST" action="{{ route('panel.guests.profile.rebuild', $guest->id) }}">
        @csrf
        <button type="submit"
                class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Rebuild Profile
        </button>
    </form>
</div>

@if (session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-5 text-sm font-medium">
    {{ session('success') }}
</div>
@endif

@if (!$profile)
<div class="bg-white rounded-2xl shadow-card border border-amber-100 p-10 text-center mb-6">
    <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
        </svg>
    </div>
    <p class="text-base font-semibold text-gray-700">Profile Not Built Yet</p>
    <p class="text-sm text-gray-400 mt-1">Click "Rebuild Profile" to generate behavioral intelligence for this guest.</p>
</div>
@else

{{-- Score Cards --}}
<div class="grid grid-cols-3 gap-5 mb-5">
    @php
        $loyaltyColor = $profile->loyalty_score >= 70 ? 'emerald' : ($profile->loyalty_score >= 40 ? 'amber' : 'gray');
        $upsellColor  = $profile->upsell_score  >= 70 ? 'violet'  : ($profile->upsell_score  >= 40 ? 'blue'  : 'gray');
        $churnColor   = $profile->churn_risk_score >= 70 ? 'red'   : ($profile->churn_risk_score >= 40 ? 'orange' : 'emerald');
        $churnTextColor = $profile->churn_risk_score >= 70 ? 'text-red-600' : ($profile->churn_risk_score >= 40 ? 'text-orange-500' : 'text-emerald-600');
    @endphp
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 text-center">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Loyalty Score</div>
        <div class="text-5xl font-bold text-{{ $loyaltyColor }}-600 leading-none">{{ $profile->loyalty_score }}</div>
        <div class="text-xs text-gray-400 mt-2">/100</div>
        <div class="mt-3 w-full bg-gray-100 rounded-full h-1.5">
            <div class="bg-{{ $loyaltyColor }}-500 h-1.5 rounded-full" style="width:{{ $profile->loyalty_score }}%"></div>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 text-center">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Upsell Score</div>
        <div class="text-5xl font-bold text-{{ $upsellColor }}-600 leading-none">{{ $profile->upsell_score }}</div>
        <div class="text-xs text-gray-400 mt-2">/100 · <span class="capitalize">{{ $profile->upsellTier() }}</span></div>
        <div class="mt-3 w-full bg-gray-100 rounded-full h-1.5">
            <div class="bg-{{ $upsellColor }}-500 h-1.5 rounded-full" style="width:{{ $profile->upsell_score }}%"></div>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 text-center">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Churn Risk</div>
        <div class="text-5xl font-bold {{ $churnTextColor }} leading-none">{{ $profile->churn_risk_score }}</div>
        <div class="text-xs text-gray-400 mt-2">
            /100 ·
            @if ($profile->isAtRisk())
            <span class="text-red-600 font-semibold">AT RISK</span>
            @else
            <span class="text-emerald-600 font-semibold">OK</span>
            @endif
        </div>
        <div class="mt-3 w-full bg-gray-100 rounded-full h-1.5">
            <div class="bg-{{ $churnColor }}-500 h-1.5 rounded-full" style="width:{{ $profile->churn_risk_score }}%"></div>
        </div>
    </div>
</div>

{{-- Revenue Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-5">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Total LTV</div>
        <div class="text-lg font-bold text-emerald-700">Rp {{ number_format($profile->total_lifetime_value, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Stays</div>
        <div class="text-lg font-bold text-gray-900">{{ $profile->total_stays }}</div>
        <div class="text-xs text-gray-400 mt-0.5">{{ $profile->total_nights }} total nights</div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Avg Daily Rate</div>
        <div class="text-lg font-bold text-gray-900">Rp {{ number_format($profile->avg_daily_rate, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Avg Review</div>
        <div class="text-lg font-bold text-gray-900">{{ $profile->avg_review_score ?? '—' }}<span class="text-sm font-normal text-gray-400"> / 5</span></div>
        <div class="text-xs text-gray-400 mt-0.5 capitalize">{{ $profile->sentiment ?? 'n/a' }}</div>
    </div>
</div>

{{-- Behavioral Profile + Spend Patterns --}}
<div class="grid grid-cols-2 gap-5 mb-5">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Preferences & Behavior</h2>
        </div>
        <dl class="divide-y divide-gray-50 text-sm">
            <div class="flex justify-between items-center px-5 py-3">
                <dt class="text-gray-500">Visit Frequency</dt>
                <dd class="font-medium text-gray-800 capitalize">{{ $profile->visit_frequency ?? '—' }}</dd>
            </div>
            <div class="flex justify-between items-center px-5 py-3">
                <dt class="text-gray-500">Preferred Room Type</dt>
                <dd class="font-medium text-gray-800">{{ $profile->preferredRoomType?->name ?? '—' }}</dd>
            </div>
            <div class="flex justify-between items-center px-5 py-3">
                <dt class="text-gray-500">Preferred Check-in Day</dt>
                <dd class="font-medium text-gray-800 capitalize">{{ $profile->preferred_check_in_day ?? '—' }}</dd>
            </div>
            <div class="flex justify-between items-center px-5 py-3">
                <dt class="text-gray-500">Avg Party Size</dt>
                <dd class="font-medium text-gray-800">{{ $profile->avg_party_size ?? '—' }}</dd>
            </div>
            <div class="flex justify-between items-center px-5 py-3">
                <dt class="text-gray-500">Avg Lead Days</dt>
                <dd class="font-medium text-gray-800">{{ $profile->avg_lead_days ?? '—' }} days</dd>
            </div>
            <div class="flex justify-between items-center px-5 py-3">
                <dt class="text-gray-500">Avg Stay Length</dt>
                <dd class="font-medium text-gray-800">{{ $profile->avg_stay_length ?? '—' }} nights</dd>
            </div>
            <div class="flex justify-between items-center px-5 py-3">
                <dt class="text-gray-500">Primary Booking Source</dt>
                <dd class="font-medium text-gray-800 capitalize">{{ $profile->primary_booking_source ?? '—' }}</dd>
            </div>
        </dl>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Spend Patterns</h2>
        </div>
        <dl class="divide-y divide-gray-50 text-sm">
            <div class="flex justify-between items-center px-5 py-3">
                <dt class="text-gray-500">Avg F&B / Stay</dt>
                <dd class="font-medium text-gray-800">Rp {{ number_format($profile->avg_fnb_spend_per_stay, 0, ',', '.') }}</dd>
            </div>
            <div class="flex justify-between items-center px-5 py-3">
                <dt class="text-gray-500">Avg Spa / Stay</dt>
                <dd class="font-medium text-gray-800">Rp {{ number_format($profile->avg_spa_spend_per_stay, 0, ',', '.') }}</dd>
            </div>
            <div class="flex justify-between items-center px-5 py-3">
                <dt class="text-gray-500">Typically Books Breakfast</dt>
                <dd>
                    @if ($profile->typically_books_breakfast)
                    <span class="text-xs font-medium bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">Yes</span>
                    @else
                    <span class="text-gray-400">—</span>
                    @endif
                </dd>
            </div>
            <div class="flex justify-between items-center px-5 py-3">
                <dt class="text-gray-500">Typically Uses Spa</dt>
                <dd>
                    @if ($profile->typically_uses_spa)
                    <span class="text-xs font-medium bg-rose-50 text-rose-700 px-2 py-0.5 rounded-full">Yes</span>
                    @else
                    <span class="text-gray-400">—</span>
                    @endif
                </dd>
            </div>
            <div class="flex justify-between items-center px-5 py-3">
                <dt class="text-gray-500">Typically Uses F&B</dt>
                <dd>
                    @if ($profile->typically_uses_fnb)
                    <span class="text-xs font-medium bg-orange-50 text-orange-700 px-2 py-0.5 rounded-full">Yes</span>
                    @else
                    <span class="text-gray-400">—</span>
                    @endif
                </dd>
            </div>
            <div class="flex justify-between items-center px-5 py-3">
                <dt class="text-gray-500">High Value Guest</dt>
                <dd>
                    @if ($profile->isHighValue())
                    <span class="text-xs font-semibold bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full">⭐ Yes</span>
                    @else
                    <span class="text-gray-400 text-xs">No</span>
                    @endif
                </dd>
            </div>
            <div class="flex justify-between items-center px-5 py-3">
                <dt class="text-gray-500">Profile Last Built</dt>
                <dd class="text-xs text-gray-400">{{ $profile->last_built_at?->diffForHumans() ?? '—' }}</dd>
            </div>
        </dl>
    </div>
</div>

@endif

{{-- Recent Stays --}}
@if ($guest->reservations->count())
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Recent Stays</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Ref</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Check-in</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Nights</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($guest->reservations as $res)
                @php $sc = match($res->status) { 'confirmed' => 'emerald', 'checked_in' => 'blue', 'checked_out' => 'gray', 'cancelled' => 'red', default => 'gray' }; @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <a href="{{ route('panel.fo.reservations.show', $res->id) }}"
                           class="font-mono text-sm font-semibold text-primary-600 hover:text-primary-800 transition-colors">
                            {{ $res->ref }}
                        </a>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-700">{{ $res->check_in->format('d M Y') }}</td>
                    <td class="px-4 py-3.5 text-center text-sm text-gray-700">{{ $res->nights }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-0.5 rounded-full capitalize">{{ $res->status }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm font-semibold text-gray-800">Rp {{ number_format($res->grand_total, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
