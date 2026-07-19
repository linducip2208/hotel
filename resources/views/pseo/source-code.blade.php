@extends('pseo.layout')

@section('pseo_body')
    @php
        $city = $params['city'] ?? null;
        $cityName = $city
            ? (\App\Support\SeoData::cityName($city) ?? \Illuminate\Support\Str::title(str_replace('-', ' ', $city)))
            : 'Indonesia';
        $kwLabel = $kw_label ?? \Illuminate\Support\Str::title(str_replace('-', ' ', $params['keyword'] ?? 'Aplikasi Hotel'));
    @endphp

    {{-- Source Code Selling USP Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 card-lift">
            <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center mb-3 text-lg">📦</div>
            <h3 class="font-semibold text-slate-900 mb-1">23+ Modul Siap Pakai</h3>
            <p class="text-sm text-slate-600 leading-relaxed">Front Office, POS, Accounting, Channel Manager, Revenue, Housekeeping, HR — all in one dashboard.</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 card-lift">
            <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center mb-3 text-lg">🔒</div>
            <h3 class="font-semibold text-slate-900 mb-1">Full Source Code Ownership</h3>
            <p class="text-sm text-slate-600 leading-relaxed">Self-host, custom bebas, jual ulang — tidak ada lock-in vendor. One-time purchase, lifetime.</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 card-lift">
            <div class="w-10 h-10 bg-violet-100 text-violet-600 rounded-xl flex items-center justify-center mb-3 text-lg">⚡</div>
            <h3 class="font-semibold text-slate-900 mb-1">Siap Deploy 1-3 Hari</h3>
            <p class="text-sm text-slate-600 leading-relaxed">Dokumentasi lengkap, 122 automated tests, tim support siap bantu remote setup.</p>
        </div>
    </div>

    {{-- Modul list --}}
    <section class="mb-10">
        <h2 class="font-display text-xl font-bold text-slate-900 mb-4">Modul HotelHub HMS</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
            @foreach ([
                '🏨 Front Office', '🍽️ POS & F&B', '📊 Accounting', '🌐 Channel Manager',
                '💹 Revenue Mgmt', '🧹 Housekeeping', '👥 HR & Payroll', '📈 Report & Analytics',
                '🔔 Notification', '🤖 AI Assistant', '💳 Payment Gateway', '🛡️ Anti-Fraud',
                '📱 Mobile Ready', '🏢 Multi-Property', '🎫 Event & Banquet', '🛒 Inventory',
                '📧 Email Marketing', '⭐ Loyalty Program', '📋 Audit Trail', '🔌 Webhook & API',
                '🛎️ Concierge', '🧾 Folio & Invoice', '📅 Group Block', '🏊‍♂️ Pool & Spa',
            ] as $mod)
                <div class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700">
                    {{ $mod }}
                </div>
            @endforeach
        </div>
    </section>

    {{-- Pricing Tiers --}}
    <section class="mb-10">
        <h2 class="font-display text-xl font-bold text-slate-900 mb-4">Paket Source Code HotelHub HMS</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-[0.1em] mb-2">Basic</div>
                <p class="font-display text-3xl font-bold text-slate-900 mb-1">Rp 5jt</p>
                <p class="text-xs text-slate-500 mb-4">One-time · Single Property</p>
                <ul class="text-sm text-slate-600 space-y-1.5 mb-5">
                    <li class="flex gap-2"><span class="text-emerald-500">✓</span> Source code lengkap</li>
                    <li class="flex gap-2"><span class="text-emerald-500">✓</span> 1 properti</li>
                    <li class="flex gap-2"><span class="text-emerald-500">✓</span> 23+ modul</li>
                    <li class="flex gap-2"><span class="text-emerald-500">✓</span> Dokumentasi teknis</li>
                    <li class="flex gap-2"><span class="text-emerald-500">✓</span> 30 hari support</li>
                </ul>
                <a href="https://wa.me/62081296052010?text=Halo%2C+tertarik+paket+Basic+HotelHub+HMS"
                   target="_blank" rel="noopener"
                   class="block text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl py-3 text-sm transition-colors">
                    Pilih Basic
                </a>
            </div>
            <div class="bg-white border-2 border-indigo-500 rounded-2xl p-6 relative">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-[0.1em]">Populer</span>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-[0.1em] mb-2">Growth</div>
                <p class="font-display text-3xl font-bold text-slate-900 mb-1">Rp 10jt</p>
                <p class="text-xs text-slate-500 mb-4">One-time · Multi-Property</p>
                <ul class="text-sm text-slate-600 space-y-1.5 mb-5">
                    <li class="flex gap-2"><span class="text-emerald-500">✓</span> Semua fitur Basic</li>
                    <li class="flex gap-2"><span class="text-emerald-500">✓</span> Multi-properti</li>
                    <li class="flex gap-2"><span class="text-emerald-500">✓</span> Whitelabel ready</li>
                    <li class="flex gap-2"><span class="text-emerald-500">✓</span> 122 test suite</li>
                    <li class="flex gap-2"><span class="text-emerald-500">✓</span> 90 hari support</li>
                </ul>
                <a href="https://wa.me/62081296052010?text=Halo%2C+tertarik+paket+Growth+HotelHub+HMS"
                   target="_blank" rel="noopener"
                   class="block text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl py-3 text-sm transition-colors">
                    Pilih Growth
                </a>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-[0.1em] mb-2">Enterprise</div>
                <p class="font-display text-3xl font-bold text-slate-900 mb-1">Rp 15jt</p>
                <p class="text-xs text-slate-500 mb-4">One-time · Full Whitelabel</p>
                <ul class="text-sm text-slate-600 space-y-1.5 mb-5">
                    <li class="flex gap-2"><span class="text-emerald-500">✓</span> Semua fitur Growth</li>
                    <li class="flex gap-2"><span class="text-emerald-500">✓</span> Full whitelabel</li>
                    <li class="flex gap-2"><span class="text-emerald-500">✓</span> Kustomisasi</li>
                    <li class="flex gap-2"><span class="text-emerald-500">✓</span> Resell rights</li>
                    <li class="flex gap-2"><span class="text-emerald-500">✓</span> 180 hari support</li>
                </ul>
                <a href="https://wa.me/62081296052010?text=Halo%2C+tertarik+paket+Enterprise+HotelHub+HMS"
                   target="_blank" rel="noopener"
                   class="block text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl py-3 text-sm transition-colors">
                    Pilih Enterprise
                </a>
            </div>
        </div>
    </section>

    {{-- Related links untuk internal linking --}}
    @if ($city)
        <section class="mt-10 border-t border-slate-200 pt-8">
            <h2 class="font-display text-xl font-bold text-slate-900 mb-4">Terkait di {{ $cityName }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                <a href="/hotels-in-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Hotel di {{ $cityName }}</a>
                <a href="/aplikasi-hotel-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Aplikasi Hotel {{ $cityName }}</a>
                <a href="/software-hotel-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Software Hotel {{ $cityName }}</a>
                <a href="/hotel-murah-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Hotel murah</a>
                <a href="/sistem-hotel-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Sistem Hotel {{ $cityName }}</a>
                <a href="/beli-aplikasi-hotel-{{ $city }}" class="bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 px-3 py-2 rounded-lg text-slate-700 transition-colors">Beli Aplikasi Hotel</a>
            </div>
        </section>
    @endif
@endsection
