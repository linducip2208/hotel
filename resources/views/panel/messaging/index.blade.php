@extends('panel.layout')
@section('title', 'Guest Messaging')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Guest Messaging</h2>
        <p class="text-sm text-slate-500">
            {{ count($threads) }} threads aktif
            @if($unreadCount > 0)
            · <span class="text-rose-600 font-semibold">{{ $unreadCount }} belum dibaca</span>
            @endif
        </p>
    </div>
</div>

@if(empty($threads))
<div class="bg-white rounded-2xl p-12 border border-slate-200 shadow-sm text-center">
    <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    </div>
    <p class="text-slate-500">Belum ada pesan dari tamu</p>
</div>
@else
<div class="grid gap-3">
    @foreach($threads as $thread)
    <a href="{{ route('panel.messages.thread', $thread['id']) }}" class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm hover:shadow-md transition-all flex items-center gap-4">
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-violet-500 flex items-center justify-center text-white font-bold text-sm shrink-0">
            {{ strtoupper(substr($thread['guest']['first_name'] ?? 'G', 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
                <p class="font-semibold text-slate-800 truncate">{{ $thread['guest']['first_name'] ?? 'Guest' }} {{ $thread['guest']['last_name'] ?? '' }}</p>
                @if(($thread['unread_count'] ?? 0) > 0)
                <span class="bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $thread['unread_count'] }}</span>
                @endif
            </div>
            <p class="text-xs text-slate-400">Channel: {{ $thread['channel'] ?? 'web_chat' }}</p>
        </div>
    </a>
    @endforeach
</div>
@endif
@endsection
