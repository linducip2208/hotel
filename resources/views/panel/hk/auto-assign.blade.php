@extends('panel.layout')
@section('title', 'Auto-Assign Housekeeping')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Auto-Assign Housekeeping</h1>
        <p class="text-sm text-gray-500 mt-0.5">Generate tugas cleaning otomatis & assign ke attendant</p>
    </div>
    <div class="flex items-center gap-2">
        <form method="POST" action="{{ route('panel.hk.auto-assign.generate') }}">
            @csrf
            <button class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Generate Tasks
            </button>
        </form>
        <form method="POST" action="{{ route('panel.hk.auto-assign.assign') }}">
            @csrf
            <button class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Auto Assign
            </button>
        </form>
    </div>
</div>

{{-- Workload Summary --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 mb-6">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Beban Kerja Attendant Hari Ini</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Pending</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">In Progress</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Selesai Hari Ini</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Aktif</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($workload as $w)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 font-medium text-gray-900">{{ $w['name'] }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-semibold bg-amber-50 text-amber-700 px-2.5 py-1 rounded-full">{{ $w['pending'] }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-semibold bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full">{{ $w['in_progress'] }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-semibold bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full">{{ $w['completed'] }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-center font-semibold text-gray-700">{{ $w['total'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-10 text-center text-sm text-gray-400">Tidak ada attendant housekeeping aktif.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Unassigned Tasks --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100">
    <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-700">Tugas Belum Ter-assign ({{ $unassigned->count() }})</h2>
    </div>
    @forelse ($unassigned as $task)
    <div class="flex items-center gap-4 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">
        <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-gray-900">{{ $task->room?->number ? 'Room '.$task->room->number : 'General' }}</span>
                <span class="text-xs font-medium bg-{{ $task->priority === 'high' ? 'rose' : ($task->priority === 'medium' ? 'amber' : 'gray') }}-100 text-{{ $task->priority === 'high' ? 'rose' : ($task->priority === 'medium' ? 'amber' : 'gray') }}-700 px-2 py-0.5 rounded-full capitalize">{{ $task->priority }}</span>
            </div>
            <div class="text-xs text-gray-400 mt-0.5">{{ $task->type }} · {{ $task->scheduled_date?->format('d M') }}</div>
        </div>
        <form method="POST" action="{{ route('panel.hk.auto-assign.reassign', $task->id) }}" class="flex items-center gap-2 shrink-0">
            @csrf
            <select name="assignee_id" class="text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 bg-white text-gray-700 hover:border-indigo-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all outline-none">
                <option value="">Assign ke...</option>
                @foreach ($attendants as $a)
                <option value="{{ $a->id }}">{{ $a->full_name }}</option>
                @endforeach
            </select>
            <button class="text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg transition-colors">Assign</button>
        </form>
    </div>
    @empty
    <div class="flex flex-col items-center justify-center py-16 text-gray-400">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p class="text-base font-medium text-gray-500">Semua tugas ter-assign</p>
        <p class="text-sm text-gray-400 mt-1">Tidak ada tugas pending yang belum di-assign.</p>
    </div>
    @endforelse
</div>

@endsection
