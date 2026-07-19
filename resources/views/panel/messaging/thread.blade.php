@extends('panel.layout')
@section('title', 'Chat — ' . ($thread->guest->first_name ?? 'Guest'))
@section('content')
<div class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('panel.messages.index') }}" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h2 class="text-lg font-bold text-slate-800">{{ $thread->guest->first_name ?? 'Guest' }} {{ $thread->guest->last_name ?? '' }}</h2>
            <p class="text-xs text-slate-500">{{ $thread->guest->phone ?? '' }} {{ $thread->guest->email ?? '' }}</p>
        </div>
    </div>
    <form method="POST" action="{{ route('panel.messages.close', $thread->id) }}">
        @csrf
        <button class="text-xs text-rose-600 hover:underline">Close Thread</button>
    </form>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
    <div id="chat-messages" class="p-4 space-y-3 max-h-[500px] overflow-y-auto">
        @foreach($messages as $msg)
        <div class="flex {{ $msg['direction'] === 'outbound' ? 'justify-end' : 'justify-start' }}">
            <div class="max-w-[75%] px-4 py-2.5 rounded-2xl {{ $msg['direction'] === 'outbound' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-800' }} text-sm">
                {{ $msg['body'] }}
                <div class="text-[10px] mt-1 {{ $msg['direction'] === 'outbound' ? 'text-indigo-200' : 'text-slate-400' }}">
                    {{ \Carbon\Carbon::parse($msg['created_at'])->format('H:i') }}
                    @if($msg['dir'] !== 'outbound' && ($msg['is_read'] ?? false))
                    <span>Read</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="border-t border-slate-100 p-4">
        @if(!empty($quickReplies))
        <div class="flex flex-wrap gap-2 mb-3">
            @foreach($quickReplies as $qr)
            <button onclick="document.getElementById('msg-body').value='{{ $qr['reply_text'] }}'" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-1.5 rounded-full transition-colors">{{ $qr['label'] }}</button>
            @endforeach
        </div>
        @endif
        <form method="POST" action="{{ route('panel.messages.send', $thread->id) }}" class="flex gap-2">
            @csrf
            <input id="msg-body" type="text" name="body" required placeholder="Ketik pesan..." class="flex-1 border-slate-300 rounded-xl text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-500">
            <button class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let lastId = {{ collect($messages)->last()['id'] ?? 0 }};
    setInterval(function() {
        fetch('{{ route('panel.messages.poll', $thread->id) }}?after_id=' + lastId)
            .then(r => r.json())
            .then(msgs => {
                if (msgs.length) {
                    const container = document.getElementById('chat-messages');
                    msgs.forEach(m => {
                        const div = document.createElement('div');
                        div.className = 'flex ' + (m.direction === 'outbound' ? 'justify-end' : 'justify-start');
                        div.innerHTML = '<div class="max-w-[75%] px-4 py-2.5 rounded-2xl ' + (m.direction === 'outbound' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-800') + ' text-sm">' + m.body + '<div class="text-[10px] mt-1 ' + (m.direction === 'outbound' ? 'text-indigo-200' : 'text-slate-400') + '">Now</div></div>';
                        container.appendChild(div);
                    });
                    lastId = msgs[msgs.length-1].id;
                    container.scrollTop = container.scrollHeight;
                }
            });
    }, 5000);
});
</script>
@endsection
