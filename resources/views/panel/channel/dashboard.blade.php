@extends('panel.layout')
@section('title', 'Dashboard Channel Manager')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard Channel Manager</h1>
    <p class="text-sm text-gray-500 mt-0.5">Ringkasan semua channel OTA, sinkronisasi, dan konflik</p>
</div>

{{-- Quick Links --}}
<div class="flex flex-wrap gap-2 mb-6">
    <a href="{{ route('panel.channel.vcc.index') }}"
       class="inline-flex items-center gap-2 text-xs font-semibold text-gray-600 bg-white border border-gray-200 px-3.5 py-2 rounded-xl hover:bg-gray-50 shadow-card transition-colors">
        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        Virtual Card
    </a>
    <a href="{{ route('panel.channel.gds.index') }}"
       class="inline-flex items-center gap-2 text-xs font-semibold text-gray-600 bg-white border border-gray-200 px-3.5 py-2 rounded-xl hover:bg-gray-50 shadow-card transition-colors">
        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
        GDS Booking
    </a>
    <a href="{{ route('panel.pricing.parity') }}"
       class="inline-flex items-center gap-2 text-xs font-semibold text-gray-600 bg-white border border-gray-200 px-3.5 py-2 rounded-xl hover:bg-gray-50 shadow-card transition-colors">
        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        Parity Alert
    </a>
    <a href="{{ route('panel.channel.mapping') }}"
       class="inline-flex items-center gap-2 text-xs font-semibold text-gray-600 bg-white border border-gray-200 px-3.5 py-2 rounded-xl hover:bg-gray-50 shadow-card transition-colors">
        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4"/></svg>
        Mapping
    </a>
    <a href="{{ route('panel.channel.sync-log') }}"
       class="inline-flex items-center gap-2 text-xs font-semibold text-gray-600 bg-white border border-gray-200 px-3.5 py-2 rounded-xl hover:bg-gray-50 shadow-card transition-colors">
        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Sync Log
    </a>
</div>

{{-- Stats Row --}}
@php
    $channels = $channels ?? collect();
    $activeChannels = $channels->where('is_active', true)->count();
    $totalMappings = $totalMappings ?? 0;
    $openConflicts = $openConflicts ?? 0;
    $parityAlerts = $parityAlerts ?? 0;
@endphp

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
            </div>
            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Channels</span>
        </div>
        <div class="flex items-baseline gap-1">
            <p class="text-3xl font-bold text-gray-900 tabular-nums">{{ $activeChannels }}</p>
            <p class="text-sm text-gray-400">/ {{ $channels->count() }}</p>
        </div>
        <p class="text-xs text-gray-500 mt-0.5">Channel Aktif</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4"/></svg>
            </div>
            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Mappings</span>
        </div>
        <p class="text-3xl font-bold text-gray-900 tabular-nums">{{ $totalMappings }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Total Mapping</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card border {{ ($openConflicts > 0) ? 'border-red-100' : 'border-gray-100' }} p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <span class="text-[10px] font-semibold {{ ($openConflicts > 0) ? 'text-red-500' : 'text-gray-400' }} uppercase tracking-wide">Alert</span>
        </div>
        <p class="text-3xl font-bold {{ ($openConflicts > 0) ? 'text-red-600' : 'text-gray-900' }} tabular-nums">{{ $openConflicts }}</p>
        <p class="text-xs {{ ($openConflicts > 0) ? 'text-red-500' : 'text-gray-500' }} mt-0.5">Konflik Terbuka</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card border {{ ($parityAlerts > 0) ? 'border-amber-100' : 'border-gray-100' }} p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <span class="text-[10px] font-semibold {{ ($parityAlerts > 0) ? 'text-amber-500' : 'text-gray-400' }} uppercase tracking-wide">Alert</span>
        </div>
        <p class="text-3xl font-bold {{ ($parityAlerts > 0) ? 'text-amber-600' : 'text-gray-900' }} tabular-nums">{{ $parityAlerts }}</p>
        <p class="text-xs {{ ($parityAlerts > 0) ? 'text-amber-500' : 'text-gray-500' }} mt-0.5">Parity Alert</p>
    </div>
</div>

{{-- Channel Health Grid --}}
<div class="mb-6">
    <h2 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
        Kesehatan Channel
    </h2>
    @if($channels->isNotEmpty())
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($channels as $c)
        @php
            $active = $c->is_active;
            $syncedAt = $c->last_sync_at;
            $syncAgo = $syncedAt?->diffForHumans() ?? 'Belum sinkron';
            $hoursSinceSync = $syncedAt ? $syncedAt->diffInHours() : null;
            if ($hoursSinceSync === null) {
                $dotColor = 'gray';
                $healthLabel = 'Belum sinkron';
                $healthBg = 'bg-gray-100';
                $healthText = 'text-gray-600';
            } elseif ($hoursSinceSync > 24) {
                $dotColor = 'red';
                $healthLabel = '> 24 jam';
                $healthBg = 'bg-red-50';
                $healthText = 'text-red-600';
            } elseif ($hoursSinceSync > 6) {
                $dotColor = 'yellow';
                $healthLabel = '> 6 jam';
                $healthBg = 'bg-amber-50';
                $healthText = 'text-amber-600';
            } else {
                $dotColor = 'green';
                $healthLabel = 'Tersinkron';
                $healthBg = 'bg-emerald-50';
                $healthText = 'text-emerald-600';
            }
        @endphp
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 hover:shadow-card-hover transition-all">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $active ? 'bg-indigo-50' : 'bg-gray-100' }} flex items-center justify-center">
                        <svg class="w-5 h-5 {{ $active ? 'text-indigo-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
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
                    {{ $active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>

            <div class="flex items-center gap-2 mb-4 text-xs {{ $healthText }}">
                <span class="w-2 h-2 rounded-full bg-{{ $dotColor }}-500"></span>
                Sinkron terakhir: {{ $syncAgo }}
                <span class="{{ $healthBg }} {{ $healthText }} px-1.5 py-0.5 rounded-full text-[10px] font-semibold">{{ $healthLabel }}</span>
            </div>

            <div class="grid grid-cols-4 gap-2 mb-4">
                <div class="text-center">
                    <p class="text-lg font-bold text-gray-800 tabular-nums">{{ $c->mappings_count ?? 0 }}</p>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide">Mapping</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold text-gray-800 tabular-nums">{{ $c->sync_logs_count ?? 0 }}</p>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide">Sync</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold text-gray-800 tabular-nums">{{ $c->vcc_count ?? 0 }}</p>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide">VCC</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold {{ ($c->conflicts_count ?? 0) > 0 ? 'text-red-600' : 'text-gray-800' }} tabular-nums">{{ $c->conflicts_count ?? 0 }}</p>
                    <p class="text-[10px] {{ ($c->conflicts_count ?? 0) > 0 ? 'text-red-400' : 'text-gray-400' }} uppercase tracking-wide">Konflik</p>
                </div>
            </div>

            <a href="{{ route('panel.channel.detail', $c->id) }}"
               class="block w-full text-center text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 py-2 rounded-lg transition-colors">
                Lihat Detail
            </a>
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
        <p class="text-base font-semibold text-gray-600">Belum ada channel terhubung</p>
        <p class="text-sm text-gray-400 mt-1">Konfigurasi channel di Pengaturan → Integrasi.</p>
    </div>
    @endif
</div>

{{-- Two columns: Sync Log + Open Conflicts --}}
<div class="grid lg:grid-cols-2 gap-6">
    {{-- Recent Sync Log --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <h2 class="text-sm font-bold text-gray-900">Log Sinkronisasi Terbaru</h2>
            </div>
            <a href="{{ route('panel.channel.sync-log') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Lihat semua →</a>
        </div>
        @php $recentLogs = $recentSyncLogs ?? collect(); @endphp
        @if($recentLogs->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Waktu</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Channel</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Operasi</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recentLogs->take(10) as $l)
                    @php $ok = $l->status === 'success'; @endphp
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-2.5 text-xs font-mono text-gray-500">{{ $l->created_at->format('d M H:i') }}</td>
                        <td class="px-4 py-2.5 text-sm text-gray-800">{{ $l->channel?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5"><code class="text-xs bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded font-mono">{{ $l->operation }}</code></td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full {{ $ok ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                                {{ $ok ? 'Sukses' : 'Gagal' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-12 text-gray-400">
            <p class="text-sm">Belum ada log sinkronisasi</p>
        </div>
        @endif
    </div>

    {{-- Open Conflicts --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h2 class="text-sm font-bold text-gray-900">Konflik Terbuka</h2>
            </div>
            <a href="{{ route('panel.channel.conflicts') }}" class="text-xs text-red-600 hover:text-red-700 font-medium">Lihat semua →</a>
        </div>
        @php $openConflictsList = $openConflictsList ?? collect(); @endphp
        @if($openConflictsList->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Detail</th>
                        <th class="px-4 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($openConflictsList->take(5) as $c)
                    @php
                        $typeColors = ['double_booking' => 'red', 'rate_mismatch' => 'amber', 'inventory_mismatch' => 'orange', 'restriction_conflict' => 'violet'];
                        $tc = $typeColors[$c->conflict_type] ?? 'gray';
                    @endphp
                    <tr class="hover:bg-gray-50/60 transition-colors bg-red-50/20">
                        <td class="px-5 py-2.5">
                            <span class="text-xs font-semibold bg-{{ $tc }}-50 text-{{ $tc }}-700 px-2 py-0.5 rounded-full capitalize">
                                {{ str_replace('_', ' ', $c->conflict_type) }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5">
                            <code class="text-xs bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded font-mono block max-w-[200px] truncate">{{ is_string($c->details) ? $c->details : json_encode($c->details) }}</code>
                        </td>
                        <td class="px-4 py-2.5 text-right">
                            <form method="POST" action="{{ route('panel.channel.conflicts.resolve', $c->id) }}">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-lg transition-colors">
                                    Resolve
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-12">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-600">Tidak ada konflik</p>
            <p class="text-xs text-gray-400 mt-1">Semua channel sinkron</p>
        </div>
        @endif
    </div>
</div>

@endsection
