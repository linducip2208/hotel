@extends('panel.layout')
@section('title', 'Inbox')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Inbox</h1>
        <p class="text-sm text-gray-500 mt-0.5">Guest communications across all channels</p>
    </div>
    @php $totalUnread = $threads->sum('unread_count'); @endphp
    @if ($totalUnread > 0)
    <span class="inline-flex items-center gap-1.5 bg-primary-50 text-primary-700 text-sm font-semibold px-3 py-1.5 rounded-full">
        <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
        {{ $totalUnread }} unread
    </span>
    @endif
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    @forelse ($threads as $t)
    @php
        $hasUnread = ($t->unread_count ?? 0) > 0;
        $channelIcons = [
            'whatsapp' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
            'email'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
            'sms'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>',
        ];
        $chIcon = $channelIcons[strtolower($t->channel ?? '')] ?? $channelIcons['email'];
        $chColors = ['whatsapp' => 'emerald', 'email' => 'blue', 'sms' => 'violet'];
        $cc = $chColors[strtolower($t->channel ?? '')] ?? 'gray';
        $statusBg = $t->status === 'open' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500';
    @endphp
    <a href="{{ route('panel.comm.thread', $t->id) }}"
       class="flex items-start gap-4 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors {{ $hasUnread ? 'bg-primary-50/30' : '' }}">

        {{-- Channel icon --}}
        <div class="w-10 h-10 rounded-xl bg-{{ $cc }}-50 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-5 h-5 text-{{ $cc }}-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                {!! $chIcon !!}
            </svg>
        </div>

        {{-- Thread content --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-2 mb-0.5">
                <span class="text-sm font-{{ $hasUnread ? 'bold' : 'semibold' }} text-gray-900 truncate">
                    {{ $t->guest?->full_name ?? '(Unknown Guest)' }}
                </span>
                <span class="text-xs text-gray-400 shrink-0">{{ $t->last_message_at?->diffForHumans() }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium capitalize text-gray-500">{{ $t->channel }}</span>
                @if ($t->subject)
                <span class="text-gray-300">·</span>
                <span class="text-xs text-gray-600 truncate">{{ $t->subject }}</span>
                @endif
                <span class="text-xs font-medium {{ $statusBg }} px-2 py-0.5 rounded-full ml-auto">{{ $t->status }}</span>
                @if ($hasUnread)
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-primary-600 text-white text-xs font-bold shrink-0">
                    {{ $t->unread_count }}
                </span>
                @endif
            </div>
        </div>

        <svg class="w-4 h-4 text-gray-300 mt-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>

    </a>
    @empty
    <div class="flex flex-col items-center justify-center py-16 text-gray-400">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </div>
        <p class="text-base font-medium text-gray-500">No messages yet</p>
        <p class="text-sm text-gray-400 mt-1">Guest communications will appear here.</p>
    </div>
    @endforelse
</div>

@if ($threads->hasPages())
<div class="mt-4">{{ $threads->links() }}</div>
@endif

@endsection
