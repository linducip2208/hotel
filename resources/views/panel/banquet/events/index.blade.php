@extends('panel.layout')
@section('title', 'Banquet Events')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Banquet Events</h1>
        <p class="text-sm text-gray-500 mt-0.5">Function room bookings & event orders</p>
    </div>
    <a href="{{ route('panel.banquet.events.create') }}"
       class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Event
    </a>
</div>

{{-- Search & Filter --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4 mb-5">
    <form method="GET" action="{{ route('panel.banquet.events.index') }}" class="flex items-center gap-3 flex-wrap">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul event atau nomor event..."
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Dari</label>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Sampai</label>
            <input type="date" name="to" value="{{ request('to') }}"
                   class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit"
                    class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-sm transition-colors">
                Filter
            </button>
            <a href="{{ route('panel.banquet.events.index') }}"
               class="text-sm font-medium text-gray-500 hover:text-gray-700 px-3 py-2 transition-colors">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Event</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Venue</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($events as $e)
                @php
                    $statusColors = ['inquiry' => 'purple', 'tentative' => 'amber', 'definite' => 'emerald', 'completed' => 'blue', 'cancelled' => 'red'];
                    $sc = $statusColors[$e->status] ?? 'gray';
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <a href="{{ route('panel.banquet.events.show', $e->id) }}"
                           class="font-mono text-sm font-medium text-primary-600 hover:text-primary-800">{{ $e->event_no }}</a>
                        <a href="{{ route('panel.banquet.events.show', $e->id) }}" class="block text-sm text-gray-700 hover:text-primary-600 mt-0.5">{{ $e->title }}</a>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">{{ $e->event_date->format('d M Y') }}</td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">{{ $e->functionRoom?->name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-0.5 rounded-full capitalize">{{ $e->status }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm font-semibold text-gray-900">
                        Rp {{ number_format($e->grand_total, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('panel.banquet.events.edit', $e->id) }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors"
                               title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('panel.banquet.events.destroy', $e->id) }}"
                                  onsubmit="return confirm('Hapus event ini?')" class="inline-flex">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors"
                                        title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-16 text-center">
                        <div class="flex flex-col items-center text-gray-400">
                            <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-sm font-medium text-gray-500">No events yet</p>
                            <p class="text-xs text-gray-400 mt-1">Create your first banquet event.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($events->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
        {{ $events->links() }}
    </div>
    @endif
</div>

@endsection
