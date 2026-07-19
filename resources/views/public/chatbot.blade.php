@extends('public.layout')
@section('title', 'Hotel Concierge Chat')
@section('content')

<div x-data="chatbot()" x-init="init()" class="fixed bottom-6 right-6 z-50 font-sans">
    {{-- Chat toggle button --}}
    <button @click="open = !open" :class="open ? 'scale-0' : 'scale-100'"
            class="w-14 h-14 rounded-full bg-primary-600 hover:bg-primary-700 text-white shadow-lg flex items-center justify-center transition-all duration-200">
        <svg x-show="!hasUnread" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <span x-show="hasUnread" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-white text-[10px] flex items-center justify-center font-bold" x-text="unreadCount"></span>
    </button>

    {{-- Chat window --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:leave="transition ease-in duration-150" x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="absolute bottom-0 right-0 w-96 h-[560px] max-h-[calc(100vh-100px)] bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-5 py-4 flex items-center gap-3 shrink-0">
            <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-white text-sm font-semibold">Hotel Concierge</p>
                <p class="text-white/70 text-xs">
                    <span x-show="typing">Typing...</span>
                    <span x-show="!typing">Online</span>
                </p>
            </div>
            <button @click="open = !open" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-gray-50/50" x-ref="messages" id="chatMessages">
            <template x-for="msg in messages" :key="msg.id">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex gap-2'">
                    <div x-show="msg.role === 'assistant'" class="w-7 h-7 rounded-full bg-primary-100 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8"/></svg>
                    </div>
                    <div :class="msg.role === 'user' ? 'bg-primary-600 text-white rounded-2xl rounded-br-md px-4 py-2.5 max-w-[80%] text-sm' : 'bg-white border border-gray-100 rounded-2xl rounded-bl-md px-4 py-2.5 max-w-[80%] text-sm text-gray-800 shadow-sm'"
                         x-text="msg.content">
                    </div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="typing" class="flex gap-2">
                <div class="w-7 h-7 rounded-full bg-primary-100 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8"/></svg>
                </div>
                <div class="bg-white border border-gray-100 rounded-2xl rounded-bl-md px-4 py-3 shadow-sm">
                    <span class="flex gap-1">
                        <span class="w-1.5 h-1.5 bg-gray-300 rounded-full animate-bounce"></span>
                        <span class="w-1.5 h-1.5 bg-gray-300 rounded-full animate-bounce" style="animation-delay:0.1s"></span>
                        <span class="w-1.5 h-1.5 bg-gray-300 rounded-full animate-bounce" style="animation-delay:0.2s"></span>
                    </span>
                </div>
            </div>

            {{-- Quick reply buttons --}}
            <div x-show="suggestions.length > 0" class="flex flex-wrap gap-2 pt-1">
                <template x-for="s in suggestions" :key="s">
                    <button @click="sendMessage(s)" class="text-xs font-medium bg-white border border-gray-200 hover:border-primary-300 hover:text-primary-600 text-gray-600 px-3 py-1.5 rounded-full transition-colors" x-text="s"></button>
                </template>
            </div>
        </div>

        {{-- Input --}}
        <div class="border-t border-gray-100 px-4 py-3 flex items-center gap-2 bg-white shrink-0">
            <input type="text" x-model="input" @keydown.enter.prevent="sendMessage()"
                   placeholder="Type your message..."
                   class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            <button @click="sendMessage()" :disabled="!input.trim() || loading"
                    class="w-10 h-10 rounded-xl bg-primary-600 hover:bg-primary-700 disabled:opacity-40 text-white flex items-center justify-center shrink-0 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </button>
        </div>
    </div>
</div>

<script>
function chatbot() {
    return {
        open: false,
        input: '',
        messages: [
            { id: 1, role: 'assistant', content: 'Welcome! I\'m your hotel concierge. How can I help you today?' }
        ],
        suggestions: ['Room Availability', 'Spa & Wellness', 'Restaurant', 'Check-in Time', 'Hotel Facilities'],
        typing: false,
        loading: false,
        hasUnread: false,
        unreadCount: 0,
        history: [],

        init() {
            this.scrollToBottom();
        },

        async sendMessage(text) {
            const content = (text || this.input).trim();
            if (!content) return;

            this.messages.push({ id: Date.now(), role: 'user', content });
            this.input = '';
            this.suggestions = [];
            this.scrollToBottom();

            this.loading = true;
            this.typing = true;

            try {
                const res = await fetch('/api/v1/ai/chatbot', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ message: content, history: this.history.slice(-10), locale: 'en' }),
                });

                const data = await res.json();

                this.messages.push({
                    id: Date.now() + 1,
                    role: 'assistant',
                    content: data.reply || data.message || 'I\'m sorry, I couldn\'t process that.',
                });

                this.history.push({ role: 'user', content });
                this.history.push({ role: 'assistant', content: data.reply || data.message });

                if (data.suggestions?.length) {
                    this.suggestions = data.suggestions;
                } else {
                    this.suggestions = ['Room Availability', 'Spa & Wellness', 'Restaurant', 'Check-in Time'];
                }

                if (data.actions?.length) {
                    for (const action of data.actions) {
                        if (action === 'show_rooms') {
                            this.suggestions = ['Show Room List', ...this.suggestions];
                        }
                    }
                }
            } catch (e) {
                this.messages.push({
                    id: Date.now() + 1,
                    role: 'assistant',
                    content: 'Sorry, I\'m having trouble connecting. Please try again later.',
                });
            }

            this.typing = false;
            this.loading = false;
            this.scrollToBottom();

            if (!this.open) {
                this.hasUnread = true;
                this.unreadCount++;
            }
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.messages;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },
    };
}
</script>

<style>
@keyframes bounce {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-4px); }
}
</style>

@endsection
