@extends('panel.layout')
@section('title', 'Marketing Campaigns')
@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Campaigns</h1>
        <p class="text-sm text-gray-500 mt-0.5">Bulk email & WhatsApp broadcast campaigns</p>
    </div>
    <a href="{{ route('panel.comm.campaigns.create') }}"
       class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors self-start">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Campaign
    </a>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Campaign</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Channel</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Audience</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Scheduled</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Progress</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($campaigns as $c)
                @php
                    $channelColors = ['email' => 'blue', 'whatsapp' => 'emerald', 'both' => 'violet'];
                    $statusColors = ['sent' => 'emerald', 'scheduled' => 'blue', 'draft' => 'gray', 'sending' => 'amber', 'paused' => 'orange', 'failed' => 'red'];
                    $cc = $channelColors[$c->channel] ?? 'gray';
                    $sc = $statusColors[$c->status] ?? 'gray';
                    $progress = $c->recipients_count > 0 ? round(($c->sent_count / $c->recipients_count) * 100) : 0;
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <a href="{{ route('panel.comm.campaigns.show', $c->id) }}" class="text-sm font-medium text-gray-800 hover:text-primary-600">{{ $c->name }}</a>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-{{ $cc }}-50 text-{{ $cc }}-700 px-2 py-0.5 rounded-full capitalize">{{ $c->channel }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">
                        {{ str_replace('_', ' ', $c->audience_filter['type'] ?? 'all') }}
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">
                        {{ $c->scheduled_at?->format('d M Y H:i') ?? '—' }}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">{{ $c->status }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        @if($c->recipients_count > 0)
                        <div class="flex items-center gap-2 justify-end">
                            <div class="w-16 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-500 rounded-full transition-all" style="width:{{ $progress }}%"></div>
                            </div>
                            <span class="text-xs tabular-nums text-gray-500">{{ $c->sent_count }}/{{ $c->recipients_count }}</span>
                        </div>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            @if(in_array($c->status, ['draft','scheduled']))
                            <form method="POST" action="{{ route('panel.comm.campaigns.send', $c->id) }}" class="inline">
                                @csrf
                                <button class="text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 px-2 py-1 rounded-lg transition-colors">Send</button>
                            </form>
                            @endif
                            @if($c->status === 'sending')
                            <form method="POST" action="{{ route('panel.comm.campaigns.pause', $c->id) }}" class="inline">
                                @csrf
                                <button class="text-xs font-medium bg-amber-50 text-amber-700 hover:bg-amber-100 px-2 py-1 rounded-lg transition-colors">Pause</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 rounded-2xl bg-violet-50 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-violet-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            </div>
                            <p class="text-sm font-medium text-gray-600">No campaigns yet</p>
                            <p class="text-xs text-gray-400 mt-1">Create your first broadcast campaign</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($campaigns->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $campaigns->links() }}</div>
    @endif
</div>

@endsection
