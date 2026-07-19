@extends('panel.layout')
@section('title', 'Audit Log')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Audit Log</h1>
    <p class="text-sm text-gray-500 mt-0.5">Security & activity trail for all user actions</p>
</div>

{{-- Filters --}}
<form method="GET" class="flex items-center gap-3 mb-5">
    <div class="relative">
        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" name="action" placeholder="Filter by action…" value="{{ request('action') }}"
               class="pl-9 pr-3 py-2 rounded-xl border border-gray-200 bg-white text-sm text-gray-700 shadow-card focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition-all w-48">
    </div>
    <input type="number" name="user_id" placeholder="User ID" value="{{ request('user_id') }}"
           class="px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm text-gray-700 shadow-card focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition-all w-28">
    <button type="submit"
            class="inline-flex items-center gap-2 text-sm font-medium text-primary-600 bg-primary-50 px-3.5 py-2 rounded-xl hover:bg-primary-100 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
        Apply
    </button>
    @if (request('action') || request('user_id'))
    <a href="{{ route('panel.audit.index') }}" class="text-sm text-gray-400 hover:text-gray-600 transition-colors">Clear</a>
    @endif
</form>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Action</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">User</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Object</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">IP</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($logs as $l)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3 text-xs text-gray-500 font-mono whitespace-nowrap">
                        {{ $l->created_at->format('d M H:i:s') }}
                    </td>
                    <td class="px-4 py-3">
                        <code class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md font-mono">{{ $l->action }}</code>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">
                        <span class="font-medium">{{ $l->user_id }}</span>
                        <span class="text-gray-400">({{ class_basename($l->user_type ?? 'User') }})</span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        @if ($l->auditable_type)
                        <span class="font-medium text-gray-700">{{ class_basename($l->auditable_type) }}</span>
                        <span class="text-gray-400">#{{ $l->auditable_id }}</span>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs font-mono text-gray-400">{{ $l->ip ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('panel.audit.show', $l->id) }}"
                           class="text-xs font-medium text-primary-600 bg-primary-50 px-2.5 py-1 rounded-lg hover:bg-primary-100 transition-colors">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-sm text-gray-400">No audit records found.</td>
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
