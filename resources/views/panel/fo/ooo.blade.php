@extends('panel.layout')
@section('title', 'Out of Order Rooms')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Out of Order Rooms</h1>
        <p class="text-sm text-gray-500 mt-0.5">Block rooms undergoing maintenance, renovation, or repairs</p>
    </div>
    @php $activeCount = $periods->where('status', 'active')->count(); @endphp
    @if ($activeCount > 0)
    <span class="inline-flex items-center gap-1.5 text-sm font-semibold bg-red-50 text-red-700 px-3.5 py-1.5 rounded-full">
        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
        {{ $activeCount }} Active Block{{ $activeCount > 1 ? 's' : '' }}
    </span>
    @endif
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- OOO periods list --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Room</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Period</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Reason</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($periods as $p)
                        @php
                            $isActive = $p->status === 'active';
                            $reasonColors = ['maintenance' => 'amber', 'renovation' => 'blue', 'deep_clean' => 'emerald', 'damage' => 'red', 'other' => 'gray'];
                            $rc = $reasonColors[$p->reason] ?? 'gray';
                            $days = $p->from_date->diffInDays($p->to_date);
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors {{ $isActive ? 'bg-red-50/20' : '' }}">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl {{ $isActive ? 'bg-red-100' : 'bg-gray-100' }} flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5 {{ $isActive ? 'text-red-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">Room {{ $p->room?->number ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="text-sm text-gray-700 font-medium">{{ $p->from_date->format('d M Y') }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">→ {{ $p->to_date->format('d M Y') }} · {{ $days }} day{{ $days != 1 ? 's' : '' }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="text-xs font-medium bg-{{ $rc }}-50 text-{{ $rc }}-700 px-2.5 py-1 rounded-full capitalize">
                                    {{ str_replace('_', ' ', $p->reason) }}
                                </span>
                                @if ($p->description)
                                <div class="text-xs text-gray-400 mt-1 max-w-[160px] truncate">{{ $p->description }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if ($isActive)
                                <span class="inline-flex items-center gap-1 text-xs font-medium bg-red-50 text-red-700 px-2.5 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span> Active
                                </span>
                                @else
                                <span class="text-xs font-medium bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full capitalize">{{ $p->status }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                @if ($isActive)
                                <form method="POST" action="{{ route('panel.fo.ooo.clear', $p->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1 rounded-lg transition-colors">
                                        Clear
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">
                                <div class="flex flex-col items-center justify-center py-12">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-600">All rooms available</p>
                                    <p class="text-xs text-gray-400 mt-1">No out-of-order blocks recorded</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- New OOO form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Block Room</h2>
        </div>
        <form method="POST" action="{{ route('panel.fo.ooo.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Room ID <span class="text-red-500">*</span></label>
                <input type="number" name="room_id" required placeholder="Room ID"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">From <span class="text-red-500">*</span></label>
                    <input type="date" name="from_date" required
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">To <span class="text-red-500">*</span></label>
                    <input type="date" name="to_date" required
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Reason <span class="text-red-500">*</span></label>
                <select name="reason" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="maintenance">Maintenance</option>
                    <option value="renovation">Renovation</option>
                    <option value="deep_clean">Deep Clean</option>
                    <option value="damage">Damage</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Description</label>
                <textarea name="description" rows="3" placeholder="Optional details…"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none"></textarea>
            </div>
            <button type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Block Room
            </button>
        </form>
    </div>

</div>

@endsection
