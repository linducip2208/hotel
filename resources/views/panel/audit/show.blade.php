@extends('panel.layout')
@section('title', 'Audit Entry')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.audit.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Audit Entry #{{ $log->id }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ $log->created_at->format('d M Y H:i:s') }} ·
            <code class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md font-mono">{{ $log->action }}</code>
        </p>
    </div>
</div>

<div class="grid md:grid-cols-2 gap-5">

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
            <h2 class="text-sm font-semibold text-gray-700">Before</h2>
        </div>
        <div class="p-5">
            <pre class="text-xs text-gray-700 bg-gray-50 rounded-xl p-4 overflow-x-auto font-mono leading-relaxed">{{ json_encode($log->before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-emerald-400"></div>
            <h2 class="text-sm font-semibold text-gray-700">After</h2>
        </div>
        <div class="p-5">
            <pre class="text-xs text-gray-700 bg-gray-50 rounded-xl p-4 overflow-x-auto font-mono leading-relaxed">{{ json_encode($log->after, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden md:col-span-2">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-blue-400"></div>
            <h2 class="text-sm font-semibold text-gray-700">Metadata</h2>
        </div>
        <div class="p-5">
            <pre class="text-xs text-gray-700 bg-gray-50 rounded-xl p-4 overflow-x-auto font-mono leading-relaxed">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>

</div>

@endsection
