@extends('pseo.layout')

@section('pseo_body')
    {{-- Content page: tips, guide, weather, events --}}

    @php
        $pageIcon = match ($page_type ?? '') {
            'tips' => '💡',
            'guide' => '🧭',
            'weather' => '🌤️',
            'events' => '🎉',
            default => '📄',
        };
    @endphp

    {{-- Key facts / quick info cards --}}
    @if ($page_type === 'weather' && isset($month))
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            @php
                $monthNum = (int) date('n', strtotime('1 ' . $month . ' 2024'));
                $isDry = $monthNum >= 5 && $monthNum <= 9;
                $isWet = $monthNum >= 11 || $monthNum <= 3;
            @endphp
            <div class="bg-white border border-slate-200 rounded-xl p-4 text-center">
                <div class="text-2xl mb-1">🌡️</div>
                <p class="text-xs text-slate-500 uppercase tracking-wide">Suhu Rata-rata</p>
                <p class="font-display text-lg font-bold text-slate-900">{{ $isDry ? '24–32°C' : ($isWet ? '23–30°C' : '24–33°C') }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 text-center">
                <div class="text-2xl mb-1">🌧️</div>
                <p class="text-xs text-slate-500 uppercase tracking-wide">Curah Hujan</p>
                <p class="font-display text-lg font-bold text-slate-900">{{ $isWet ? 'Tinggi' : ($isDry ? 'Rendah' : 'Sedang') }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 text-center">
                <div class="text-2xl mb-1">💧</div>
                <p class="text-xs text-slate-500 uppercase tracking-wide">Kelembaban</p>
                <p class="font-display text-lg font-bold text-slate-900">{{ $isWet ? '80–90%' : '70–80%' }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 text-center">
                <div class="text-2xl mb-1">{{ $isDry ? '☀️' : ($isWet ? '🌧️' : '⛅') }}</div>
                <p class="text-xs text-slate-500 uppercase tracking-wide">Kategori</p>
                <p class="font-display text-lg font-bold text-slate-900">{{ $isDry ? 'Kemarau' : ($isWet ? 'Hujan' : 'Pancaroba') }}</p>
            </div>
        </div>
    @endif

    @if ($page_type === 'events' && isset($year))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-5">
                <p class="text-xs font-bold text-indigo-700 uppercase tracking-[0.2em] mb-2">🎭 Budaya</p>
                <p class="text-sm text-indigo-900 leading-relaxed">Festival budaya, upacara adat, dan pertunjukan tradisional yang berlangsung secara tahunan di {{ $city_name }}.</p>
            </div>
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5">
                <p class="text-xs font-bold text-emerald-700 uppercase tracking-[0.2em] mb-2">🎵 Musik & Seni</p>
                <p class="text-sm text-emerald-900 leading-relaxed">Konser, pameran seni, dan pertunjukan kontemporer di {{ $city_name }} tahun {{ $year }}.</p>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                <p class="text-xs font-bold text-amber-700 uppercase tracking-[0.2em] mb-2">🍴 Kuliner</p>
                <p class="text-sm text-amber-900 leading-relaxed">Bazaar makanan, festival kuliner, dan kompetisi memasak yang menampilkan cita rasa {{ $city_name }}.</p>
            </div>
        </div>
    @endif

    @if ($page_type === 'tips')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 flex gap-3 items-start">
                <span class="text-2xl shrink-0">📍</span>
                <div>
                    <h3 class="font-semibold text-slate-900">Prioritaskan Lokasi</h3>
                    <p class="text-sm text-slate-600 mt-1">Pilih area yang sesuai dengan tujuan perjalanan Anda. Hotel pusat kota praktis tapi bising — area pinggiran lebih tenang & hemat 20-40%.</p>
                </div>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 flex gap-3 items-start">
                <span class="text-2xl shrink-0">💰</span>
                <div>
                    <h3 class="font-semibold text-slate-900">Cek Total Harga</h3>
                    <p class="text-sm text-slate-600 mt-1">Harga dasar sering belum termasuk pajak 11% dan service 5-10%. Selalu lihat 'total harga' sebelum booking.</p>
                </div>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 flex gap-3 items-start">
                <span class="text-2xl shrink-0">⭐</span>
                <div>
                    <h3 class="font-semibold text-slate-900">Baca Review Terbaru</h3>
                    <p class="text-sm text-slate-600 mt-1">Fokus pada review 3 bulan terakhir. Cari kata kunci spesifik sesuai prioritas: Wi-Fi, kebersihan, sarapan, kedap suara.</p>
                </div>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 flex gap-3 items-start">
                <span class="text-2xl shrink-0">📅</span>
                <div>
                    <h3 class="font-semibold text-slate-900">Timing adalah Kunci</h3>
                    <p class="text-sm text-slate-600 mt-1">Weekday 10-20% lebih murah. Peak season naik 30-50%. Booking 2-4 minggu sebelumnya untuk harga terbaik.</p>
                </div>
            </div>
        </div>
    @endif

    @if ($page_type === 'guide')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="text-2xl mb-2">✈️</div>
                <h3 class="font-semibold text-slate-900">Cara ke {{ $city_name }}</h3>
                <p class="text-sm text-slate-600 mt-1">Penerbangan langsung dari kota besar Indonesia. Dari bandara, taksi atau shuttle ke pusat kota 30-90 menit.</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="text-2xl mb-2">🏨</div>
                <h3 class="font-semibold text-slate-900">Akomodasi</h3>
                <p class="text-sm text-slate-600 mt-1">Dari hostel Rp 100rb hingga resort Rp 5jt+. Pilih sesuai zona kenyamanan dan budget Anda.</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="text-2xl mb-2">🍜</div>
                <h3 class="font-semibold text-slate-900">Kuliner Wajib</h3>
                <p class="text-sm text-slate-600 mt-1">Jelajahi street food, pasar tradisional, dan restoran lokal untuk pengalaman rasa autentik {{ $city_name }}.</p>
            </div>
        </div>
    @endif

    {{-- Related links untuk internal linking --}}
    <section class="mt-10 border-t border-slate-200 pt-8">
        <h2 class="font-display text-xl font-bold text-slate-900 mb-4">Halaman Terkait</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
            <a href="/hotels-in-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Hotel di {{ $city_name }}</a>
            <a href="/best-hotels-{{ $city }}-{{ date('Y') }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Hotel terbaik {{ date('Y') }}</a>
            <a href="/hotel-murah-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Hotel murah</a>
            <a href="/best-time-to-visit-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Waktu terbaik berkunjung</a>
            <a href="/honeymoon-stay-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Honeymoon stay</a>
            <a href="/family-stay-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Family stay</a>
        </div>
    </section>

    {{-- CTA Box --}}
    <section class="mt-8 bg-gradient-to-br from-indigo-600 to-violet-600 text-white rounded-3xl p-8 lg:p-10 shadow-xl shadow-indigo-500/30">
        <div class="grid md:grid-cols-[1fr_auto] gap-6 items-center">
            <div>
                <p class="text-xs font-bold text-indigo-100 uppercase tracking-[0.2em] mb-2">Rencanakan Perjalanan Anda</p>
                <h3 class="font-display text-2xl md:text-3xl font-bold leading-tight">Booking hotel di {{ $city_name }} sekarang</h3>
                <p class="mt-2 text-indigo-100/90 text-sm leading-relaxed">Free cancellation H-1, customer service 24/7, dan harga terbaik tanpa biaya perantara.</p>
            </div>
            <a href="/booking" class="inline-flex items-center gap-2 bg-white text-indigo-700 hover:bg-indigo-50 px-6 py-3 rounded-xl font-semibold shadow-lg transition-colors whitespace-nowrap">
                Cari Kamar
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </section>
@endsection
