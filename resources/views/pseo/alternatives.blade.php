@extends('pseo.layout')

@section('pseo_body')
    @if ($alternatives->isNotEmpty())
        <h2 class="font-display text-2xl font-bold text-slate-900 mb-5">{{ $alternatives->count() }} Alternatif Direkomendasikan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($alternatives as $alt)
                @php
                    $photos = is_array($alt->photos) ? $alt->photos : (is_string($alt->photos) ? json_decode($alt->photos, true) : []);
                    $cover = $photos[0] ?? null;
                @endphp
                <a href="/rooms/{{ $alt->slug ?? $alt->id }}" class="group bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition-all">
                    <div class="aspect-[4/3] bg-gradient-to-br from-indigo-100 to-violet-100 relative">
                        @if ($cover)
                            <img src="{{ $cover }}" alt="{{ $alt->name }}" loading="lazy" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @endif
                    </div>
                    <div class="p-5">
                        <h3 class="font-semibold text-slate-900">{{ $alt->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $alt->description ?? '' }}</p>
                        <p class="font-display text-lg font-bold text-indigo-600 mt-3 tabular-nums">Rp {{ number_format($alt->base_rate, 0, ',', '.') }}<span class="text-xs font-normal text-slate-500"> / malam</span></p>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <p class="text-slate-500">Belum ada alternatif yang tersedia saat ini.</p>
    @endif
@endsection
