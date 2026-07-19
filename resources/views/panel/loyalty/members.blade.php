@extends('panel.layout')
@section('title', 'Loyalty Members')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Loyalty Members</h1>
    <p class="text-sm text-gray-500 mt-0.5">Member points, tiers, and enrollment</p>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Members table --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Active Members</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Member</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tier</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Points</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Lifetime</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($members as $m)
                        @php
                            $name = $m->guest?->full_name ?? 'Guest';
                            $initials = collect(explode(' ', $name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
                            $tierName = $m->tier?->name ?? 'Member';
                            $tierColors = ['Platinum' => 'violet', 'Gold' => 'amber', 'Silver' => 'gray', 'Member' => 'primary'];
                            $tc = $tierColors[$tierName] ?? 'primary';
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold shrink-0">{{ $initials }}</div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $name }}</div>
                                        <div class="text-xs font-mono text-gray-400">{{ $m->membership_no }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="text-xs font-semibold bg-{{ $tc }}-50 text-{{ $tc }}-700 px-2.5 py-1 rounded-full">{{ $tierName }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-semibold text-gray-900">{{ number_format($m->points_balance) }}</td>
                            <td class="px-4 py-3.5 text-right font-mono text-gray-500">{{ number_format($m->lifetime_points) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-sm text-gray-400">No members enrolled yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($members->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $members->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Enroll form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Enroll Guest</h2>
            <p class="text-xs text-gray-400 mt-0.5">Add a guest to the loyalty program</p>
        </div>
        <form method="POST" action="{{ route('panel.loyalty.enroll') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Guest ID <span class="text-red-500">*</span></label>
                <input type="number" name="guest_id" value="{{ old('guest_id') }}" required placeholder="Enter guest ID"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Enroll Member
            </button>
        </form>
    </div>

</div>

@endsection
