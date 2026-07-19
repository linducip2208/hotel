@extends('pseo.layout')

@section('pseo_body')
    @if ($items->isNotEmpty())
        <h2 class="font-display text-2xl md:text-3xl font-bold text-slate-900 mb-5">Top {{ $items->count() }} {{ $category_name }}</h2>
        <ol class="space-y-4">
            @foreach ($items as $i => $rt)
                @php
                    $photos = is_array($rt->photos) ? $rt->photos : (is_string($rt->photos) ? json_decode($rt->photos, true) : []);
                    $cover = $photos[0] ?? null;
                @endphp
                <li class="bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-md transition-shadow">
                    <div class="grid grid-cols-1 sm:grid-cols-[200px_1fr] gap-0">
                        <div class="aspect-[4/3] sm:aspect-auto sm:h-full min-h-[160px] bg-gradient-to-br from-indigo-100 to-violet-100 relative">
                            @if ($cover)
                                <img src="{{ $cover }}" alt="{{ $rt->name }}" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
                            @endif
                            <span class="absolute top-3 left-3 inline-flex items-center justify-center w-8 h-8 bg-amber-500 text-white text-sm font-bold rounded-full shadow">
                                {{ $i + 1 }}
                            </span>
                        </div>
                        <div class="p-5 flex flex-col gap-2">
                            <h3 class="font-display text-lg font-bold text-slate-900">{{ $rt->name }}</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">{{ $rt->description ?? 'Kamar nyaman dengan fasilitas modern dan layanan profesional.' }}</p>
                            <div class="flex items-center gap-4 text-xs text-slate-500 mt-1">
                                <span>👥 {{ $rt->max_occupancy }} tamu</span>
                                @if ($rt->bed_config)<span>🛏 {{ $rt->bed_config }}</span>@endif
                                @if ($rt->size_sqm)<span>📐 {{ $rt->size_sqm }}m²</span>@endif
                            </div>
                            <div class="flex items-end justify-between mt-2 pt-3 border-t border-slate-100">
                                <p class="font-display text-xl font-bold text-slate-900 tabular-nums">
                                    Rp {{ number_format($rt->base_rate, 0, ',', '.') }}
                                    <span class="text-xs font-normal text-slate-500">/ malam</span>
                                </p>
                                <a href="/rooms/{{ $rt->slug ?? $rt->id }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">Detail</a>
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ol>
    @else
        <p class="text-slate-500">Belum ada akomodasi yang dipublikasikan untuk kategori ini.</p>
    @endif
@endsection
