@extends('pseo.layout')

@section('pseo_body')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white border border-slate-200 rounded-2xl p-5">
            <div class="text-2xl mb-2">🍴</div>
            <h3 class="font-semibold text-slate-900">Kuliner</h3>
            <p class="text-sm text-slate-600 mt-1">Cicipi makanan khas lokal di sekitar lokasi — banyak warung otentik dengan harga ramah kantong.</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-5">
            <div class="text-2xl mb-2">🚗</div>
            <h3 class="font-semibold text-slate-900">Transportasi</h3>
            <p class="text-sm text-slate-600 mt-1">Akses mudah dengan ride-hailing, taksi, atau kendaraan sewa. Beberapa hotel menyediakan shuttle gratis.</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-5">
            <div class="text-2xl mb-2">📷</div>
            <h3 class="font-semibold text-slate-900">Photo Spot</h3>
            <p class="text-sm text-slate-600 mt-1">Lokasi populer dengan banyak spot foto Instagrammable, terutama saat golden hour pagi atau sore.</p>
        </div>
    </div>

    @if ($property)
        <section class="bg-slate-50 border border-slate-200 rounded-2xl p-6">
            <h3 class="font-display text-xl font-bold text-slate-900 mb-2">Akomodasi Direkomendasikan</h3>
            <p class="text-sm text-slate-600 mb-3">Untuk akses tercepat ke {{ $landmark?->name ?? $title }}, pilih properti dengan jarak ≤2 km.</p>
            <a href="/rooms" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
                Lihat kamar tersedia
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </section>
    @endif
@endsection
