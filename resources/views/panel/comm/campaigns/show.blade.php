@extends('panel.layout')
@section('title', $campaign->name)
@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <a href="{{ route('panel.comm.campaigns') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $campaign->name }}</h1>
        </div>
        <div class="flex items-center gap-3 mt-1">
            @php $sc = ['sent' => 'emerald', 'scheduled' => 'blue', 'draft' => 'gray', 'sending' => 'amber', 'paused' => 'orange', 'failed' => 'red'][$campaign->status] ?? 'gray'; @endphp
            <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">{{ $campaign->status }}</span>
            <span class="text-xs text-gray-400">{{ $campaign->created_at->diffForHumans() }}</span>
        </div>
    </div>
    <div class="flex items-center gap-2">
        @if(in_array($campaign->status, ['draft','scheduled']))
        <form method="POST" action="{{ route('panel.comm.campaigns.send', $campaign->id) }}">
            @csrf
            <button class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">Send Now</button>
        </form>
        @endif
        @if($campaign->status === 'sending')
        <form method="POST" action="{{ route('panel.comm.campaigns.pause', $campaign->id) }}">
            @csrf
            <button class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">Pause</button>
        </form>
        @endif
    </div>
</div>

<div class="grid md:grid-cols-4 gap-4 mb-6">
    @php
    $progress = $campaign->recipients_count > 0 ? round(($campaign->sent_count / $campaign->recipients_count) * 100) : 0;
    @endphp
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Recipients</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $campaign->recipients_count }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Sent</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $campaign->sent_count }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Progress</p>
        <div class="flex items-center gap-3 mt-1.5">
            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full bg-primary-500 rounded-full transition-all" style="width:{{ $progress }}%"></div>
            </div>
            <span class="text-sm font-bold text-gray-900">{{ $progress }}%</span>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Channel</p>
        <p class="text-lg font-bold text-gray-900 mt-1 capitalize">{{ $campaign->channel }}</p>
    </div>
</div>

<div class="grid md:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Campaign Details</h2>
        </div>
        <div class="px-5 py-4 space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Audience</span><span class="font-medium text-gray-800">{{ str_replace('_', ' ', $campaign->audience_filter['type'] ?? 'all') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Template</span><span class="font-medium text-gray-800">{{ $campaign->template?->name ?? 'None' }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Scheduled</span><span class="font-medium text-gray-800">{{ $campaign->scheduled_at?->format('d M Y H:i') ?? 'Immediate' }}</span></div>
            @if($campaign->subject)
            <div><span class="text-gray-500 block mb-1">Subject</span><span class="font-medium text-gray-800">{{ $campaign->subject }}</span></div>
            @endif
            @if($campaign->body)
            <div><span class="text-gray-500 block mb-1">Body</span><p class="text-gray-700 bg-gray-50 rounded-lg p-3 whitespace-pre-wrap">{{ \Illuminate\Support\Str::limit($campaign->body, 500) }}</p></div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Engagement (Approximate)</h2>
        </div>
        <div class="px-5 py-4">
            <div class="space-y-4" x-data="analytics({{ $campaign->id }})" x-init="fetch()">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Opened</span>
                    <span class="font-semibold" x-text="stats.opened"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Clicked</span>
                    <span class="font-semibold" x-text="stats.clicked"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Bookings</span>
                    <span class="font-semibold" x-text="stats.booked"></span>
                </div>
                <div class="flex justify-between text-sm border-t pt-3">
                    <span class="text-gray-500">Open Rate</span>
                    <span class="font-semibold" x-text="stats.sent > 0 ? ((stats.opened/stats.sent)*100).toFixed(1)+'%' : '—'"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Click Rate</span>
                    <span class="font-semibold" x-text="stats.sent > 0 ? ((stats.clicked/stats.sent)*100).toFixed(1)+'%' : '—'"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function analytics(campaignId) {
    return {
        stats: { total: 0, sent: 0, opened: 0, clicked: 0, booked: 0 },
        async fetch() {
            try {
                const res = await fetch('/panel/campaigns/' + campaignId + '/analytics');
                const data = await res.json();
                this.stats = data.stats;
            } catch(e) {}
        }
    };
}
</script>

@endsection
