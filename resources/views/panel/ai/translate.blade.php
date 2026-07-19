@extends('panel.layout')
@section('title', 'AI Translate')
@section('content')

<div class="max-w-4xl mx-auto" x-data="translateTool()">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-br from-sky-50 to-blue-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-900">AI Translate</h1>
                    <p class="text-xs text-slate-500">Terjemahkan deskripsi kamar, email, atau konten apapun ke bahasa lain.</p>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-0">
            <div class="p-6 border-r border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Sumber</label>
                    <select x-model="from" class="text-xs border-slate-200 rounded bg-slate-50 py-1 pr-7 pl-2">
                        <option value="">Auto-detect</option>
                        <option value="id">🇮🇩 Indonesia</option><option value="en">🇬🇧 English</option>
                        <option value="zh">🇨🇳 中文</option><option value="ja">🇯🇵 日本語</option>
                        <option value="ko">🇰🇷 한국어</option><option value="ar">🇸🇦 العربية</option>
                    </select>
                </div>
                <textarea x-model="text" rows="14" placeholder="Tempel teks di sini…"
                          class="w-full text-sm border-slate-200 rounded-lg focus:ring-1 focus:ring-sky-500 resize-none"></textarea>
            </div>
            <div class="p-6 bg-slate-50/50">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Target</label>
                    <select x-model="to" class="text-xs border-slate-200 rounded bg-slate-50 py-1 pr-7 pl-2">
                        <option value="en">🇬🇧 English</option><option value="id">🇮🇩 Indonesia</option>
                        <option value="zh">🇨🇳 中文</option><option value="ja">🇯🇵 日本語</option>
                        <option value="ko">🇰🇷 한국어</option><option value="ar">🇸🇦 العربية</option>
                        <option value="es">🇪🇸 Español</option><option value="fr">🇫🇷 Français</option>
                    </select>
                </div>
                <div class="w-full h-[336px] text-sm border border-slate-200 rounded-lg p-3 bg-white whitespace-pre-wrap overflow-y-auto" x-text="result || (loading ? 'Menerjemahkan…' : '(belum diterjemahkan)')"></div>
            </div>
        </div>

        <div class="border-t border-slate-100 p-4 flex items-center justify-between">
            <p class="text-[11px] text-slate-400">Provider: di-set di Settings → Integrations (kategori: AI)</p>
            <button @click="translate()" :disabled="loading || !text.trim()"
                    class="bg-sky-600 hover:bg-sky-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white px-5 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                <span x-text="loading ? 'Memproses…' : 'Terjemahkan'"></span>
            </button>
        </div>
    </div>
</div>

<script>
function translateTool() {
    return {
        text: '', result: '', from: '', to: 'en', loading: false,
        async translate() {
            this.loading = true;
            this.result = '';
            try {
                const body = { text: this.text, to: this.to };
                if (this.from) body.from = this.from;
                const res = await fetch('/api/v1/ai/translate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                    body: JSON.stringify(body),
                });
                const data = await res.json();
                this.result = data.translation || data.text || data.error || JSON.stringify(data);
            } catch (e) {
                this.result = '[Error: ' + e.message + ']';
            }
            this.loading = false;
        }
    };
}
</script>

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@endsection
