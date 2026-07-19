@extends('pseo.layout')

@section('pseo_body')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        @foreach ([$a, $b] as $rt)
            @php
                $photos = is_array($rt->photos) ? $rt->photos : (is_string($rt->photos) ? json_decode($rt->photos, true) : []);
                $cover = $photos[0] ?? null;
            @endphp
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="aspect-[16/9] bg-gradient-to-br from-indigo-100 to-violet-100 relative">
                    @if ($cover)
                        <img src="{{ $cover }}" alt="{{ $rt->name }}" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
                    @endif
                </div>
                <div class="p-5">
                    <h3 class="font-display text-xl font-bold text-slate-900">{{ $rt->name }}</h3>
                    <p class="text-sm text-slate-600 mt-1 line-clamp-2">{{ $rt->description ?? '' }}</p>
                    <p class="font-display text-2xl font-bold text-indigo-600 mt-3 tabular-nums">Rp {{ number_format($rt->base_rate, 0, ',', '.') }}<span class="text-xs font-normal text-slate-500"> / malam</span></p>
                </div>
            </div>
        @endforeach
    </div>

    <h2 class="font-display text-2xl font-bold text-slate-900 mb-4">Tabel Perbandingan</h2>
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700">Fitur</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-700">{{ $a->name }}</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-700">{{ $b->name }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr><td class="px-4 py-3 text-slate-600">Max Occupancy</td><td class="px-4 py-3 text-center font-medium">{{ $a->max_occupancy }}</td><td class="px-4 py-3 text-center font-medium">{{ $b->max_occupancy }}</td></tr>
                <tr><td class="px-4 py-3 text-slate-600">Bed Config</td><td class="px-4 py-3 text-center font-medium">{{ $a->bed_config ?? '-' }}</td><td class="px-4 py-3 text-center font-medium">{{ $b->bed_config ?? '-' }}</td></tr>
                <tr><td class="px-4 py-3 text-slate-600">Ukuran</td><td class="px-4 py-3 text-center font-medium">{{ $a->size_sqm ?? '-' }} m²</td><td class="px-4 py-3 text-center font-medium">{{ $b->size_sqm ?? '-' }} m²</td></tr>
                <tr><td class="px-4 py-3 text-slate-600">Tarif</td><td class="px-4 py-3 text-center font-medium tabular-nums">Rp {{ number_format($a->base_rate, 0, ',', '.') }}</td><td class="px-4 py-3 text-center font-medium tabular-nums">Rp {{ number_format($b->base_rate, 0, ',', '.') }}</td></tr>
            </tbody>
        </table>
    </div>
@endsection
