@extends('panel.layout')
@section('title', 'Channel Manager')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Channel Manager</h1>
        <p class="text-sm text-gray-500 mt-0.5">OTA connections, ARI sync, and booking ingest</p>
    </div>
    <a href="{{ route('panel.channel.sync-log') }}"
       class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 px-3.5 py-2 rounded-xl hover:bg-gray-50 shadow-card transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Sync Log
    </a>
</div>

{{-- Quick nav --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    @foreach ([
        ['label' => 'Rate Mapping', 'route' => 'panel.channel.mapping', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4"/>', 'color' => 'primary'],
        ['label' => 'Rates & ARI', 'route' => 'panel.channel.rates', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'color' => 'emerald'],
        ['label' => 'Restrictions', 'route' => 'panel.channel.restrictions', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>', 'color' => 'amber'],
        ['label' => 'Conflicts', 'route' => 'panel.channel.conflicts', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>', 'color' => 'red'],
    ] as $nav)
    <a href="{{ route($nav['route']) }}"
       class="flex items-center gap-3 bg-white rounded-xl px-4 py-3 shadow-card border border-gray-100 hover:shadow-card-hover hover:border-{{ $nav['color'] }}-100 transition-all group">
        <div class="w-8 h-8 rounded-lg bg-{{ $nav['color'] }}-50 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-{{ $nav['color'] }}-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">{!! $nav['icon'] !!}</svg>
        </div>
        <span class="text-sm font-medium text-gray-700">{{ $nav['label'] }}</span>
    </a>
    @endforeach
</div>

{{-- Channels grid --}}
@if ($channels->isNotEmpty())
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ($channels as $c)
    @php
        $active = $c->is_active;
        $syncedAt = $c->last_sync_at;
        $syncAgo = $syncedAt?->diffForHumans() ?? 'Never synced';
        $syncWarn = $syncedAt && $syncedAt->diffInHours() > 6;
    @endphp
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 hover:shadow-card-hover transition-all">
        {{-- Header --}}
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl {{ $active ? 'bg-primary-50' : 'bg-gray-100' }} flex items-center justify-center">
                    <svg class="w-5 h-5 {{ $active ? 'text-primary-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-900">{{ $c->name }}</div>
                    <div class="text-xs text-gray-400 font-mono uppercase">{{ $c->code }}</div>
                </div>
            </div>
            <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full {{ $active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                {{ $active ? 'Active' : 'Inactive' }}
            </span>
        </div>

        {{-- Sync info --}}
        <div class="flex items-center gap-2 text-xs {{ $syncWarn ? 'text-amber-600' : 'text-gray-500' }} mb-4">
            @if ($syncWarn)
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            @else
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            @endif
            Last sync: {{ $syncAgo }}
        </div>

        {{-- Actions --}}
        <div class="flex gap-2">
            <a href="{{ route('panel.channel.rates', ['channel' => $c->id]) }}"
               class="flex-1 text-center text-xs font-medium text-primary-600 bg-primary-50 px-3 py-1.5 rounded-lg hover:bg-primary-100 transition-colors">
                Rates
            </a>
            <a href="{{ route('panel.channel.mapping', ['channel' => $c->id]) }}"
               class="flex-1 text-center text-xs font-medium text-gray-600 bg-gray-100 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition-colors">
                Mapping
            </a>
            <a href="{{ route('panel.channel.sync-log', ['channel' => $c->id]) }}"
               class="flex-1 text-center text-xs font-medium text-gray-600 bg-gray-100 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition-colors">
                Log
            </a>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-12 flex flex-col items-center text-center">
    <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
        </svg>
    </div>
    <p class="text-base font-semibold text-gray-600">No channels connected</p>
    <p class="text-sm text-gray-400 mt-1 mb-4">Configure OTA connections in Settings → Integrations.</p>
    <a href="{{ route('panel.settings.integrations') }}"
       class="inline-flex items-center gap-2 text-sm font-semibold text-primary-600 bg-primary-50 px-4 py-2 rounded-xl hover:bg-primary-100 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Go to Integrations
    </a>
</div>
@endif

@endsection
