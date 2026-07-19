@extends('panel.layout')
@section('title', 'HK Tasks')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Housekeeping Tasks</h1>
        <p class="text-sm text-gray-500 mt-0.5">Assign and track cleaning & maintenance tasks</p>
    </div>
    @php
        $pending = $tasks->where('status', 'pending')->count();
        $inProg = $tasks->where('status', 'in_progress')->count();
        $done = $tasks->where('status', 'done')->count();
    @endphp
    <div class="flex items-center gap-3 text-xs font-medium">
        <span class="bg-amber-50 text-amber-700 px-3 py-1.5 rounded-full">{{ $pending }} pending</span>
        <span class="bg-blue-50 text-blue-700 px-3 py-1.5 rounded-full">{{ $inProg }} in progress</span>
        <span class="bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-full">{{ $done }} done</span>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    @forelse ($tasks as $t)
    @php
        $statusColors = ['pending' => 'amber', 'in_progress' => 'blue', 'done' => 'emerald'];
        $typeIcons = [
            'checkout' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
            'stayover' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',
            'inspection' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 3h6m-6 4h6m-2 4h2"/>',
            'maintenance' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
        ];
        $sc = $statusColors[$t->status] ?? 'gray';
        $icon = $typeIcons[$t->type] ?? '<path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>';
    @endphp
    <div class="flex items-center gap-4 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">

        {{-- Type icon --}}
        <div class="w-9 h-9 rounded-xl bg-{{ $sc }}-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-{{ $sc }}-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        </div>

        {{-- Room + type --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                @if ($t->room?->number)
                <span class="text-sm font-semibold text-gray-900">Room {{ $t->room->number }}</span>
                @else
                <span class="text-sm font-semibold text-gray-900">General Task</span>
                @endif
                <span class="text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full capitalize">{{ str_replace('_', ' ', $t->type) }}</span>
            </div>
            @if ($t->assignee?->name)
            <div class="flex items-center gap-1 mt-0.5 text-xs text-gray-400">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                {{ $t->assignee->name }}
            </div>
            @else
            <div class="text-xs text-gray-400 mt-0.5">Unassigned</div>
            @endif
        </div>

        {{-- Status badge --}}
        <span class="shrink-0 text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">
            {{ str_replace('_', ' ', $t->status) }}
        </span>

        {{-- Quick status update --}}
        <form method="POST" action="{{ route('panel.hk.tasks.update', $t->id) }}" class="shrink-0">
            @csrf @method('PATCH')
            <select name="status" onchange="this.form.submit()"
                    class="text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 bg-white text-gray-700 hover:border-primary-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none cursor-pointer">
                <option value="" disabled selected>Change status</option>
                <option value="pending" @selected($t->status === 'pending')>Pending</option>
                <option value="in_progress" @selected($t->status === 'in_progress')>In Progress</option>
                <option value="done" @selected($t->status === 'done')>Done</option>
            </select>
        </form>

    </div>
    @empty
    <div class="flex flex-col items-center justify-center py-16 text-gray-400">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p class="text-base font-medium text-gray-500">All tasks complete</p>
        <p class="text-sm text-gray-400 mt-1">No pending housekeeping tasks.</p>
    </div>
    @endforelse
</div>

@endsection
