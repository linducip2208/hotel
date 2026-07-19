@extends('panel.layout')
@section('title', 'AI Concierge')
@section('content')

<div class="max-w-3xl mx-auto" x-data="conciergeChat()">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-br from-indigo-50 to-violet-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-900">AI Concierge — Tester</h1>
                    <p class="text-xs text-slate-500">Multi-bahasa chatbot untuk tamu. Pakai tool ini untuk preview pengalaman tamu.</p>
                </div>
            </div>
        </div>

        <div class="h-[480px] overflow-y-auto p-6 space-y-3" x-ref="chatLog">
            <template x-for="(m, i) in messages" :key="i">
                <div :class="m.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="m.role === 'user' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-800'"
                         class="max-w-[80%] rounded-2xl px-4 py-2.5 text-sm leading-relaxed whitespace-pre-wrap"
                         x-text="m.content"></div>
                </div>
            </template>
            <div x-show="loading" class="flex justify-start">
                <div class="bg-slate-100 rounded-2xl px-4 py-2.5 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-slate-400 animate-bounce" style="animation-delay:0s"></span>
                    <span class="w-2 h-2 rounded-full bg-slate-400 animate-bounce" style="animation-delay:.15s"></span>
                    <span class="w-2 h-2 rounded-full bg-slate-400 animate-bounce" style="animation-delay:.3s"></span>
                </div>
            </div>
        </div>

        <form @submit.prevent="send()" class="border-t border-slate-100 p-4">
            <div class="flex items-center gap-2">
                <select x-model="locale" class="text-xs border-slate-200 rounded-lg bg-slate-50 py-1.5 pr-7 pl-2 focus:ring-1 focus:ring-indigo-500">
                    <option value="id">🇮🇩 ID</option>
                    <option value="en">🇬🇧 EN</option>
                    <option value="zh">🇨🇳 ZH</option>
                    <option value="ja">🇯🇵 JA</option>
                    <option value="ko">🇰🇷 KO</option>
                </select>
                <input type="text" x-model="input" :disabled="loading" required
                       placeholder="Tanya: 'Bisa minta sarapan jam berapa?' atau 'Restoran terdekat?'"
                       class="flex-1 text-sm border-slate-200 rounded-lg focus:ring-1 focus:ring-indigo-500">
                <button type="submit" :disabled="loading || !input.trim()"
                        class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                    Kirim
                </button>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Note: Provider AI harus dikonfigurasi di Settings → Integrations dulu.</p>
        </form>
    </div>
</div>

<script>
function conciergeChat() {
    return {
        messages: [],
        input: '',
        locale: 'id',
        loading: false,
        async send() {
            const msg = this.input.trim();
            if (!msg || this.loading) return;
            this.messages.push({ role: 'user', content: msg });
            this.input = '';
            this.loading = true;
            this.$nextTick(() => this.$refs.chatLog.scrollTop = this.$refs.chatLog.scrollHeight);
            try {
                const res = await fetch('/api/v1/ai/concierge', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                    body: JSON.stringify({
                        message: msg, locale: this.locale,
                        history: this.messages.slice(0, -1).map(m => ({ role: m.role, content: m.content })),
                    }),
                });
                const data = await res.json();
                const reply = data.reply || data.message || data.error || 'Tidak ada balasan dari provider.';
                this.messages.push({ role: 'assistant', content: reply });
            } catch (e) {
                this.messages.push({ role: 'assistant', content: '[Error: ' + e.message + ']' });
            }
            this.loading = false;
            this.$nextTick(() => this.$refs.chatLog.scrollTop = this.$refs.chatLog.scrollHeight);
        }
    };
}
</script>

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@endsection
