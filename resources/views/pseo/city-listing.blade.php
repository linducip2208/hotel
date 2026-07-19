@extends('pseo.layout')

@section('pseo_body')
    @if ($property)
        @php
            $rooms = \App\Models\RoomType::where('property_id', $property->id)
                ->where('is_active', true)
                ->orderBy('base_rate')
                ->get();
        @endphp

        @if ($rooms->isNotEmpty())
            <h2 class="font-display text-2xl md:text-3xl font-bold text-slate-900 mb-5">Pilihan Kamar di {{ $city_name }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($rooms as $i => $rt)
                    <article class="bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition-all">
                        <div class="aspect-[4/3] bg-gradient-to-br from-indigo-100 to-violet-100 relative">
                            @php
                                $photos = is_array($rt->photos) ? $rt->photos : (is_string($rt->photos) ? json_decode($rt->photos, true) : []);
                                $cover = $photos[0] ?? null;
                            @endphp
                            @if ($cover)
                                <img src="{{ $cover }}" alt="{{ $rt->name }}" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
                            @endif
                            <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 bg-emerald-500 text-white text-[11px] font-semibold rounded-full px-2.5 py-1 shadow">
                                #{{ $i + 1 }} Pilihan
                            </span>
                        </div>
                        <div class="p-5">
                            <h3 class="font-semibold text-slate-900 mb-1">{{ $rt->name }}</h3>
                            <p class="text-xs text-slate-500 mb-3 line-clamp-2">{{ $rt->description ?? 'Kamar nyaman dengan fasilitas modern.' }}</p>
                            <div class="flex items-end justify-between">
                                <div>
                                    <p class="text-[10px] text-slate-400 uppercase tracking-wide">Mulai dari</p>
                                    <p class="font-display text-xl font-bold text-slate-900 tabular-nums">Rp {{ number_format($rt->base_rate, 0, ',', '.') }}</p>
                                </div>
                                <a href="/rooms/{{ $rt->slug ?? $rt->id }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-semibold">Detail →</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Related links untuk internal linking --}}
    <section class="mt-12">
        <h2 class="font-display text-xl font-bold text-slate-900 mb-4">Eksplorasi Lain di {{ $city_name }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
            <a href="/best-time-to-visit-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Waktu terbaik berkunjung</a>
            <a href="/honeymoon-stay-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Honeymoon stay</a>
            <a href="/family-stay-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Family stay</a>
            <a href="/business-stay-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Business stay</a>
            <a href="/pet-friendly-hotels-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Pet-friendly hotels</a>
            <a href="/hotels-under-500k-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Hotel di bawah Rp 500K</a>
        </div>
    </section>
@endsection
