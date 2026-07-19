@extends('panel.layout')
@section('title', 'Preventive Maintenance')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Preventive Maintenance</h1>
    <p class="text-sm text-gray-500 mt-0.5">Scheduled maintenance and inspection cadence</p>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Asset</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Frequency</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Next Due</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Last Done</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($schedules as $s)
                @php
                    $isOverdue = $s->next_due_at?->isPast();
                    $isDueSoon = !$isOverdue && $s->next_due_at?->diffInDays(now()) <= 7;
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors {{ $isOverdue ? 'bg-red-50/20' : '' }}">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg {{ $isOverdue ? 'bg-red-50' : ($isDueSoon ? 'bg-amber-50' : 'bg-blue-50') }} flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 {{ $isOverdue ? 'text-red-500' : ($isDueSoon ? 'text-amber-500' : 'text-blue-500') }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-800">{{ $s->asset?->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full capitalize">{{ $s->frequency }}</span>
                    </td>
                    <td class="px-4 py-3.5">
                        @if ($s->next_due_at)
                        <div class="flex items-center gap-2">
                            <span class="text-sm {{ $isOverdue ? 'text-red-600 font-semibold' : ($isDueSoon ? 'text-amber-600 font-medium' : 'text-gray-700') }}">
                                {{ $s->next_due_at->format('d M Y') }}
                            </span>
                            @if ($isOverdue)
                            <span class="text-xs font-semibold text-red-500">Overdue</span>
                            @elseif ($isDueSoon)
                            <span class="text-xs font-semibold text-amber-500">Soon</span>
                            @endif
                        </div>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-sm {{ $s->last_done_at ? 'text-gray-600' : 'text-gray-300' }}">
                        {{ $s->last_done_at?->format('d M Y') ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-12 text-center text-sm text-gray-400">No PPM schedules configured.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
