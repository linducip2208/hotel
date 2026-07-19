@extends('pseo.layout')

@section('pseo_body')
    @php
        $perks = match ($occasion) {
            'honeymoon' => ['💐 Welcome flowers', '🍷 Champagne package', '🛁 Couple spa', '🌅 Sunset dinner'],
            'family'    => ['🛏 Extra bed', '🧒 Kids meal', '🎮 Play area', '🚌 Family shuttle'],
            'business'  => ['💼 Business center', '⚡ High-speed Wi-Fi', '🖨 Printing service', '☕ Express breakfast'],
            'romantic'  => ['🌹 Rose petals', '🎶 Romantic playlist', '🍫 Couple amenities', '🛀 Bath ritual'],
            'wedding'   => ['💒 Venue setup', '👰 Bridal suite', '🍽 Catering package', '📸 Photographer'],
            default     => ['✨ Welcome amenity', '🛎 Concierge', '🍳 Breakfast', '🎁 Free upgrade'],
        };
    @endphp

    <h2 class="font-display text-2xl font-bold text-slate-900 mb-4">Apa yang Termasuk dalam Paket?</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
        @foreach ($perks as $p)
            <div class="bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700">{{ $p }}</div>
        @endforeach
    </div>

    @if ($property)
        @php
            $rooms = \App\Models\RoomType::where('property_id', $property->id)
                ->where('is_active', true)
                ->orderBy('base_rate', 'desc')
                ->take(6)
                ->get();
        @endphp
        @if ($rooms->isNotEmpty())
            <h2 class="font-display text-2xl font-bold text-slate-900 mb-5">Kamar Direkomendasikan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @foreach ($rooms->take(4) as $rt)
                    @php
                        $photos = is_array($rt->photos) ? $rt->photos : (is_string($rt->photos) ? json_decode($rt->photos, true) : []);
                        $cover = $photos[0] ?? null;
                    @endphp
                    <a href="/rooms/{{ $rt->slug ?? $rt->id }}" class="bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-lg transition-shadow flex">
                        <div class="w-32 sm:w-40 shrink-0 bg-gradient-to-br from-indigo-100 to-violet-100 relative">
                            @if ($cover)
                                <img src="{{ $cover }}" alt="{{ $rt->name }}" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="p-4 flex-1">
                            <h3 class="font-semibold text-slate-900">{{ $rt->name }}</h3>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $rt->description ?? '' }}</p>
                            <p class="font-display text-base font-bold text-indigo-600 mt-2 tabular-nums">Rp {{ number_format($rt->base_rate, 0, ',', '.') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    @endif
@endsection
