@extends('public.layout')

@push('head')
    <meta name="description" content="{{ $seoDescription ?? '' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription ?? '' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($post->featured_image)
        <meta property="og:image" content="{{ asset($post->featured_image) }}">
    @endif
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription ?? '' }}">
    @if(! empty($schema))
        <script type="application/ld+json">@json($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    @endif
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => route('blog.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title, 'item' => url()->current()],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('title', $seoTitle)

@section('content')
{{-- Hero --}}
<section class="bg-gradient-to-br from-slate-900 via-indigo-950 to-violet-950 text-white pt-32 pb-12">
    <div class="max-w-4xl mx-auto px-4 lg:px-8">
        <nav aria-label="Breadcrumb" class="text-xs text-white/60 mb-3 flex items-center gap-1.5 flex-wrap">
            <a href="/" class="hover:text-white">Beranda</a>
            <span>›</span>
            <a href="{{ route('blog.index') }}" class="hover:text-white">Blog</a>
            @if($post->category)
                <span>›</span>
                <a href="{{ route('blog.category', $post->category->slug) }}" class="hover:text-white">{{ $post->category->name }}</a>
            @endif
        </nav>
        @if($post->category)
            <span class="inline-block text-[11px] font-semibold text-white bg-white/15 backdrop-blur px-3 py-1 rounded-full mb-3">
                {{ $post->category->name }}
            </span>
        @endif
        <h1 class="font-display text-3xl md:text-4xl lg:text-5xl font-bold leading-tight tracking-tight">
            {{ $post->title }}
        </h1>
        <div class="flex items-center gap-4 mt-4 text-sm text-indigo-100/80">
            @if($post->author)
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    {{ $post->author->name }}
                </span>
            @endif
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ $post->published_at?->format('d M Y') }}
            </span>
        </div>
    </div>
</section>

{{-- Article body --}}
<div class="max-w-6xl mx-auto px-4 lg:px-8 py-10 lg:py-14">
    <div class="grid lg:grid-cols-[1fr_300px] gap-10">
        <article>
            @if($post->featured_image)
                <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="w-full rounded-2xl mb-8 shadow-lg" loading="lazy">
            @endif

            <div class="prose prose-slate max-w-none prose-headings:font-display prose-a:text-indigo-600 prose-img:rounded-xl">
                {!! $post->content !!}
            </div>

            {{-- Share --}}
            <div class="mt-10 pt-6 border-t border-slate-200">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-[0.12em] mb-3">Bagikan Artikel</p>
                <div class="flex gap-2">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-3 py-2 rounded-lg transition-colors">Facebook</a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener" class="bg-sky-500 hover:bg-sky-600 text-white text-xs font-medium px-3 py-2 rounded-lg transition-colors">Twitter</a>
                    <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . url()->current()) }}" target="_blank" rel="noopener" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium px-3 py-2 rounded-lg transition-colors">WhatsApp</a>
                </div>
            </div>

            {{-- Related posts --}}
            @if($relatedPosts->count() > 0)
            <div class="mt-10 pt-6 border-t border-slate-200">
                <h3 class="font-display text-xl font-bold text-slate-900 mb-5">Artikel Terkait</h3>
                <div class="grid md:grid-cols-3 gap-4">
                    @foreach($relatedPosts as $rp)
                        <a href="{{ route('blog.show', $rp->slug) }}" class="bg-white border border-slate-200 rounded-xl p-4 hover:shadow-md transition-shadow group">
                            <h4 class="font-semibold text-sm text-slate-800 group-hover:text-indigo-600 transition-colors line-clamp-2 leading-snug">{{ $rp->title }}</h4>
                            <p class="text-xs text-slate-400 mt-1.5">{{ $rp->published_at?->format('d M Y') }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </article>

        {{-- Sidebar --}}
        <aside class="space-y-6">
            {{-- Categories --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-[0.12em] mb-3">Kategori</h3>
                <ul class="space-y-1.5">
                    @foreach($categories as $cat)
                        <li>
                            <a href="{{ route('blog.category', $cat->slug) }}"
                               class="flex items-center justify-between text-sm text-slate-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg px-2 py-1.5 -mx-2 transition-colors {{ $post->category_id === $cat->id ? 'text-indigo-700 bg-indigo-50 font-medium' : '' }}">
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
                            <a href="{{ route('blog.show', $rp->slug) }}" class="text-sm text-slate-700 hover:text-indigo-600 transition-colors line-clamp-2 leading-snug {{ $rp->id === $post->id ? 'text-indigo-600 font-medium' : '' }}">
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
                   class="block text-center bg-emerald-500 hover:bg-emerald-400 text-white text-xs font-bold rounded-xl py-2.5 transition-colors">
                    Chat WhatsApp
                </a>
                <p class="text-center text-[10px] text-indigo-200/60 mt-2">📞 081296052010</p>
            </div>
        </aside>
    </div>
</div>

{{-- Bottom Source Code CTA --}}
<div class="max-w-4xl mx-auto px-4 lg:px-8 pb-12">
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
                    Source code <strong class="text-white">Laravel 11 all-in-one</strong>: Front Office, Booking Engine, POS, Accounting, Channel Manager, Revenue Management, Housekeeping, HR & Payroll.
                </p>
                <ul class="text-sm text-indigo-100/70 space-y-1">
                    <li class="flex items-start gap-1.5"><span class="text-emerald-400 mt-0.5">✓</span> Full ownership — self-host, bebas kustomisasi</li>
                    <li class="flex items-start gap-1.5"><span class="text-emerald-400 mt-0.5">✓</span> 122 automated tests + 27 dokumentasi teknis</li>
                    <li class="flex items-start gap-1.5"><span class="text-emerald-400 mt-0.5">✓</span> White-label siap pakai — branding sendiri</li>
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
