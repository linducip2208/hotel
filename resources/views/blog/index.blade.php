@extends('public.layout')

@push('head')
    <meta name="description" content="{{ $metaDescription ?? 'Blog — ' . config('app.name') }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title ?? 'Blog — ' . config('app.name') }}">
    <meta property="og:description" content="{{ $metaDescription ?? '' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'Blog — ' . config('app.name') }}">
    <meta name="twitter:description" content="{{ $metaDescription ?? '' }}">
    @if (! empty($schema))
        <script type="application/ld+json">@json($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    @endif
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => route('blog.index')],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('title', $title ?? 'Blog — ' . config('app.name'))

@section('content')
{{-- Hero --}}
<section class="bg-gradient-to-br from-slate-900 via-indigo-950 to-violet-950 text-white pt-32 pb-14">
    <div class="max-w-6xl mx-auto px-4 lg:px-8">
        <nav aria-label="Breadcrumb" class="text-xs text-white/60 mb-3 flex items-center gap-1.5">
            <a href="/" class="hover:text-white">Beranda</a>
            <span>›</span>
            <span class="text-white/80">Blog</span>
            @isset($category)
                <span>›</span>
                <span class="text-white/80">{{ $category->name }}</span>
            @endisset
        </nav>
        <h1 class="font-display text-3xl md:text-4xl lg:text-5xl font-bold leading-tight tracking-tight">
            @isset($category)
                Kategori: {{ $category->name }}
            @else
                Blog — Tips Perhotelan &amp; Panduan Wisata
            @endisset
        </h1>
        <p class="mt-3 text-indigo-100/90 max-w-3xl text-sm md:text-base leading-relaxed">
            {{ $metaDescription ?? 'Tips perhotelan, panduan wisata, review hotel, dan berita terbaru seputar dunia perhotelan Indonesia.' }}
        </p>
    </div>
</section>

{{-- Main content --}}
<div class="max-w-6xl mx-auto px-4 lg:px-8 py-10 lg:py-14">
    <div class="grid lg:grid-cols-[1fr_300px] gap-10">
        {{-- Posts grid --}}
        <div>
            @if($posts->count() > 0)
                <div class="grid md:grid-cols-2 gap-6">
                    @foreach($posts as $post)
                        <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-lg transition-shadow group">
                            @if($post->featured_image)
                                <a href="{{ route('blog.show', $post->slug) }}" class="block h-48 overflow-hidden">
                                    <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                </a>
                            @else
                                <a href="{{ route('blog.show', $post->slug) }}" class="block h-48 bg-gradient-to-br from-indigo-100 to-violet-200 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-indigo-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                </a>
                            @endif
                            <div class="p-5">
                                @if($post->category)
                                    <a href="{{ route('blog.category', $post->category->slug) }}" class="inline-block text-[11px] font-semibold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full mb-2.5 hover:bg-indigo-100 transition-colors">
                                        {{ $post->category->name }}
                                    </a>
                                @endif
                                <h3 class="font-display text-lg font-bold text-slate-900 leading-snug mb-2 group-hover:text-indigo-600 transition-colors">
                                    <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                                </h3>
                                <p class="text-sm text-slate-500 leading-relaxed line-clamp-2 mb-3">{{ $post->excerpt }}</p>
                                <div class="flex items-center justify-between text-xs text-slate-400">
                                    <span>{{ $post->published_at?->format('d M Y') }}</span>
                                    <a href="{{ route('blog.show', $post->slug) }}" class="font-medium text-indigo-600 hover:text-indigo-700">Baca selengkapnya →</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    <p class="text-slate-500 text-lg">Belum ada artikel.</p>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <aside class="space-y-6">
            {{-- Search --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <form action="{{ route('blog.index') }}" method="GET">
                    <label class="text-xs font-semibold text-slate-400 uppercase tracking-[0.12em] mb-2 block">Cari Artikel</label>
                    <div class="flex">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Kata kunci..."
                               class="flex-1 px-3 py-2 text-sm border border-slate-300 rounded-l-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-r-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Categories --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-[0.12em] mb-3">Kategori</h3>
                <ul class="space-y-1.5">
                    @foreach($categories as $cat)
                        <li>
                            <a href="{{ route('blog.category', $cat->slug) }}"
                               class="flex items-center justify-between text-sm text-slate-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg px-2 py-1.5 -mx-2 transition-colors {{ (request()->route('slug') ?? '') === $cat->slug ? 'text-indigo-700 bg-indigo-50 font-medium' : '' }}">
                                <span>{{ $cat->name }}</span>
                                <span class="text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">{{ $cat->posts_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Recent posts --}}
            @if($recentPosts->count() > 0)
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-[0.12em] mb-3">Artikel Terbaru</h3>
                <ul class="space-y-3">
                    @foreach($recentPosts as $rp)
                        <li>
                            <a href="{{ route('blog.show', $rp->slug) }}" class="text-sm text-slate-700 hover:text-indigo-600 transition-colors line-clamp-2 leading-snug">
                                {{ $rp->title }}
                            </a>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $rp->published_at?->format('d M Y') }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Source Code CTA --}}
            <div class="bg-gradient-to-br from-indigo-700 via-indigo-800 to-violet-900 rounded-2xl p-5 text-white shadow-lg shadow-indigo-500/20">
                <div class="inline-flex items-center gap-1.5 bg-white/15 border border-white/20 rounded-full px-2.5 py-0.5 mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span class="text-[10px] font-semibold text-white/90">Source Code</span>
                </div>
                <h4 class="font-bold text-sm leading-snug mb-2">Punya Aplikasi Hotel Sendiri</h4>
                <p class="text-xs text-indigo-100/80 leading-relaxed mb-3">
                    Dapatkan source code lengkap HotelHub HMS — Laravel 11, 23+ modul, siap deploy.
                </p>
                <a href="https://wa.me/62081296052010?text=Halo%2C+saya+tertarik+dengan+source+code+HotelHub+HMS+dari+halaman+blog.+Boleh+info+lebih+lanjut%3F"
                   target="_blank" rel="noopener"
                   class="block text-center bg-emerald-500 hover:bg-emerald-400 text-white text-xs font-bold rounded-xl py-2.5 mt-2 transition-colors">
                    Chat WhatsApp
                </a>
                <p class="text-center text-[10px] text-indigo-200/60 mt-2">📞 081296052010</p>
            </div>
        </aside>
    </div>
</div>

{{-- Bottom Source Code CTA --}}
<div class="max-w-6xl mx-auto px-4 lg:px-8 pb-12">
    <div class="bg-gradient-to-br from-indigo-700 via-indigo-800 to-violet-900 rounded-3xl p-8 lg:p-10 shadow-2xl shadow-indigo-900/30 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-20" style="background-image:radial-gradient(circle at 20% 50%, rgba(255,255,255,.3), transparent 50%),radial-gradient(circle at 80% 50%, rgba(139,92,246,.5), transparent 50%)"></div>
        <div class="relative grid lg:grid-cols-5 gap-6 items-center">
            <div class="lg:col-span-3">
                <div class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur border border-white/20 rounded-full px-3 py-1 mb-4">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-xs font-semibold text-white/90">Source Code Tersedia</span>
                </div>
                <h2 class="text-xl lg:text-2xl font-bold leading-tight mb-2">Punya Aplikasi Hotel Sendiri — Source Code Lengkap HotelHub HMS</h2>
                <p class="text-indigo-100/80 text-sm leading-relaxed mb-2">
                    Source code <strong class="text-white">Laravel 11 all-in-one</strong>: Front Office, Booking Engine, POS, Accounting, Channel Manager (Booking.com, Agoda, Traveloka), Revenue Management, Housekeeping, HR & Payroll.
                </p>
                <ul class="text-sm text-indigo-100/70 space-y-1">
                    <li class="flex items-start gap-1.5"><span class="text-emerald-400 mt-0.5">✓</span> Full ownership — self-host, bebas kustomisasi</li>
                    <li class="flex items-start gap-1.5"><span class="text-emerald-400 mt-0.5">✓</span> BYOK Payment (13 gateway) + AI (20+ provider) + OTA (10 channel)</li>
                    <li class="flex items-start gap-1.5"><span class="text-emerald-400 mt-0.5">✓</span> Responsive, PWA-ready, white-label siap pakai</li>
                </ul>
            </div>
            <div class="lg:col-span-2 flex flex-col gap-2.5">
                <a href="https://wa.me/62081296052010?text=Halo%2C+saya+tertarik+dengan+source+code+HotelHub+HMS+dari+halaman+blog.+Boleh+info+lebih+lanjut%3F"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-2xl px-5 py-3.5 shadow-lg shadow-emerald-500/30 transition-all text-sm">
                    Chat WhatsApp — 081296052010
                </a>
                <a href="/docs" class="inline-flex items-center justify-center gap-2 bg-white/10 backdrop-blur border border-white/30 hover:bg-white/20 text-white font-semibold rounded-2xl px-5 py-3.5 transition-all text-sm">
                    Lihat Dokumentasi Lengkap
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
