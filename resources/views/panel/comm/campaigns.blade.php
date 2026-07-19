@extends('panel.layout')
@section('title', 'Marketing Campaigns')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Marketing Campaigns</h1>
    <p class="text-sm text-gray-500 mt-0.5">Bulk email, WhatsApp, and SMS outreach campaigns</p>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Campaigns list --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Campaign</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Channel</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Scheduled</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Sent</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($campaigns as $c)
                        @php
                            $channelColors = ['email' => 'blue', 'whatsapp' => 'emerald', 'sms' => 'violet'];
                            $statusColors = ['sent' => 'emerald', 'scheduled' => 'blue', 'draft' => 'gray', 'sending' => 'amber', 'failed' => 'red'];
                            $cc = $channelColors[$c->channel] ?? 'gray';
                            $sc = $statusColors[$c->status] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5 text-sm font-medium text-gray-800">{{ $c->name }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-xs font-medium bg-{{ $cc }}-50 text-{{ $cc }}-700 px-2 py-0.5 rounded-full capitalize">{{ $c->channel }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-sm text-gray-600">
                                {{ $c->scheduled_at?->format('d M Y H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">{{ $c->status }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-right text-sm tabular-nums">
                                <span class="text-gray-800 font-semibold">{{ $c->sent_count }}</span><span class="text-gray-400">/{{ $c->recipients_count }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-sm text-gray-400">No campaigns yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- New campaign form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">New Campaign</h2>
        </div>
        <form method="POST" action="{{ route('panel.comm.campaigns.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Campaign Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Holiday Promo 2025"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Channel <span class="text-red-500">*</span></label>
                <select name="channel" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="email">Email</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="sms">SMS</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Template</label>
                <select name="template_id"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="">— select template —</option>
                    @foreach ($templates as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Schedule At</label>
                <input type="datetime-local" name="scheduled_at"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                <p class="text-xs text-gray-400 mt-1">Leave blank to schedule manually later</p>
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Create Campaign
            </button>
        </form>
    </div>

</div>

@endsection
