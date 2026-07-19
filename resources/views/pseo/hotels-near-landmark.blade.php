@extends('pseo.layout')

@section('pseo_body')
    @if ($landmark)
        <div class="bg-white border border-slate-200 rounded-2xl p-5 mb-8">
            <h3 class="font-display text-xl font-bold text-slate-900">📍 {{ $landmark->name }}</h3>
            <p class="text-sm text-slate-500 mt-1">{{ $landmark->city }}{{ $landmark->province ? ', '.$landmark->province : '' }}</p>
            @if ($landmark->lat && $landmark->lng)
                <p class="text-xs text-slate-400 mt-1 font-mono">{{ $landmark->lat }}, {{ $landmark->lng }}</p>
            @endif
        </div>
    @endif

    @if ($property)
        @php
            $rooms = \App\Models\RoomType::where('property_id', $property->id)
                ->where('is_active', true)
                ->orderBy('base_rate')
                ->take(6)
                ->get();
        @endphp
        @if ($rooms->isNotEmpty())
            <h2 class="font-display text-2xl font-bold text-slate-900 mb-5">Pilihan Akomodasi Terdekat</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($rooms as $rt)
                    @php
                        $photos = is_array($rt->photos) ? $rt->photos : (is_string($rt->photos) ? json_decode($rt->photos, true) : []);
                        $cover = $photos[0] ?? null;
                    @endphp
                    <a href="/rooms/{{ $rt->slug ?? $rt->id }}" class="group bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition-all">
                        <div class="aspect-[4/3] bg-gradient-to-br from-indigo-100 to-violet-100 relative">
                            @if ($cover)
                                <img src="{{ $cover }}" alt="{{ $rt->name }}" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="p-5">
                            <h3 class="font-semibold text-slate-900">{{ $rt->name }}</h3>
                            <p class="font-display text-lg font-bold text-indigo-600 mt-2 tabular-nums">Rp {{ number_format($rt->base_rate, 0, ',', '.') }}<span class="text-xs font-normal text-slate-500"> / malam</span></p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    @endif
@endsection
