@extends('panel.layout')
@section('title', 'Upsell Pre-arrival Campaigns')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Upsell Pre-arrival Campaigns</h2>
        <p class="text-sm text-slate-500">Auto-tawarkan upgrade 3 hari sebelum check-in via WA/Email</p>
    </div>
    <a href="{{ route('panel.upsell.campaigns.create') }}" class="bg-rose-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-rose-700 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Buat Campaign
    </a>
</div>

@if($campaigns->isEmpty())
<div class="bg-white rounded-2xl p-12 border border-slate-200 shadow-sm text-center text-slate-400">
    <p>Belum ada campaign. Buat campaign pertama untuk auto-upsell tamu.</p>
</div>
@else
<div class="grid gap-4">
    @foreach($campaigns as $campaign)
    <a href="{{ route('panel.upsell.campaigns.show', $campaign->id) }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition-all">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-{{ $campaign->status === 'active' ? 'emerald' : ($campaign->status === 'paused' ? 'amber' : 'slate') }}-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-{{ $campaign->status === 'active' ? 'emerald' : ($campaign->status === 'paused' ? 'amber' : 'slate') }}-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-slate-800">{{ $campaign->name }}</p>
                    <p class="text-xs text-slate-500">{{ $campaign->days_before_arrival }} hari sebelum check-in · {{ $campaign->channel }}</p>
                </div>
            </div>
            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-{{ $campaign->status === 'active' ? 'emerald' : ($campaign->status === 'paused' ? 'amber' : 'slate') }}-50 text-{{ $campaign->status === 'active' ? 'emerald' : ($campaign->status === 'paused' ? 'amber' : 'slate') }}-700">{{ ucfirst($campaign->status) }}</span>
        </div>
        <div class="grid grid-cols-3 gap-3 text-xs text-slate-500">
            <div><span class="font-bold text-slate-700">{{ $campaign->sent_count }}</span> terkirim</div>
            <div><span class="font-bold text-emerald-600">{{ $campaign->accepted_count }}</span> diterima</div>
            <div><span class="font-bold text-indigo-600">Rp {{ number_format($campaign->revenue_generated, 0, ',', '.') }}</span> revenue</div>
        </div>
    </a>
    @endforeach
</div>
@endif
@endsection
