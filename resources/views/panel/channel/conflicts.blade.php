@extends('panel.layout')
@section('title', 'Channel Conflicts')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.channel.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Channel Conflicts</h1>
        <p class="text-sm text-gray-500 mt-0.5">Booking conflicts between OTA channels and the PMS</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Details</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($conflicts as $c)
                @php
                    $typeColors = ['double_booking' => 'red', 'rate_mismatch' => 'amber', 'inventory_mismatch' => 'orange', 'restriction_conflict' => 'violet'];
                    $tc = $typeColors[$c->conflict_type] ?? 'gray';
                    $isOpen = $c->status === 'open';
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors {{ $isOpen ? 'bg-red-50/20' : '' }}">
                    <td class="px-5 py-3.5">
                        <span class="text-xs font-semibold bg-{{ $tc }}-50 text-{{ $tc }}-700 px-2.5 py-1 rounded-full capitalize">
                            {{ str_replace('_', ' ', $c->conflict_type) }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5">
                        <code class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-md font-mono block max-w-xs truncate">{{ json_encode($c->details) }}</code>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if ($isOpen)
                        <span class="inline-flex items-center gap-1 text-xs font-medium bg-red-50 text-red-700 px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span> Open
                        </span>
                        @else
                        <span class="text-xs font-medium bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full capitalize">{{ $c->status }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        @if ($isOpen)
                        <form method="POST" action="{{ route('panel.channel.conflicts.resolve', $c->id) }}">
                            @csrf
                            <button type="submit"
                                    class="text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 px-3 py-1.5 rounded-lg transition-colors">
                                Resolve
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">
                        <div class="flex flex-col items-center justify-center py-12">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-600">No conflicts</p>
                            <p class="text-xs text-gray-400 mt-1">All channels are in sync</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
