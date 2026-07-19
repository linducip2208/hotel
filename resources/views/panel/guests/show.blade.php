@extends('panel.layout')
@section('title', 'Guest Profile')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.guests.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900">{{ $guest->full_name }}</h1>
            @if ($guest->is_vip)
            <span class="text-xs font-semibold bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full">VIP</span>
            @endif
        </div>
        <p class="text-sm text-gray-500 mt-0.5">{{ $guest->email }} · {{ $guest->phone }} · {{ $guest->country }}</p>
    </div>
    <a href="{{ route('panel.guests.profile', $guest->id) }}"
       class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        360° Profile
    </a>
</div>

<div class="max-w-3xl">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Stay History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Ref</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Check-in</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($guest->reservations as $r)
                    @php $sc = match($r->status) { 'confirmed' => 'emerald', 'checked_in' => 'blue', 'checked_out' => 'gray', 'cancelled' => 'red', default => 'gray' }; @endphp
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-3.5">
                            <a href="{{ route('panel.fo.reservations.show', $r->id) }}"
                               class="font-mono text-sm font-semibold text-primary-600 hover:text-primary-800 transition-colors">
                                {{ $r->ref }}
                            </a>
                        </td>
                        <td class="px-4 py-3.5 text-sm text-gray-700">{{ $r->check_in->format('d M Y') }}</td>
                        <td class="px-4 py-3.5 text-center">
                            <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">{{ $r->status }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-10 text-center text-sm text-gray-400">No stays yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
