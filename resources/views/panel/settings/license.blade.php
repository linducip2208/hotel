@extends('panel.layout')
@section('title', 'License')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">License & Activation</h1>
    <p class="text-sm text-gray-500 mt-0.5">Pairing status, heartbeat, and plan details</p>
</div>

@php
    $licenseStatus = $local?->status ?? 'unpaired';
    $isActive = in_array($licenseStatus, ['active', 'grace']);
    $isGrace = $licenseStatus === 'grace';
    $statusColor = match ($licenseStatus) {
        'active' => 'emerald',
        'grace'  => 'amber',
        default  => 'red',
    };
@endphp

<div class="max-w-2xl space-y-4">

    {{-- Status card --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">Activation Status</h2>
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-{{ $statusColor }}-50 text-{{ $statusColor }}-700 px-3 py-1 rounded-full capitalize">
                <span class="w-2 h-2 rounded-full bg-{{ $statusColor }}-500 @if($isActive && !$isGrace) animate-pulse @endif"></span>
                {{ $licenseStatus }}
            </span>
        </div>
        <div class="p-5 grid grid-cols-2 gap-4">
            <div>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Plan</div>
                <div class="text-sm font-semibold text-gray-900">{{ $local?->plan ?? '—' }}</div>
            </div>
            <div>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Valid Until</div>
                <div class="text-sm font-semibold text-gray-900">
                    {{ $local?->valid_until?->format('d M Y') ?? '—' }}
                </div>
            </div>
            <div>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Last Heartbeat</div>
                <div class="text-sm text-gray-700">
                    {{ $local?->last_heartbeat_success_at?->diffForHumans() ?? 'Never' }}
                </div>
            </div>
            @if ($local?->grace_until)
            <div>
                <div class="text-xs font-semibold text-amber-500 uppercase tracking-wide mb-1">Grace Until</div>
                <div class="text-sm font-semibold text-amber-700">{{ $local->grace_until->format('d M Y') }}</div>
            </div>
            @endif
        </div>
        <div class="px-5 py-4 border-t border-gray-50 flex gap-3">
            <form method="POST" action="{{ route('panel.settings.license.refresh') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 text-sm font-semibold bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Refresh Heartbeat
                </button>
            </form>
            <form method="POST" action="{{ route('panel.settings.license.migrate') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-xl shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    Migrate Install
                </button>
            </form>
        </div>
    </div>

    {{-- Raw status --}}
    @if ($status)
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Raw License Payload</h2>
        </div>
        <div class="p-5">
            <pre class="bg-gray-50 rounded-xl p-4 text-xs text-gray-600 overflow-x-auto leading-relaxed">{{ json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
    @endif

</div>

@endsection
