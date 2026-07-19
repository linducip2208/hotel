@extends('pseo.layout')

@section('pseo_body')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5">
            <p class="text-xs font-bold text-emerald-700 uppercase tracking-[0.2em] mb-2">Peak Season</p>
            <h3 class="font-display text-xl font-bold text-emerald-900">Mei – September</h3>
            <p class="text-sm text-emerald-800 mt-2 leading-relaxed">Cuaca cerah, ideal untuk aktivitas outdoor. Tarif hotel naik 20–35%, pesan minimal 4–6 minggu sebelumnya untuk mendapatkan pilihan terbaik.</p>
        </div>
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <p class="text-xs font-bold text-amber-700 uppercase tracking-[0.2em] mb-2">Off Season</p>
            <h3 class="font-display text-xl font-bold text-amber-900">November – Maret</h3>
            <p class="text-sm text-amber-800 mt-2 leading-relaxed">Musim hujan dengan diskon hotel besar (sering 25–40%). Beberapa atraksi outdoor terbatas, namun cocok untuk staycation dan eksplorasi indoor.</p>
        </div>
    </div>

    <h2 class="font-display text-2xl font-bold text-slate-900 mb-4">Bulan Terbaik Berkunjung</h2>
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700">Bulan</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700">Cuaca</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700">Tarif Hotel</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr><td class="px-4 py-3 font-medium">Januari</td><td class="px-4 py-3">Hujan sedang</td><td class="px-4 py-3"><span class="bg-emerald-100 text-emerald-700 text-xs font-semibold px-2 py-0.5 rounded">Murah</span></td><td class="px-4 py-3 text-slate-500">Awal tahun, hotel banyak promo.</td></tr>
                <tr><td class="px-4 py-3 font-medium">April</td><td class="px-4 py-3">Mulai kemarau</td><td class="px-4 py-3"><span class="bg-amber-100 text-amber-700 text-xs font-semibold px-2 py-0.5 rounded">Sedang</span></td><td class="px-4 py-3 text-slate-500">Transisi musim, banyak promo Lebaran.</td></tr>
                <tr><td class="px-4 py-3 font-medium">Juli</td><td class="px-4 py-3">Cerah</td><td class="px-4 py-3"><span class="bg-rose-100 text-rose-700 text-xs font-semibold px-2 py-0.5 rounded">Mahal</span></td><td class="px-4 py-3 text-slate-500">Liburan sekolah, peak season.</td></tr>
                <tr><td class="px-4 py-3 font-medium">September</td><td class="px-4 py-3">Cerah</td><td class="px-4 py-3"><span class="bg-amber-100 text-amber-700 text-xs font-semibold px-2 py-0.5 rounded">Sedang</span></td><td class="px-4 py-3 text-slate-500">Akhir kemarau, masih ramai weekend.</td></tr>
                <tr><td class="px-4 py-3 font-medium">Desember</td><td class="px-4 py-3">Hujan lebat</td><td class="px-4 py-3"><span class="bg-rose-100 text-rose-700 text-xs font-semibold px-2 py-0.5 rounded">Mahal</span></td><td class="px-4 py-3 text-slate-500">Holiday end-of-year, perlu booking awal.</td></tr>
            </tbody>
        </table>
    </div>

    <section class="mt-8">
        <h3 class="font-display text-xl font-bold text-slate-900 mb-3">Eksplorasi Lain di {{ $city_name }}</h3>
        <div class="flex flex-wrap gap-2">
            <a href="/hotels-in-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-1.5 rounded-lg text-sm text-slate-700 transition-colors">Hotel di {{ $city_name }}</a>
            <a href="/family-stay-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-1.5 rounded-lg text-sm text-slate-700 transition-colors">Family stay</a>
            <a href="/honeymoon-stay-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-1.5 rounded-lg text-sm text-slate-700 transition-colors">Honeymoon</a>
            <a href="/business-stay-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-1.5 rounded-lg text-sm text-slate-700 transition-colors">Business trip</a>
        </div>
    </section>
@endsection
