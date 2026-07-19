@extends('panel.layout')
@section('title', 'Message Thread')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.comm.inbox') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div class="flex-1 min-w-0">
        <h1 class="text-xl font-bold text-gray-900 truncate">{{ $thread->subject }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            <span class="capitalize font-medium">{{ $thread->channel }}</span>
            @if ($thread->guest?->full_name) · {{ $thread->guest->full_name }} @endif
        </p>
    </div>
</div>

<div class="max-w-2xl space-y-5">

    {{-- Message thread --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 space-y-4">
        @forelse ($thread->messages as $m)
        <div class="{{ $m->direction === 'outbound' ? 'flex flex-col items-end' : 'flex flex-col items-start' }}">
            <div class="max-w-md px-4 py-3 rounded-2xl text-sm {{ $m->direction === 'outbound' ? 'bg-primary-600 text-white rounded-br-sm' : 'bg-gray-100 text-gray-800 rounded-bl-sm' }}">
                {{ $m->body }}
            </div>
            <div class="text-xs text-gray-400 mt-1.5 flex items-center gap-2 {{ $m->direction === 'outbound' ? 'flex-row-reverse' : '' }}">
                <span>{{ $m->created_at->diffForHumans() }}</span>
                <span>·</span>
                @if ($m->status === 'delivered' || $m->status === 'read')
                <span class="text-emerald-500 capitalize">{{ $m->status }}</span>
                @else
                <span class="capitalize">{{ $m->status }}</span>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center text-sm text-gray-400 py-8">No messages in this thread.</div>
        @endforelse
    </div>

    {{-- Reply form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-3.5">
            <h2 class="text-sm font-semibold text-gray-700">Reply</h2>
        </div>
        <form method="POST" action="{{ route('panel.comm.reply', $thread->id) }}" class="p-5">
            @csrf
            <textarea name="body" required rows="3" placeholder="Type your reply…"
                      class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none mb-3"></textarea>
            <button type="submit"
                    class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                Send Reply
            </button>
        </form>
    </div>

</div>

@endsection
