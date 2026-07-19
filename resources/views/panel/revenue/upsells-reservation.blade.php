@extends('panel.layout')
@section('title', 'Upsell — Reservasi ' . $reservation->ref)
@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <a href="{{ route('panel.fo.reservations.show', $reservation->id) }}" class="text-sm text-indigo-600 hover:underline mb-1 inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke reservasi
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Upsell untuk {{ $reservation->ref }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $reservation->primaryGuest?->full_name }} &middot; {{ $reservation->check_in?->translatedFormat('d M Y') }} &rarr; {{ $reservation->check_out?->translatedFormat('d M Y') }}</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">

    {{-- Available offers --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-6">
            <div class="p-5 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">Penawaran Tersedia</h2>
                <p class="text-xs text-gray-500 mt-0.5">Pilih penawaran untuk disajikan ke tamu</p>
            </div>
            @if(count($allOffers) > 0)
            <form method="POST" action="{{ route('panel.revenue.upsells.present', $reservation->id) }}" class="p-5">
                @csrf
                <div class="space-y-3 mb-4">
                    @foreach($allOffers as $offer)
                    @php
                        $alreadyDone = $presentations->firstWhere('upsell_offer_id', $offer->id);
                    @endphp
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer {{ $alreadyDone ? 'opacity-50' : '' }}">
                        <input type="checkbox" name="upsell_offer_ids[]" value="{{ $offer->id }}" class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ $alreadyDone ? 'disabled' : '' }}>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-900">{{ $offer->name }}</span>
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                    @switch($offer->type)
                                        @case('room_upgrade') bg-violet-100 text-violet-700 @break
                                        @case('late_checkout') bg-sky-100 text-sky-700 @break
                                        @case('spa') bg-pink-100 text-pink-700 @break
                                        @case('dinner') bg-amber-100 text-amber-700 @break
                                        @case('airport_transfer') bg-emerald-100 text-emerald-700 @break
                                        @case('package') bg-indigo-100 text-indigo-700 @break
                                        @default bg-gray-100 text-gray-600
                                    @endswitch
                                ">{{ str_replace('_', ' ', ucfirst($offer->type)) }}</span>
                                @if($alreadyDone)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                    @switch($alreadyDone->status)
                                        @case('offered') bg-blue-100 text-blue-700 @break
                                        @case('accepted') bg-emerald-100 text-emerald-700 @break
                                        @case('declined') bg-rose-100 text-rose-700 @break
                                        @default bg-gray-100 text-gray-500
                                    @endswitch
                                ">{{ ucfirst($alreadyDone->status) }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $offer->description }}</p>
                        </div>
                        <div class="text-sm font-bold text-gray-900 font-mono shrink-0">Rp {{ number_format($offer->price, 0, ',', '.') }}</div>
                    </label>
                    @endforeach
                </div>
                <button type="submit" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm shadow-indigo-500/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    Sajikan Penawaran
                </button>
            </form>
            @else
            <div class="p-12 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Belum ada penawaran upsell tersedia. <a href="{{ route('panel.revenue.upsells.index') }}" class="text-indigo-600 hover:underline">Buat penawaran baru</a>.
            </div>
            @endif
        </div>

        {{-- Presentation history --}}
        @if($presentations->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">Riwayat Penawaran</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-5 py-3">Penawaran</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Harga</th>
                            <th class="px-5 py-3">Waktu</th>
                            <th class="px-5 py-3 w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($presentations as $pres)
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-5 py-3.5 font-medium text-gray-900 text-xs">{{ $pres->offer?->name }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                    @switch($pres->status)
                                        @case('offered') bg-blue-100 text-blue-700 @break
                                        @case('accepted') bg-emerald-100 text-emerald-700 @break
                                        @case('declined') bg-rose-100 text-rose-700 @break
                                        @case('expired') bg-gray-100 text-gray-500 @break
                                    @endswitch
                                ">{{ ucfirst($pres->status) }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-gray-600">Rp {{ number_format($pres->price_accepted ?? $pres->price_offered, 0, ',', '.') }}</td>
                            <td class="px-5 py-3.5 text-xs text-gray-500">{{ $pres->responded_at ? $pres->responded_at->diffForHumans() : $pres->offered_at->diffForHumans() }}</td>
                            <td class="px-5 py-3.5">
                                @if($pres->status === 'offered')
                                <div class="flex items-center gap-1">
                                    <form method="POST" action="{{ route('panel.revenue.upsells.accept', $pres->id) }}" class="inline-flex items-center gap-1">
                                        @csrf
                                        <input type="number" name="negotiated_price" step="1000" min="0"
                                            class="w-20 border border-gray-200 rounded-lg px-2 py-1 text-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                            placeholder="Harga" value="{{ $pres->price_offered }}">
                                        <button class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors" title="Terima">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('panel.revenue.upsells.decline', $pres->id) }}">
                                        @csrf
                                        <button class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition-colors" title="Tolak">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- Right sidebar: guest profile --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 text-sm mb-3">Profil Tamu</h3>
            @php $profile = $reservation->primaryGuest?->profile; @endphp
            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-gray-500">Tier Upsell</span>
                    <span class="font-bold uppercase @switch($profile?->upsellTier())
                        @case('hot') text-rose-600 @break
                        @case('warm') text-amber-600 @break
                        @default text-sky-600
                    @endswitch">{{ $profile?->upsellTier() ?? 'cold' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Lifetime Value</span>
                    <span class="font-mono text-gray-900">Rp {{ number_format($profile?->total_lifetime_value ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Avg Daily Rate</span>
                    <span class="font-mono text-gray-900">Rp {{ number_format($profile?->avg_daily_rate ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Avg F&B Spend</span>
                    <span class="font-mono text-gray-900">Rp {{ number_format($profile?->avg_fnb_spend_per_stay ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Avg Spa Spend</span>
                    <span class="font-mono text-gray-900">Rp {{ number_format($profile?->avg_spa_spend_per_stay ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
