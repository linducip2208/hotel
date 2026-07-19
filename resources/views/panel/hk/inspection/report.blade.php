@extends('panel.layout')
@section('title', 'Inspection Report')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.hk.inspection.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Inspection Report</h1>
        <p class="text-sm text-gray-500">Summary of room inspection performance</p>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-card text-center">
        <div class="text-2xl font-bold text-gray-900">{{ $total }}</div>
        <div class="text-xs text-gray-500 mt-0.5 font-medium">Total Inspections</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-card text-center">
        <div class="text-2xl font-bold text-emerald-600">{{ $passed }}</div>
        <div class="text-xs text-emerald-600 mt-0.5 font-medium">Passed</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-red-100 shadow-card text-center">
        <div class="text-2xl font-bold text-red-600">{{ $failed }}</div>
        <div class="text-xs text-red-600 mt-0.5 font-medium">Failed</div>
    </div>
</div>

<div class="mb-6">
    <p class="text-sm text-gray-600">Pass Rate: <span class="font-bold {{ $passRate >= 80 ? 'text-emerald-600' : ($passRate >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ $passRate }}%</span></p>
    <div class="w-full h-3 bg-gray-200 rounded-full mt-1 overflow-hidden">
        <div class="h-full {{ $passRate >= 80 ? 'bg-emerald-500' : ($passRate >= 50 ? 'bg-amber-500' : 'bg-red-500') }} rounded-full transition-all" style="width:{{ $passRate }}%"></div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Room</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Inspector</th>
                <th class="text-center px-5 py-3 font-semibold text-gray-600">Status</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach ($inspections as $i)
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3 font-medium text-gray-900">Room {{ $i->room?->number ?? '—' }}</td>
                <td class="px-5 py-3 text-gray-600">{{ $i->inspector?->name ?? '—' }}</td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize {{ $i->overall_status === 'pass' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">{{ $i->overall_status }}</span>
                </td>
                <td class="px-5 py-3 text-gray-600">{{ $i->created_at->format('d M Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
