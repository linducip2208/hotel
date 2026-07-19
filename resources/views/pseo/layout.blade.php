@extends('public.layout')

{{-- Allow children to set: $title, $meta_description, $intro, $faqs, $schema --}}

@push('head')
    @isset($meta_description)
        <meta name="description" content="{{ $meta_description }}">
    @endisset
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title ?? config('app.name') }}">
    @isset($meta_description)
        <meta property="og:description" content="{{ $meta_description }}">
    @endisset
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? config('app.name') }}">
    @isset($meta_description)
        <meta name="twitter:description" content="{{ $meta_description }}">
    @endisset

    {{-- JSON-LD Schema --}}
    @if (! empty($schema))
        <script type="application/ld+json">@json($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    @endif

    {{-- BreadcrumbList --}}
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $title ?? 'Halaman', 'item' => url()->current()],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('title', $title ?? config('app.name'))

@section('content')
{{-- Page hero --}}
<section class="bg-gradient-to-br from-slate-900 via-indigo-950 to-violet-950 text-white pt-32 pb-12">
    <div class="max-w-5xl mx-auto px-4 lg:px-8">
        <nav aria-label="Breadcrumb" class="text-xs text-white/60 mb-3 flex items-center gap-1.5">
            <a href="/" class="hover:text-white">Beranda</a>
            <span>›</span>
            <span class="text-white/80">{{ $title ?? '' }}</span>
        </nav>
        <h1 class="font-display text-3xl md:text-4xl lg:text-5xl font-bold leading-tight tracking-tight">
            {{ $title ?? '' }}
        </h1>
        @isset($meta_description)
            <p class="mt-3 text-indigo-100/90 max-w-3xl text-sm md:text-base leading-relaxed">{{ $meta_description }}</p>
        @endisset
    </div>
</section>

<div class="max-w-5xl mx-auto px-4 lg:px-8 py-10 lg:py-14">

    {{-- Long-form intro (300+ kata) --}}
    @isset($intro)
        <div class="prose prose-slate max-w-none mb-10 text-[15px] leading-relaxed">
            @foreach (explode("\n\n", $intro) as $para)
                <p class="mb-4 text-slate-700">{{ trim($para) }}</p>
            @endforeach
        </div>
    @endisset

    {{-- Page-specific body --}}
    @yield('pseo_body')

    {{-- Quick CTA --}}
    <section class="mt-12 bg-gradient-to-br from-indigo-600 to-violet-600 text-white rounded-3xl p-8 lg:p-10 shadow-xl shadow-indigo-500/30">
        <div class="grid md:grid-cols-[1fr_auto] gap-6 items-center">
            <div>
                <p class="text-xs font-bold text-indigo-100 uppercase tracking-[0.2em] mb-2">Booking Langsung</p>
                <h3 class="font-display text-2xl md:text-3xl font-bold leading-tight">Dapatkan harga terbaik tanpa biaya perantara</h3>
                <p class="mt-2 text-indigo-100/90 text-sm leading-relaxed">Free cancellation H-1, customer service 24/7, dan booking 30 detik via web.</p>
            </div>
            <a href="/booking" class="inline-flex items-center gap-2 bg-white text-indigo-700 hover:bg-indigo-50 px-6 py-3 rounded-xl font-semibold shadow-lg transition-colors whitespace-nowrap">
                Cari Kamar
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </section>

    {{-- FAQ --}}
    @isset($faqs)
        @if (! empty($faqs))
            <section class="mt-12" itemscope itemtype="https://schema.org/FAQPage">
                <h2 class="font-display text-2xl md:text-3xl font-bold text-slate-900 mb-5">Pertanyaan Umum</h2>
                <dl class="space-y-3">
                    @foreach ($faqs as $f)
                        <div class="bg-white border border-slate-200 rounded-2xl p-5 hover:shadow-md transition-shadow"
                             itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <dt class="font-semibold text-slate-900" itemprop="name">{{ $f['q'] }}</dt>
                            <dd class="text-slate-600 mt-2 text-sm leading-relaxed"
                                itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <span itemprop="text">{{ $f['a'] }}</span>
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endif
    @endisset

    {{-- ═══════ Source Code Hotel HMS CTA ═══════ --}}
    <div class="max-w-4xl mx-auto mt-12 mb-8 px-4">
        <div class="bg-gradient-to-br from-indigo-700 via-indigo-800 to-violet-900 rounded-3xl p-8 lg:p-10 shadow-2xl shadow-indigo-900/30 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-20" style="background-image:radial-gradient(circle at 20% 50%, rgba(255,255,255,.3), transparent 50%),radial-gradient(circle at 80% 50%, rgba(139,92,246,.5), transparent 50%)"></div>
            <div class="relative grid lg:grid-cols-5 gap-6 items-center">
                <div class="lg:col-span-3">
                    <div class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur border border-white/20 rounded-full px-3 py-1 mb-4">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-xs font-semibold text-white/90">Source Code Tersedia</span>
                    </div>
                    <h2 class="text-2xl lg:text-3xl font-bold leading-tight mb-3">Punya Aplikasi Hotel Sendiri — Source Code Lengkap HotelHub HMS</h2>
                    <p class="text-indigo-100/80 text-sm leading-relaxed mb-2">
                        Dapatkan <strong class="text-white">source code lengkap</strong> sistem manajemen hotel all-in-one: Front Office, Booking Engine, POS, Accounting, Channel Manager (Booking.com, Agoda, Traveloka), Revenue Management, Housekeeping, HR & Payroll — <strong class="text-white">23+ modul dalam 1 dashboard Laravel 11.</strong>
                    </p>
                    <ul class="text-sm text-indigo-100/70 space-y-1 mb-0">
                        <li class="flex items-start gap-1.5"><span class="text-emerald-400 mt-0.5">✓</span> Source code Laravel 11 + MySQL — full ownership, self-host</li>
                        <li class="flex items-start gap-1.5"><span class="text-emerald-400 mt-0.5">✓</span> 122 automated tests + 27 dokumentasi teknis</li>
                        <li class="flex items-start gap-1.5"><span class="text-emerald-400 mt-0.5">✓</span> BYOK Payment (13 gateway) + AI (20+ provider) + OTA (10 channel)</li>
                        <li class="flex items-start gap-1.5"><span class="text-emerald-400 mt-0.5">✓</span> Responsive, PWA-ready, white-label siap pakai</li>
                    </ul>
                </div>
                <div class="lg:col-span-2 flex flex-col gap-3">
                    <a href="https://wa.me/62081296052010?text=Halo%2C+saya+tertarik+dengan+source+code+HotelHub+HMS.+Boleh+info+lebih+lanjut%3F"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-2xl px-6 py-4 shadow-lg shadow-emerald-500/30 transition-all text-sm">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
                        Chat WhatsApp — 081296052010
                    </a>
                    <a href="/docs" class="inline-flex items-center justify-center gap-2 bg-white/10 backdrop-blur border border-white/30 hover:bg-white/20 text-white font-semibold rounded-2xl px-6 py-3.5 transition-all text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Lihat Dokumentasi Lengkap
                    </a>
                    <p class="text-center text-xs text-indigo-200/60">📞 <strong class="text-white">081296052010</strong> · Laravel 11 · Full Stack · Siap Deploy</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- ═══════ Floating WhatsApp Badge ═══════ --}}
@push('head')
<style>
@keyframes wa-bounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-4px)}}
</style>
@endpush
<div id="wa-float" style="position:fixed;bottom:24px;right:24px;z-index:9999;background:#25d366;color:#fff;border-radius:50px;padding:10px 18px;font-size:13px;font-weight:600;box-shadow:0 4px 20px rgba(37,211,102,.45);display:flex;align-items:center;gap:8px;cursor:pointer;animation:wa-bounce 2.5s infinite;" onclick="window.open('https://wa.me/62081296052010?text=Halo%2C+saya+tertarik+dengan+source+code+HotelHub+HMS+dari+halaman+PSEO.+Boleh+info+lebih+lanjut%3F','_blank')">
    <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    Source Code · 081296052010
</div>
