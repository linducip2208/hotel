@extends('panel.layout')
@section('title', 'AI Review Replies')
@section('content')

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-br from-amber-50 to-rose-50 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-rose-500 flex items-center justify-center shadow-md">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <h1 class="text-base font-bold text-slate-900">AI Review Reply Generator</h1>
            <p class="text-xs text-slate-500">Generate balasan untuk review tamu (multi-bahasa, configurable tone).</p>
        </div>
    </div>

    @if($reviews->isEmpty())
        <div class="text-center py-16">
            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <p class="text-sm text-slate-500">Belum ada review yang menunggu balasan.</p>
            <p class="text-xs text-slate-400 mt-1">Review akan masuk otomatis dari OTA channel sync atau guest portal.</p>
        </div>
    @else
        <div class="divide-y divide-slate-100">
            @foreach($reviews as $review)
                <div class="p-5 hover:bg-slate-50 transition-colors" x-data="{ generating: false, reply: '' }">
                    <div class="flex items-start gap-3 mb-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-amber-400 to-rose-500 text-white flex items-center justify-center font-bold text-xs shrink-0">
                            {{ strtoupper(substr($review->guest_name ?? 'G', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-sm font-semibold text-slate-800">{{ $review->guest_name ?? 'Guest' }}</p>
                                <span class="text-[10px] uppercase font-mono text-slate-400">{{ $review->source ?? 'direct' }}</span>
                                <div class="flex items-center gap-0.5">
                                    @for($i = 0; $i < ($review->rating ?? 0); $i++)
                                        <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-sm text-slate-700 leading-relaxed">{{ $review->content ?? $review->review_text ?? '' }}</p>
                        </div>
                    </div>

                    <div class="ml-12 pt-3 border-t border-slate-100">
                        <div class="flex items-center gap-2 mb-2">
                            <button @click="generating = true; fetch('/api/v1/ai/reviews/{{ $review->id }}/reply', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' }, body: JSON.stringify({ tone: 'friendly_professional', locale: 'id' }) }).then(r=>r.json()).then(d=>{ reply = d.reply || d.error || JSON.stringify(d); generating = false }).catch(e=>{ reply = e.message; generating = false })"
                                    :disabled="generating"
                                    class="inline-flex items-center gap-1.5 bg-violet-600 hover:bg-violet-700 disabled:bg-slate-300 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span x-text="generating ? 'Generating…' : 'Generate Reply'"></span>
                            </button>
                        </div>
                        <textarea x-show="reply" x-model="reply" rows="3" class="w-full text-sm border-slate-200 rounded-lg" placeholder="AI-generated reply akan muncul di sini…"></textarea>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@endsection
