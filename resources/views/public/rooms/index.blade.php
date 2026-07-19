@extends('public.layout')
@section('title', $rooms->count().' Kamar — '.($property?->name ?? config('app.name')))
@section('description', 'Lihat '.$rooms->count().' kamar di '.($property?->name ?? '').' — semua kamar dengan foto, harga, dan ketersediaan terkini.')

@section('content')

{{-- ════════════════════════ HERO ════════════════════════ --}}
<section class="pt-32 pb-12 lg:pt-40 lg:pb-16 bg-gradient-to-b from-stone-50 via-white to-stone-50">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <p class="text-xs font-bold text-indigo-600 uppercase tracking-[0.25em] mb-3">Akomodasi</p>
        <h1 class="font-display text-4xl lg:text-5xl font-bold text-slate-900 tracking-tight">
            <span class="text-indigo-600 tabular-nums">{{ $rooms->count() }}</span> Kamar &amp; {{ $roomTypes->count() }} Tipe
        </h1>
        <p class="text-slate-600 mt-3 max-w-2xl">Pilih kamar yang sesuai kebutuhan Anda. Semua tarif sudah termasuk pajak. Setiap kamar memiliki foto interior agar Anda bisa melihat detail sebelum booking.</p>

        {{-- Quick stats --}}
        <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-2xl">
            <div class="bg-white border border-slate-200 rounded-2xl p-4">
                <p class="font-display text-2xl font-bold text-indigo-600 tabular-nums">{{ $rooms->count() }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Total Kamar</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-4">
                <p class="font-display text-2xl font-bold text-violet-600 tabular-nums">{{ $roomTypes->count() }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Tipe Kamar</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-4">
                <p class="font-display text-2xl font-bold text-emerald-600 tabular-nums">{{ $rooms->groupBy('floor')->count() }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Lantai</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-4">
                <p class="font-display text-2xl font-bold text-amber-600">24/7</p>
                <p class="text-xs text-slate-500 mt-0.5">Layanan</p>
            </div>
        </div>
    </div>
</section>

{{-- ════════════════════════ TIPE KAMAR (3 cards) ════════════════════════ --}}
<section class="max-w-7xl mx-auto px-4 lg:px-8 pb-12">
    <h2 class="font-display text-2xl lg:text-3xl font-bold text-slate-900 mb-6">Tipe Kamar</h2>

    @if($roomTypes->isEmpty())
        <div class="text-center py-20 bg-white border border-slate-200 rounded-3xl">
            <p class="text-slate-500">Belum ada tipe kamar yang dipublikasikan.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            @foreach($roomTypes as $rt)
                @php
                    $photos = is_array($rt->photos) ? $rt->photos : (is_string($rt->photos) ? json_decode($rt->photos, true) : []);
                    $cover = $photos[0] ?? null;
                    $amenList = is_array($rt->amenities) ? $rt->amenities : (is_string($rt->amenities) ? json_decode($rt->amenities, true) : []);
                    $unitCount = $rooms->where('room_type_id', $rt->id)->count();
                @endphp
                <article class="group bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all overflow-hidden flex flex-col">
                    <a href="{{ route('rooms.show', $rt->slug) }}" class="relative block h-56 overflow-hidden bg-slate-200">
                        @if($cover)
                            <img src="{{ $cover }}" alt="{{ $rt->name }}" loading="lazy"
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/10 to-transparent"></div>
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-violet-600"></div>
                        @endif
                        <div class="absolute top-4 left-4 inline-flex items-center gap-1.5 bg-emerald-500 text-white text-xs font-semibold rounded-full px-2.5 py-1 shadow">
                            {{ $unitCount }} unit tersedia
                        </div>
                        @if($rt->size_sqm)
                            <div class="absolute bottom-4 left-4 text-white drop-shadow">
                                <p class="font-display text-3xl font-bold leading-none">{{ $rt->size_sqm }}<span class="text-base font-normal opacity-80">m²</span></p>
                            </div>
                        @endif
                    </a>

                    <div class="p-6 flex flex-col flex-1">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.18em] mb-1">{{ $rt->code }}</p>
                        <h3 class="font-display text-xl font-bold text-slate-900 mb-2">{{ $rt->name }}</h3>
                        <p class="text-sm text-slate-600 leading-relaxed mb-4 flex-1">{{ $rt->description ?? 'Kamar nyaman dengan fasilitas modern.' }}</p>

                        <div class="flex items-center gap-3 text-xs text-slate-500 mb-4 pb-4 border-b border-slate-100">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $rt->max_occupancy }} tamu
                            </span>
                            @if($rt->bed_config)
                                <span class="text-slate-300">·</span>
                                <span>{{ $rt->bed_config }}</span>
                            @endif
                        </div>

                        @if(!empty($amenList))
                            <div class="flex flex-wrap gap-1.5 mb-5">
                                @foreach(array_slice((array) $amenList, 0, 4) as $am)
                                    <span class="text-[10px] uppercase tracking-wider font-semibold bg-slate-100 text-slate-600 px-2 py-1 rounded">{{ $am }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-xs text-slate-400">Mulai dari</p>
                                <p class="font-display text-2xl font-bold text-slate-900 tabular-nums leading-none">Rp {{ number_format($rt->base_rate, 0, ',', '.') }}</p>
                                <p class="text-[10px] text-slate-400 mt-1">/ malam</p>
                            </div>
                            <a href="{{ route('rooms.show', $rt->slug) }}"
                               class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-700 px-3 py-2 rounded-lg hover:bg-indigo-50 transition-colors">
                                Detail
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>

{{-- ════════════════════════ ALL 100 ROOMS GRID ════════════════════════ --}}
<section x-data="{ filter: 'all' }" class="max-w-7xl mx-auto px-4 lg:px-8 pb-20">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
        <div>
            <h2 class="font-display text-2xl lg:text-3xl font-bold text-slate-900">Lihat Semua {{ $rooms->count() }} Kamar</h2>
            <p class="text-slate-600 mt-2 text-sm">Setiap kamar dipotret berdasarkan tipenya. Klik kartu untuk melihat detail tipe.</p>
        </div>

        {{-- Filter pills --}}
        <div class="flex flex-wrap gap-2">
            <button @click="filter='all'"
                    :class="filter==='all' ? 'bg-indigo-600 text-white shadow' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'"
                    class="text-xs font-semibold px-3 py-1.5 rounded-full transition-colors">
                Semua ({{ $rooms->count() }})
            </button>
            @foreach($roomTypes as $rt)
                @php $cnt = $rooms->where('room_type_id', $rt->id)->count(); @endphp
                <button @click="filter='{{ $rt->code }}'"
                        :class="filter==='{{ $rt->code }}' ? 'bg-indigo-600 text-white shadow' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'"
                        class="text-xs font-semibold px-3 py-1.5 rounded-full transition-colors">
                    {{ $rt->name }} ({{ $cnt }})
                </button>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 lg:gap-4">
        @foreach($rooms as $i => $room)
            @php
                $rt = $room->roomType;
                $rtCode = $rt?->code ?? 'XXX';
                $photo = \App\Support\RoomPhotos::forRoom($room);
                $statusColor = match($room->fo_status ?? 'vacant') {
                    'vacant'   => 'bg-emerald-500',
                    'occupied' => 'bg-rose-500',
                    'reserved' => 'bg-amber-500',
                    default    => 'bg-slate-400',
                };
                $statusLabel = match($room->fo_status ?? 'vacant') {
                    'vacant'   => 'Tersedia',
                    'occupied' => 'Terisi',
                    'reserved' => 'Dipesan',
                    default    => ucfirst($room->fo_status ?? 'unknown'),
                };
            @endphp
            <a href="{{ $rt ? route('rooms.show', $rt->slug) : '#' }}"
               x-show="filter==='all' || filter==='{{ $rtCode }}'"
               class="group block bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition-all">
                <div class="relative aspect-[4/3] bg-gradient-to-br from-indigo-100 to-violet-100">
                    <img src="{{ $photo }}" alt="Kamar {{ $room->number }} — {{ $rt?->name }}" loading="lazy"
                         onerror="this.onerror=null; this.src='https://picsum.photos/seed/room-{{ $room->id }}/800/600';"
                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">

                    <div class="absolute inset-x-0 top-0 p-2 flex items-start justify-between">
                        <span class="bg-black/60 backdrop-blur text-white text-[10px] font-bold tracking-wide px-2 py-1 rounded">
                            #{{ $room->number }}
                        </span>
                        <span class="inline-flex items-center gap-1 bg-white/90 backdrop-blur text-[10px] font-semibold px-1.5 py-0.5 rounded">
                            <span class="w-1.5 h-1.5 rounded-full {{ $statusColor }}"></span>
                            <span class="text-slate-700 hidden sm:inline">{{ $statusLabel }}</span>
                        </span>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-2.5">
                        <p class="text-white text-[11px] font-semibold leading-tight">{{ $rt?->name }}</p>
                        <p class="text-white/80 text-[10px]">Lt. {{ $room->floor }} · {{ $rt?->size_sqm ?? '–' }}m²</p>
                    </div>
                </div>
                <div class="p-2.5 flex items-center justify-between">
                    <p class="font-display text-sm font-bold text-slate-900 tabular-nums">
                        Rp {{ number_format($rt?->base_rate ?? 0, 0, ',', '.') }}
                    </p>
                    <svg class="w-4 h-4 text-indigo-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-10 text-center bg-gradient-to-br from-indigo-600 to-violet-600 rounded-3xl p-8 lg:p-10 text-white">
        <h3 class="font-display text-2xl lg:text-3xl font-bold">Siap memesan kamar Anda?</h3>
        <p class="text-indigo-100 mt-2">Ketersediaan real-time, free cancellation H-1.</p>
        <a href="/booking" class="inline-flex items-center gap-2 mt-5 bg-white text-indigo-700 hover:bg-indigo-50 px-6 py-3 rounded-xl font-semibold shadow-lg transition-colors">
            Cari Kamar Sekarang
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </a>
    </div>
</section>

@endsection
