@extends('panel.layout')
@section('title', 'Lost & Found Item')
@section('content')

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('panel.hk.lost-found.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-2 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Lost & Found
        </a>
    </div>

    @php
        $badge = match ($item->status) {
            'found'    => 'bg-amber-100 text-amber-700',
            'claimed'  => 'bg-blue-100 text-blue-700',
            'returned' => 'bg-emerald-100 text-emerald-700',
            'disposed' => 'bg-gray-100 text-gray-500',
            default    => 'bg-gray-100 text-gray-500',
        };
        $label = match ($item->status) {
            'found'    => 'Found',
            'claimed'  => 'Claimed',
            'returned' => 'Returned',
            'disposed' => 'Disposed',
            default    => ucfirst($item->status),
        };
    @endphp

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $item->description }}</h1>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium mt-1 {{ $badge }}">{{ $label }}</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('panel.hk.lost-found.edit', $item->id) }}"
                   class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
            </div>
        </div>

        {{-- Photo --}}
        @if ($item->photo_path)
        <div class="px-6 py-4 border-b border-gray-100">
            <img src="{{ Storage::url($item->photo_path) }}" class="max-w-full max-h-64 rounded-xl object-cover border border-gray-200">
        </div>
        @endif

        {{-- Details --}}
        <div class="px-6 py-5">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Location Found</dt>
                    <dd class="text-gray-900 font-medium mt-0.5">{{ $item->found_location ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Date Found</dt>
                    <dd class="text-gray-900 font-medium mt-0.5">{{ $item->found_date->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Room</dt>
                    <dd class="text-gray-900 font-medium mt-0.5">{{ $item->room?->number ? 'Room '.$item->room->number.' — '.$item->room->roomType?->name : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Found By</dt>
                    <dd class="text-gray-900 font-medium mt-0.5">{{ $item->foundByUser?->name ?: '—' }}</dd>
                </div>

                @if ($item->status === 'claimed' || $item->status === 'returned')
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Claimed By</dt>
                    <dd class="text-gray-900 font-medium mt-0.5">{{ $item->claimedByGuest?->full_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Claimed Date</dt>
                    <dd class="text-gray-900 font-medium mt-0.5">{{ $item->claimed_date?->format('d M Y') ?: '—' }}</dd>
                </div>
                @endif

                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Notes</dt>
                    <dd class="text-gray-700 mt-0.5">{{ $item->notes ?: '—' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Actions --}}
        @if ($item->status === 'found')
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            <div class="flex flex-col sm:flex-row gap-3">
                {{-- Claim Form --}}
                <form method="POST" action="{{ route('panel.hk.lost-found.claim', $item->id) }}" class="flex-1">
                    @csrf
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Guest (optional)</label>
                            <input type="text" name="claimed_by_guest_id" placeholder="Guest ID"
                                   class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-700 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <button type="submit" class="shrink-0 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                            Claim
                        </button>
                    </div>
                </form>

                <div class="flex gap-2">
                    <form method="POST" action="{{ route('panel.hk.lost-found.return', $item->id) }}">
                        @csrf
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                            Return to Owner
                        </button>
                    </form>
                    <form method="POST" action="{{ route('panel.hk.lost-found.dispose', $item->id) }}"
                          onsubmit="return confirm('Mark this item as disposed?')">
                        @csrf
                        <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg transition">
                            Dispose
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @elseif ($item->status === 'claimed')
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex gap-2">
            <form method="POST" action="{{ route('panel.hk.lost-found.return', $item->id) }}">
                @csrf
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Return to Owner
                </button>
            </form>
            <form method="POST" action="{{ route('panel.hk.lost-found.dispose', $item->id) }}"
                  onsubmit="return confirm('Mark this item as disposed?')">
                @csrf
                <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg transition">
                    Dispose
                </button>
            </form>
        </div>
        @endif
    </div>
</div>

@endsection
