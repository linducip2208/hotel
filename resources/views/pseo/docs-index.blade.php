@extends('pseo.layout')

@push('head')
<style>
    :root {
        --brand: #4f46e5;
        --brand-light: #6366f1;
    }

    /* ── Scroll reveal ── */
    .reveal {
        opacity: 0;
        transform: translateY(32px);
        transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1),
                    transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .reveal.visible {
        opacity: 1;
        transform: translateY(0);
    }
    .reveal-delay-1 { transition-delay: 0.08s; }
    .reveal-delay-2 { transition-delay: 0.16s; }
    .reveal-delay-3 { transition-delay: 0.24s; }
    .reveal-delay-4 { transition-delay: 0.32s; }

    /* ── Card hover lift ── */
    .card-lift {
        transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1),
                    box-shadow 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .card-lift:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.12);
    }

    /* ── Shimmer placeholder ── */
    .shimmer {
        background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
        background-size: 200% 100%;
        animation: shimmerAnim 2.2s ease-in-out infinite;
    }
    @keyframes shimmerAnim {
        0%   { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* ── Jump nav active ── */
    .jump-link {
        transition: color 0.2s, background 0.2s;
    }
    .jump-link.active {
        color: var(--brand);
        background: #eef2ff;
    }

    /* ── Accordion icon ── */
    .accordion-icon {
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .accordion-open .accordion-icon {
        transform: rotate(180deg);
    }

    /* ── Print ── */
    @media print {
        .jump-nav, .demo-card, .cta-section { page-break-inside: avoid; }
    }

    /* ── Reduced motion ── */
    @media (prefers-reduced-motion: reduce) {
        .reveal { opacity: 1; transform: none; transition: none; }
        .card-lift:hover { transform: none; }
    }
</style>
@endpush

@section('pseo_body')

{{-- ════════════════════════ JUMP NAV ════════════════════════ --}}
<nav x-data="{ active: 'demo' }" x-init="
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) active = e.target.id; });
    }, { rootMargin: '-100px 0px -60% 0px', threshold: 0 });
    document.querySelectorAll('section[id]').forEach(s => observer.observe(s));
" class="jump-nav sticky top-16 lg:top-20 z-30 -mx-4 lg:-mx-8 mb-8 bg-white/95 backdrop-blur border-b border-slate-200 overflow-x-auto shadow-sm">
    <div class="flex items-center gap-1 px-4 lg:px-8 py-2.5 min-w-max">
        <a href="#demo-accounts" @click.prevent="document.getElementById('demo-accounts').scrollIntoView({behavior:'smooth'})"
           :class="active === 'demo-accounts' ? 'active' : ''"
           class="jump-link flex items-center gap-1.5 text-xs font-medium text-slate-600 hover:text-indigo-600 px-3 py-1.5 rounded-full whitespace-nowrap transition-colors">🔑 Demo</a>
        <a href="#struktur-menu" @click.prevent="document.getElementById('struktur-menu').scrollIntoView({behavior:'smooth'})"
           :class="active === 'struktur-menu' ? 'active' : ''"
           class="jump-link flex items-center gap-1.5 text-xs font-medium text-slate-600 hover:text-indigo-600 px-3 py-1.5 rounded-full whitespace-nowrap transition-colors">📋 Menu</a>
        <a href="#tutorial" @click.prevent="document.getElementById('tutorial').scrollIntoView({behavior:'smooth'})"
           :class="active === 'tutorial' ? 'active' : ''"
           class="jump-link flex items-center gap-1.5 text-xs font-medium text-slate-600 hover:text-indigo-600 px-3 py-1.5 rounded-full whitespace-nowrap transition-colors">🎓 Tutorial</a>
        <a href="#fitur" @click.prevent="document.getElementById('fitur').scrollIntoView({behavior:'smooth'})"
           :class="active === 'fitur' ? 'active' : ''"
           class="jump-link flex items-center gap-1.5 text-xs font-medium text-slate-600 hover:text-indigo-600 px-3 py-1.5 rounded-full whitespace-nowrap transition-colors">✨ Fitur</a>
        <a href="#daftar-fitur" @click.prevent="document.getElementById('daftar-fitur').scrollIntoView({behavior:'smooth'})"
            :class="active === 'daftar-fitur' ? 'active' : ''"
            class="jump-link flex items-center gap-1.5 text-xs font-medium text-slate-600 hover:text-indigo-600 px-3 py-1.5 rounded-full whitespace-nowrap transition-colors">📋 Tabel Fitur</a>
        <a href="#perbandingan-kompetitor" @click.prevent="document.getElementById('perbandingan-kompetitor').scrollIntoView({behavior:'smooth'})"
            :class="active === 'perbandingan-kompetitor' ? 'active' : ''"
            class="jump-link flex items-center gap-1.5 text-xs font-medium text-slate-600 hover:text-indigo-600 px-3 py-1.5 rounded-full whitespace-nowrap transition-colors">⚔️ Perbandingan</a>
        <a href="#cta" @click.prevent="document.getElementById('cta').scrollIntoView({behavior:'smooth'})"
            :class="active === 'cta' ? 'active' : ''"
            class="jump-link flex items-center gap-1.5 text-xs font-medium text-slate-600 hover:text-indigo-600 px-3 py-1.5 rounded-full whitespace-nowrap transition-colors">🚀 Coba</a>
    </div>
</nav>

{{-- ════════════════════════ DEMO ACCOUNTS ════════════════════════ --}}
<section id="demo-accounts" class="reveal mb-12 scroll-mt-28">
    <div class="flex items-center gap-2.5 mb-2">
        <span class="text-2xl">🔑</span>
        <h2 class="font-display text-2xl md:text-3xl font-bold text-slate-900">Akun Demo</h2>
    </div>
    <p class="text-slate-500 text-sm mb-6 max-w-2xl">Gunakan kredensial berikut untuk login ke <a href="/admin" class="text-indigo-600 font-medium hover:underline">admin panel</a>. Setiap role memiliki akses dan tampilan dashboard yang berbeda.</p>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach ($demoAccounts as $i => $acc)
            <div class="reveal card-lift bg-white border border-slate-200 rounded-2xl p-4 hover:border-{{ $acc['color'] }}-300 transition-colors" style="transition-delay: {{ $i * 0.06 }}s">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-xl">{{ $acc['icon'] }}</span>
                    <span class="font-semibold text-slate-900 text-sm">{{ $acc['role'] }}</span>
                </div>
                <div class="space-y-1.5 text-xs font-mono">
                    <div class="flex items-center gap-1.5 text-slate-500">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span class="truncate">{{ $acc['email'] }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-slate-400">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>{{ $acc['password'] }}</span>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-3 leading-relaxed border-t border-slate-100 pt-2.5">{{ $acc['scope'] }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ════════════════════════ STRUKTUR MENU ════════════════════════ --}}
<section id="struktur-menu" class="reveal mb-12 scroll-mt-28">
    <div class="flex items-center gap-2.5 mb-2">
        <span class="text-2xl">📋</span>
        <h2 class="font-display text-2xl md:text-3xl font-bold text-slate-900">Struktur Menu Admin</h2>
    </div>
    <p class="text-slate-500 text-sm mb-6 max-w-2xl">Berikut adalah navigation group di admin panel — total <strong>{{ collect($menuStructure)->sum(fn($g) => collect($g['sections'])->sum(fn($s) => count($s['items']))) }}</strong> menu items dalam <strong>{{ count($menuStructure) }}</strong> grup.</p>

    @foreach ($menuStructure as $group)
        <div class="reveal mb-5">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-lg">{{ $group['icon'] }}</span>
                <h3 class="font-display text-lg font-bold text-slate-800">{{ $group['group'] }}</h3>
                <span class="text-xs text-slate-400 font-medium">{{ collect($group['sections'])->sum(fn($s) => count($s['items'])) }} menu</span>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($group['sections'] as $section)
                    <div class="card-lift bg-white border border-slate-200 rounded-xl p-4 hover:border-indigo-200">
                        <p class="text-xs font-bold text-indigo-600 uppercase tracking-[0.06em] mb-2.5">{{ $section['label'] }}</p>
                        <ul class="space-y-1">
                            @foreach ($section['items'] as $item)
                                <li class="flex items-center gap-2 text-sm text-slate-600">
                                    <span class="w-1 h-1 rounded-full bg-slate-300 shrink-0"></span>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</section>

{{-- ════════════════════════ TUTORIAL ════════════════════════ --}}
<section id="tutorial" class="reveal mb-12 scroll-mt-28">
    <div class="flex items-center gap-2.5 mb-2">
        <span class="text-2xl">🎓</span>
        <h2 class="font-display text-2xl md:text-3xl font-bold text-slate-900">Tutorial Langkah Demi Langkah</h2>
    </div>
    <p class="text-slate-500 text-sm mb-6 max-w-2xl">{{ count($tutorial) }} fase tutorial mencakup seluruh alur bisnis hotel — dari setup awal hingga advanced revenue management. Klik fase untuk melihat detail langkah.</p>

    <div class="space-y-3">
        @foreach ($tutorial as $idx => $phase)
            <div x-data="{ open: {{ $idx === 0 ? 'true' : 'false' }} }"
                 :class="open ? 'accordion-open' : ''"
                 class="reveal bg-white border border-slate-200 rounded-2xl overflow-hidden card-lift">
                <button @click="open = !open"
                        class="w-full flex items-center justify-between gap-4 px-5 py-4 hover:bg-slate-50 transition-colors text-left">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">{{ $phase['icon'] }}</span>
                        <div>
                            <span class="font-semibold text-slate-900 text-sm">{{ $phase['phase'] }}</span>
                            <span class="text-xs text-slate-400 ml-2">{{ count($phase['steps']) }} langkah</span>
                        </div>
                    </div>
                    <svg class="accordion-icon w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse>
                    <div class="px-5 pb-5 border-t border-slate-100">
                        <ol class="mt-4 space-y-3">
                            @foreach ($phase['steps'] as $si => $step)
                                <li class="flex gap-3 text-sm leading-relaxed">
                                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center mt-0.5">{{ $si + 1 }}</span>
                                    <span class="text-slate-700 pt-0.5">{!! $step !!}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- ════════════════════════ FITUR LENGKAP ════════════════════════ --}}
<section id="fitur" class="reveal mb-12 scroll-mt-28">
    <div class="flex items-center gap-2.5 mb-2">
        <span class="text-2xl">✨</span>
        <h2 class="font-display text-2xl md:text-3xl font-bold text-slate-900">Fitur Lengkap</h2>
    </div>
    <p class="text-slate-500 text-sm mb-8 max-w-2xl">{{ count($features) }} modul utama sistem manajemen hotel. Setiap modul dirancang untuk alur kerja perhotelan profesional — dari front desk hingga laporan eksekutif.</p>

    @foreach ($features as $i => $feature)
        <div class="reveal grid md:grid-cols-5 gap-6 mb-10 items-center {{ $i % 2 === 1 ? 'md:grid-flow-dense' : '' }}">
            {{-- Screenshot placeholder --}}
            <div class="md:col-span-2 {{ $i % 2 === 1 ? 'md:col-start-4' : '' }}">
                <div class="shimmer rounded-2xl border border-slate-200 overflow-hidden aspect-[4/3] flex items-center justify-center relative group">
                    <div class="absolute top-0 inset-x-0 h-7 bg-slate-800 flex items-center gap-1.5 px-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        <span class="ml-2 text-[10px] text-slate-400 font-mono truncate">admin/{{ Str::slug($feature['group']) }}</span>
                    </div>
                    <div class="text-center mt-4">
                        <span class="text-5xl block mb-3">{{ $feature['icon'] }}</span>
                        <p class="text-slate-400 text-sm font-medium">{{ $feature['group'] }}</p>
                        <p class="text-xs text-slate-300 mt-1">Screenshot akan ditambahkan</p>
                    </div>
                </div>
            </div>

            {{-- Description --}}
            <div class="md:col-span-3 {{ $i % 2 === 1 ? 'md:col-start-1 md:row-start-1' : '' }}">
                <h3 class="font-display text-xl font-bold text-slate-900 flex items-center gap-2 mb-3">
                    <span>{{ $feature['icon'] }}</span>
                    {{ $feature['group'] }}
                </h3>
                <p class="text-slate-600 text-sm leading-relaxed mb-4">{{ $feature['description'] }}</p>
                <ul class="space-y-2">
                    @foreach ($feature['bullets'] as $bullet)
                        <li class="flex items-start gap-2.5 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-indigo-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ $bullet }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endforeach
</section>

{{-- ════════════════════════ DAFTAR FITUR LENGKAP (dari 01-FEATURES.md) ════════════════════════ --}}
@if(!empty($featureModules))
<section id="daftar-fitur" class="reveal mb-12 scroll-mt-28">
    <div class="flex items-center gap-2.5 mb-2">
        <span class="text-2xl">📋</span>
        <h2 class="font-display text-2xl md:text-3xl font-bold text-slate-900">Daftar Fitur Lengkap</h2>
    </div>
    <p class="text-slate-500 text-sm mb-6 max-w-2xl">
        <strong>{{ collect($featureModules)->sum(fn($m) => count($m['features'])) }}</strong> fitur dalam <strong>{{ count($featureModules) }}</strong> modul.
        <span class="inline-flex items-center gap-1 ml-2">
            <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> MVP
            <span class="w-3 h-3 rounded-full bg-amber-500 inline-block ml-1"></span> Phase 2
            <span class="w-3 h-3 rounded-full bg-blue-500 inline-block ml-1"></span> Phase 3+
        </span>
    </p>

    @foreach ($featureModules as $modIdx => $module)
        <div class="reveal mb-8" x-data="{ open: {{ $modIdx < 3 ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between gap-3 bg-white border border-slate-200 rounded-2xl px-5 py-4 hover:border-indigo-300 card-lift transition-colors text-left">
                <div class="flex items-center gap-3">
                    <span class="font-display font-bold text-slate-800 text-sm">{{ $loop->iteration }}. {{ $module['name'] }}</span>
                    <span class="text-xs text-slate-400 font-medium">{{ count($module['features']) }} fitur</span>
                </div>
                <svg :class="open ? 'rotate-180' : ''" class="w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-collapse>
                <div class="bg-white border border-t-0 border-slate-200 rounded-b-2xl px-4 pb-4 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-left py-3 px-2 text-xs font-bold text-slate-400 uppercase tracking-wider w-12">#</th>
                                <th class="text-left py-3 px-2 text-xs font-bold text-slate-400 uppercase tracking-wider">Fitur</th>
                                <th class="text-center py-3 px-2 text-xs font-bold text-slate-400 uppercase tracking-wider w-24">Phase</th>
                                <th class="text-left py-3 px-2 text-xs font-bold text-slate-400 uppercase tracking-wider hidden md:table-cell">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($module['features'] as $feat)
                                <tr class="border-b border-slate-50 hover:bg-slate-50/60 transition-colors">
                                    <td class="py-2.5 px-2 text-xs text-slate-400 font-mono">{{ $feat['number'] }}</td>
                                    <td class="py-2.5 px-2 text-slate-700 font-medium">{{ $feat['name'] }}</td>
                                    <td class="py-2.5 px-2 text-center">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full
                                            {{ $feat['phase'] === 'mvp' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                            {{ $feat['phase'] === 'phase2' ? 'bg-amber-100 text-amber-700' : '' }}
                                            {{ $feat['phase'] === 'phase3' ? 'bg-blue-100 text-blue-700' : '' }}">
                                            {!! $feat['phaseLabel'] !!}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-2 text-xs text-slate-500 hidden md:table-cell">{{ $feat['notes'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</section>
@endif

{{-- ════════════════════════ PERBANDINGAN KOMPETITOR ════════════════════════ --}}
@include('pseo.comparison')

{{-- ════════════════════════ CTA ════════════════════════ --}}
<section id="cta" class="reveal scroll-mt-28 mb-4">
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-700 text-white rounded-3xl p-8 lg:p-12 shadow-xl shadow-indigo-500/30 text-center">
        <span class="text-4xl block mb-4">🚀</span>
        <h2 class="font-display text-2xl md:text-3xl lg:text-4xl font-bold leading-tight">Siap Mencoba?</h2>
        <p class="mt-3 text-indigo-100/90 text-sm md:text-base max-w-xl mx-auto leading-relaxed">
            Login ke admin panel dengan akun demo di atas. Eksplor semua modul, lihat dashboard real-time, dan rasakan workflow operasional hotel yang seamless.
        </p>
        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
            <a href="/admin"
               class="inline-flex items-center gap-2 bg-white text-indigo-700 hover:bg-indigo-50 px-6 py-3 rounded-xl font-semibold shadow-lg transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                Masuk Admin Panel
            </a>
            <a href="/booking"
               class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white border border-white/30 px-6 py-3 rounded-xl font-semibold transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Booking Engine Publik
            </a>
            <a href="/docs/00-OVERVIEW"
               class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white border border-white/30 px-6 py-3 rounded-xl font-semibold transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Dokumentasi Teknis
            </a>
        </div>
        <p class="mt-6 text-xs text-indigo-200/70">
            Butuh bantuan? Hubungi <a href="https://wa.me/62081296052010" class="text-white font-medium hover:underline">WhatsApp Support</a> atau email <a href="mailto:support@demohotel.id" class="text-white font-medium hover:underline">support@demohotel.id</a>
        </p>
    </div>
</section>

<script>
    (function() {
        var reveals = document.querySelectorAll('.reveal');
        if (!reveals.length) return;

        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        reveals.forEach(function(el) { observer.observe(el); });
    })();
</script>

@endsection
