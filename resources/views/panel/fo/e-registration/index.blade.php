@extends('panel.layout')
@section('title', 'E-Registration Cards')
@section('content')

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">E-Registration Cards</h1>
            <p class="text-sm text-gray-500 mt-1">Digital registration cards submitted by guests</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Reservation</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Guest</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Signed At</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Verified</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Verified By</th>
                    <th class="px-5 py-3.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse ($cards as $card)
                @php
                    $verifiedBadge = $card->is_verified
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-amber-100 text-amber-700';
                    $verifiedLabel = $card->is_verified ? 'Verified' : 'Pending';
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="font-mono text-xs font-semibold text-gray-700 bg-gray-100 px-2 py-1 rounded-lg">{{ $card->reservation?->ref }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="font-medium text-gray-900">{{ $card->guest?->full_name ?: '—' }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $card->signed_at?->format('d M Y H:i') ?: '—' }}</td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $verifiedBadge }}">{{ $verifiedLabel }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $card->verifiedByStaff?->name ?: '—' }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('panel.fo.e-registration.show', $card->id) }}"
                           class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-800 text-xs font-medium transition">
                            View
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="text-sm font-medium text-gray-700">No e-registration cards yet</p>
                            <p class="text-xs text-gray-400">Cards appear when guests complete registration</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if ($cards->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
            {{ $cards->links() }}
        </div>
    @endif
</div>

@endsection
