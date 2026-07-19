@extends('panel.layout')
@section('title', 'ARI Sync Log')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.channel.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">ARI Sync Log</h1>
        <p class="text-sm text-gray-500 mt-0.5">Rate & inventory push operations to OTA channels</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Channel</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Operation</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($logs as $l)
                @php $ok = $l->status === 'success'; @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3 text-xs font-mono text-gray-500">{{ $l->created_at->format('d M H:i:s') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800">{{ $l->channel?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <code class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md font-mono">{{ $l->operation }}</code>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-0.5 rounded-full {{ $ok ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                @if ($ok)
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                @else
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                @endif
                            </svg>
                            {{ $l->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-10 text-center text-sm text-gray-400">No sync operations yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($logs->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
        {{ $logs->links() }}
    </div>
    @endif
</div>

@endsection
