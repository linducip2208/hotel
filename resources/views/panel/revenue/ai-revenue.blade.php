@extends('panel.layout')
@section('title', 'AI Revenue Agent')
@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">AI Revenue Agent</h1>
        <p class="text-sm text-gray-500 mt-0.5">Analisis harga optimal berbasis AI untuk setiap tipe kamar</p>
    </div>
    <div class="flex items-center gap-2">
        <form method="GET" class="flex items-center gap-2">
            <input type="date" name="date" value="{{ $date->toDateString() }}"
                   class="rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            <button type="submit"
                    class="inline-flex items-center gap-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold px-3.5 py-2 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Lihat
            </button>
        </form>
    </div>
</div>

<div x-data="{
    loading: false,
    result: null,
    error: null,
    batchLoading: false,
    allApplied: false,

    async analyze() {
        this.loading = true; this.error = null; this.result = null;
        try {
            const res = await fetch('{{ route('panel.revenue.ai-revenue.analyze') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ date: '{{ $date->toDateString() }}' })
            });
            const data = await res.json();
            if (data.error) { this.error = data.error; } else { this.result = data; }
        } catch (e) {
            this.error = 'Gagal menghubungi AI. Pastikan provider AI sudah dikonfigurasi.';
        }
        this.loading = false;
    },

    async batchAnalyze() {
        this.batchLoading = true;
        try {
            const res = await fetch('{{ route('panel.revenue.ai-revenue.batch') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({})
            });
            const data = await res.json();
            alert('Batch analisis selesai untuk 7 hari ke depan. ' + Object.keys(data.insights || {}).length + ' hari berhasil dianalisis.');
        } catch (e) {
            alert('Gagal batch analyze.');
        }
        this.batchLoading = false;
    },

    async applyAll() {
        if (!this.result || !this.result.recommendations) return;
        const res = await fetch('{{ route('panel.revenue.ai-revenue.apply') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ date: '{{ $date->toDateString() }}', recommendations: this.result.recommendations })
        });
        const data = await res.json();
        if (data.ok) {
            this.allApplied = true;
            alert(data.message);
        }
    },

    async applyOne(rec) {
        const res = await fetch('{{ route('panel.revenue.ai-revenue.apply') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ date: '{{ $date->toDateString() }}', recommendations: [rec] })
        });
        const data = await res.json();
        if (data.ok) alert(data.message);
    }
}">

    {{-- Action buttons --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <button @click="analyze()" :disabled="loading"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-md shadow-indigo-500/25 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
            <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span x-text="loading ? 'Menganalisis...' : 'Analisis AI'"></span>
        </button>

        <button @click="batchAnalyze()" :disabled="batchLoading"
                class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors disabled:opacity-50">
            <svg x-show="!batchLoading" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <svg x-show="batchLoading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span>Batch 7 Hari</span>
        </button>
    </div>

    {{-- Loading skeleton --}}
    <div x-show="loading" class="space-y-4">
        <div class="animate-pulse bg-white rounded-2xl p-6 shadow-card border border-gray-100">
            <div class="h-5 bg-gray-200 rounded w-1/3 mb-4"></div>
            <div class="h-4 bg-gray-100 rounded w-full mb-2"></div>
            <div class="h-4 bg-gray-100 rounded w-5/6 mb-2"></div>
            <div class="h-4 bg-gray-100 rounded w-2/3"></div>
        </div>
        <div class="animate-pulse bg-white rounded-2xl p-6 shadow-card border border-gray-100">
            <div class="h-4 bg-gray-200 rounded w-1/4 mb-3"></div>
            <div class="space-y-2">
                <div class="h-12 bg-gray-100 rounded"></div>
                <div class="h-12 bg-gray-100 rounded"></div>
                <div class="h-12 bg-gray-100 rounded"></div>
            </div>
        </div>
    </div>

    {{-- Error --}}
    <div x-show="error" x-transition class="bg-rose-50 border border-rose-200 text-rose-800 rounded-xl px-5 py-4 text-sm mb-6 flex items-start gap-3">
        <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
        <span x-text="error"></span>
    </div>

    {{-- Results --}}
    <div x-show="result" x-transition class="space-y-6">

        {{-- Strategy & Market Analysis --}}
        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-card border border-gray-100">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Strategi Keseluruhan</h2>
                <span class="inline-flex px-3 py-1.5 rounded-full text-sm font-bold"
                      :class="{
                        'bg-emerald-100 text-emerald-700': result.overall_strategy === 'aggressive',
                        'bg-indigo-100 text-indigo-700': result.overall_strategy === 'moderate',
                        'bg-amber-100 text-amber-700': result.overall_strategy === 'conservative',
                      }"
                      x-text="result.overall_strategy || '-'"></span>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-card border border-gray-100">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Analisis Pasar</h2>
                <p class="text-sm text-gray-700 leading-relaxed" x-text="result.market_analysis || '-'"></p>
            </div>
        </div>

        {{-- Recommendations table --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden" x-show="result.recommendations && result.recommendations.length > 0">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">Rekomendasi Harga</h2>
                <button @click="applyAll()" :disabled="allApplied"
                        class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3.5 py-1.5 rounded-lg transition-colors disabled:opacity-50 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span x-text="allApplied ? 'Sudah Diterapkan' : 'Terapkan Semua'"></span>
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-5 py-3">Tipe Kamar</th>
                            <th class="px-5 py-3 text-right">Harga Saat Ini</th>
                            <th class="px-5 py-3 text-right">Harga Rekomendasi</th>
                            <th class="px-5 py-3 text-center">Penyesuaian</th>
                            <th class="px-5 py-3">Alasan</th>
                            <th class="px-5 py-3 w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <template x-for="(rec, i) in result.recommendations" :key="i">
                            <tr class="hover:bg-gray-50/60 transition-colors">
                                <td class="px-5 py-3.5 font-medium text-gray-900" x-text="rec.room_type"></td>
                                <td class="px-5 py-3.5 text-right text-gray-600 font-mono text-xs"
                                    x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(rec.current_rate || 0)"></td>
                                <td class="px-5 py-3.5 text-right font-semibold font-mono text-xs"
                                    :class="(rec.adjustment_pct || 0) > 0 ? 'text-emerald-600' : ((rec.adjustment_pct || 0) < 0 ? 'text-rose-600' : 'text-gray-900')"
                                    x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(rec.suggested_rate || 0)"></td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold"
                                          :class="(rec.adjustment_pct || 0) > 0 ? 'bg-emerald-100 text-emerald-700' : ((rec.adjustment_pct || 0) < 0 ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-600')"
                                          x-text="(rec.adjustment_pct || 0) > 0 ? '+' + rec.adjustment_pct + '%' : rec.adjustment_pct + '%'"></span>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-gray-600 max-w-xs" x-text="rec.reason || '-'"></td>
                                <td class="px-5 py-3.5">
                                    <button @click="applyOne(rec)"
                                            class="inline-flex items-center gap-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 text-xs font-semibold px-2.5 py-1 rounded-lg transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        Terapkan
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Risks / Warnings --}}
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5" x-show="result.risks && result.risks.length > 0">
            <h2 class="text-sm font-semibold text-amber-800 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Risiko & Peringatan
            </h2>
            <ul class="space-y-1.5">
                <template x-for="(risk, i) in result.risks" :key="i">
                    <li class="text-sm text-amber-700 flex items-start gap-2" x-text="'• ' + risk"></li>
                </template>
            </ul>
        </div>

    </div>

    {{-- Empty state --}}
    <div x-show="!result && !loading && !error" class="bg-white rounded-2xl shadow-card border border-gray-100 p-12 text-center">
        <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">AI Revenue Agent</h3>
        <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">Klik tombol <strong>"Analisis AI"</strong> untuk menganalisis harga optimal tanggal <strong>{{ $date->translatedFormat('d F Y') }}</strong> berdasarkan data okupansi, harga kompetitor, dan tren pasar.</p>
        <p class="text-xs text-gray-400">Pastikan AI Provider sudah dikonfigurasi di menu AI Providers.</p>
    </div>

</div>

@endsection
