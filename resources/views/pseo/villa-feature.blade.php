@extends('pseo.layout')

@section('pseo_body')
    <div class="bg-gradient-to-br from-indigo-50 to-violet-50 border border-indigo-200 rounded-2xl p-6 mb-8">
        <h2 class="font-display text-xl font-bold text-slate-900 mb-2">Mengapa Memilih Villa dengan {{ $feature_name }}?</h2>
        <p class="text-slate-700 text-sm leading-relaxed">
            Fitur {{ $feature_name }} memberikan pengalaman menginap yang lebih privat dan eksklusif dibandingkan kamar hotel standar. Cocok untuk keluarga besar, grup teman, atau pasangan yang mengutamakan kenyamanan dan privasi.
        </p>
    </div>

    @if ($property)
        @php
            $rooms = \App\Models\RoomType::where('property_id', $property->id)
                ->where('is_active', true)
                ->orderBy('base_rate', 'desc')
                ->get();
        @endphp
        @if ($rooms->isNotEmpty())
            <h2 class="font-display text-2xl font-bold text-slate-900 mb-5">Pilihan Villa di {{ $city_name }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @foreach ($rooms->take(6) as $rt)
                    @php
                        $photos = is_array($rt->photos) ? $rt->photos : (is_string($rt->photos) ? json_decode($rt->photos, true) : []);
                        $cover = $photos[0] ?? null;
                    @endphp
                    <a href="/rooms/{{ $rt->slug ?? $rt->id }}" class="group bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition-all">
                        <div class="aspect-[16/10] bg-gradient-to-br from-indigo-100 to-violet-100 relative">
                            @if ($cover)
                                <img src="{{ $cover }}" alt="{{ $rt->name }}" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
                            @endif
                            <span class="absolute top-3 left-3 inline-flex items-center bg-white/90 backdrop-blur text-indigo-700 text-[10px] font-bold uppercase tracking-wide rounded-full px-2.5 py-1 shadow">
                                {{ $feature_name }}
                            </span>
                        </div>
                        <div class="p-5">
                            <h3 class="font-semibold text-slate-900">{{ $rt->name }}</h3>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $rt->description ?? '' }}</p>
                            <p class="font-display text-lg font-bold text-indigo-600 mt-3 tabular-nums">Rp {{ number_format($rt->base_rate, 0, ',', '.') }}<span class="text-xs font-normal text-slate-500"> / malam</span></p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    @endif
@endsection
