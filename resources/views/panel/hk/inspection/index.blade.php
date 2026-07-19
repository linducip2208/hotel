@extends('panel.layout')
@section('title', 'Inspection Log')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Inspection Checklist</h1>
        <p class="text-sm text-gray-500 mt-0.5">Room inspection log and pass/fail tracking</p>
    </div>
    <a href="{{ route('panel.hk.inspection.report') }}" class="inline-flex items-center gap-1.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Report
    </a>
</div>

{{-- Filters --}}
<form method="GET" class="flex items-center gap-3 mb-4 flex-wrap">
    <select name="status" class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-gray-600">
        <option value="">All Status</option>
        <option value="pass" @selected(request('status') === 'pass')>Pass</option>
        <option value="fail" @selected(request('status') === 'fail')>Fail</option>
        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
    </select>
    <input type="date" name="date" value="{{ request('date') }}" class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-gray-600">
    <button type="submit" class="text-xs bg-primary-50 text-primary-700 hover:bg-primary-100 px-3 py-1.5 rounded-lg font-medium transition-colors">Filter</button>
</form>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Date</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Room</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Inspector</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600">Items</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($inspections as $inspection)
                @php
                    $totalItems = count($inspection->items ?? []);
                    $passedItems = collect($inspection->items)->where('status', 'pass')->count();
                    $statusBadge = match($inspection->overall_status) {
                        'pass' => 'bg-emerald-50 text-emerald-700',
                        'fail' => 'bg-red-50 text-red-700',
                        default => 'bg-amber-50 text-amber-700',
                    };
                @endphp
                <tr class="hover:bg-gray-50/50">
                    <td class="px-5 py-3 text-gray-600 whitespace-nowrap">{{ $inspection->created_at->format('d M Y H:i') }}</td>
                    <td class="px-5 py-3 font-medium text-gray-900">
                        Room {{ $inspection->room?->number ?? '—' }}
                        <span class="text-xs text-gray-400 block">{{ $inspection->room?->roomType?->name }}</span>
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $inspection->inspector?->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="text-xs text-gray-500">{{ $passedItems }}/{{ $totalItems }} passed</span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize {{ $statusBadge }}">{{ $inspection->overall_status }}</span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <a href="{{ route('panel.hk.inspection.show', $inspection->id) }}" class="text-xs text-primary-600 hover:text-primary-700 font-medium">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-16 text-center text-gray-400">No inspections recorded.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($inspections->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $inspections->links() }}</div>
    @endif
</div>

@endsection
