@extends('panel.layout')
@section('title', 'AI Tools')
@section('content')

@php
    $rt = function (string $name, ...$args) {
        try { return route($name, $args); } catch (\Throwable) { return '#'; }
    };
@endphp

{{-- Hero --}}
<div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-700 via-fuchsia-700 to-rose-600 p-6 lg:p-8 mb-6 shadow-xl shadow-violet-900/20">
    <div class="absolute inset-0 opacity-30 pointer-events-none"
         style="background-image:radial-gradient(circle at 25% 0%,rgba(244,63,94,.45),transparent 50%),radial-gradient(circle at 80% 100%,rgba(139,92,246,.5),transparent 50%);"></div>
    <div class="relative flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div>
            <p class="text-violet-200/80 text-xs uppercase tracking-[0.2em] font-semibold mb-1 flex items-center gap-2">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                BYOK · Bring Your Own Key
            </p>
            <h1 class="text-2xl lg:text-3xl font-bold text-white tracking-tight">AI Tools</h1>
            <p class="text-violet-100/80 text-sm mt-1">{{ count($tools) }} fitur AI · Pakai provider apapun (DeepSeek, Gemini, Anthropic, Self-hosted)</p>
        </div>
        <a href="{{ $rt('panel.settings.integrations') }}"
           class="inline-flex items-center gap-2 bg-white text-violet-700 hover:bg-slate-100 text-sm font-semibold px-4 py-2.5 rounded-xl shadow-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Konfigurasi Provider
        </a>
    </div>
</div>

{{-- Provider status --}}
<div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-sm font-bold text-slate-800">AI Provider Aktif</h2>
            <p class="text-xs text-slate-500 mt-0.5">Provider yang bisa dipanggil dari semua tool AI</p>
        </div>
        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $aiProviders->where('is_active',true)->count() > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
            {{ $aiProviders->where('is_active',true)->count() }} active
        </span>
    </div>
    @if($aiProviders->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($aiProviders as $p)
                <div class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $p->name }}</p>
                        <p class="text-[11px] text-slate-500">{{ $p->api_format }} · {{ $p->default_model ?? 'model not set' }}</p>
                    </div>
                    @if($p->is_active)
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8">
            <p class="text-sm text-slate-500 mb-3">Belum ada AI provider yang dikonfigurasi.</p>
            <a href="{{ $rt('panel.settings.integrations') }}" class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold px-4 py-2 rounded-xl">
                Tambah Provider Pertama
            </a>
        </div>
    @endif
</div>

{{-- AI Tools Grid --}}
<div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="text-base font-bold text-slate-900">Semua AI Tools</h2>
        <p class="text-xs text-slate-500 mt-0.5">{{ count($tools) }} fitur · BYOK ke provider mana saja</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 p-6">
        @foreach($tools as $t)
            @php
                $url = match($t['key']) {
                    'concierge' => $rt('panel.ai.concierge'),
                    'translate' => $rt('panel.ai.translate'),
                    'forecast' => $rt('panel.ai.forecast'),
                    'review-reply' => $rt('panel.ai.review-replies'),
                    'sentiment', 'pseo-content', 'ocr' => '#',
                    default => '#',
                };
                $available = $url !== '#';
            @endphp
            <a href="{{ $url }}"
               class="group flex flex-col gap-3 p-5 rounded-2xl border border-slate-200 hover:border-{{ $t['color'] }}-300 hover:shadow-md hover:-translate-y-0.5 transition-all {{ !$available ? 'opacity-60' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-{{ $t['color'] }}-400 to-{{ $t['color'] }}-600 flex items-center justify-center shadow-md shadow-{{ $t['color'] }}-500/30">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['icon'] }}"/></svg>
                    </div>
                    @if($available)
                        <span class="text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">Ready</span>
                    @else
                        <span class="text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">API only</span>
                    @endif
                </div>
                <div>
                    <p class="text-base font-bold text-slate-900 mb-1">{{ $t['label'] }}</p>
                    <p class="text-xs text-slate-500 leading-relaxed">{{ $t['desc'] }}</p>
                </div>
                @if($available)
                    <div class="text-xs font-semibold text-{{ $t['color'] }}-600 group-hover:text-{{ $t['color'] }}-700 flex items-center gap-1 mt-1">
                        Buka tool
                        <svg class="w-3 h-3 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                @endif
            </a>
        @endforeach
    </div>
</div>

{{-- API Endpoints --}}
<div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden mt-6">
    <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="text-base font-bold text-slate-900">REST API Endpoints</h2>
        <p class="text-xs text-slate-500 mt-0.5">Semua AI tool juga bisa diakses lewat API (untuk integrasi luar)</p>
    </div>
    <div class="px-6 py-4 space-y-2 text-xs font-mono">
        <div class="flex items-center gap-3"><span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">POST</span><span class="text-slate-700">/api/v1/ai/concierge</span><span class="text-slate-400 font-sans">— chatbot tamu</span></div>
        <div class="flex items-center gap-3"><span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">POST</span><span class="text-slate-700">/api/v1/ai/translate</span><span class="text-slate-400 font-sans">— terjemahan</span></div>
        <div class="flex items-center gap-3"><span class="font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">GET&nbsp;</span><span class="text-slate-700">/api/v1/ai/demand-forecast</span><span class="text-slate-400 font-sans">— prediksi permintaan</span></div>
        <div class="flex items-center gap-3"><span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">POST</span><span class="text-slate-700">/api/v1/ai/reviews/{id}/reply</span><span class="text-slate-400 font-sans">— generate review reply</span></div>
        <div class="flex items-center gap-3"><span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">POST</span><span class="text-slate-700">/api/v1/ai/chatbot</span><span class="text-slate-400 font-sans">— internal staff assistant</span></div>
    </div>
</div>

@endsection
