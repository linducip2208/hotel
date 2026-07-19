@extends('panel.layout')
@section('title', 'Event Details')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.banquet.events.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-3 flex-wrap">
            <h1 class="text-2xl font-bold text-gray-900 truncate">{{ $event->title }}</h1>
            @php $sc = match($event->status) { 'confirmed' => 'emerald', 'tentative' => 'amber', 'cancelled' => 'red', 'completed' => 'blue', default => 'gray' }; @endphp
            <span class="text-xs font-semibold bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize shrink-0">{{ $event->status }}</span>
        </div>
        <p class="text-sm text-gray-500 mt-0.5">{{ $event->event_no }} · {{ $event->event_date->isoFormat('dddd, D MMMM Y') }}</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <a href="{{ route('panel.banquet.events.edit', $event->id) }}"
           class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            Edit
        </a>
        <form method="POST" action="{{ route('panel.banquet.events.destroy', $event->id) }}"
              onsubmit="return confirm('Hapus event ini?')" class="inline-flex">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus
            </button>
        </form>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <a href="{{ route('panel.banquet.events.beo', $event->id) }}"
           class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            BEO Sheet
        </a>
    </div>
</div>

<div class="grid md:grid-cols-3 gap-5">

    <div class="md:col-span-2 space-y-5">

        {{-- Event info --}}
        <div class="grid grid-cols-2 gap-5">
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Event Details</div>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Function Room</span>
                        <span class="font-medium text-gray-800">{{ $event->functionRoom?->name }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Setup</span>
                        <span class="font-medium text-gray-800 capitalize">{{ $event->setup }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Attendees</span>
                        <span class="font-medium text-gray-800">{{ $event->expected_attendees }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Time</span>
                        <span class="font-mono text-sm text-gray-800">{{ $event->start_time?->format('H:i') }} – {{ $event->end_time?->format('H:i') }}</span>
                    </div>
                </div>
            </div>

            {{-- Financial summary --}}
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Financial Summary</div>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Venue</span>
                        <span class="font-mono text-sm text-gray-800">Rp {{ number_format($event->venue_rate, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">F&B</span>
                        <span class="font-mono text-sm text-gray-800">Rp {{ number_format($event->fnb_total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Add-ons</span>
                        <span class="font-mono text-sm text-gray-800">Rp {{ number_format($event->addons_total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-100 pt-2 mt-2">
                        <span class="font-semibold text-gray-900">Grand Total</span>
                        <span class="font-mono text-sm font-bold text-gray-900">Rp {{ number_format($event->grand_total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Deposit</span>
                        <span class="font-mono text-sm text-emerald-700">Rp {{ number_format($event->deposit_paid, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-gray-700">Balance Due</span>
                        <span class="font-mono text-sm font-bold {{ $event->balance > 0 ? 'text-red-600' : 'text-emerald-700' }}">
                            Rp {{ number_format($event->balance, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Menu items --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">F&B Menu</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse ($event->menuItems as $m)
                <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50/40 transition-colors">
                    <span class="text-sm text-gray-800">{{ $m->name }}</span>
                    <div class="flex items-center gap-4">
                        <span class="text-xs text-gray-500">×{{ $m->qty }}</span>
                        <span class="font-mono text-sm text-gray-900">Rp {{ number_format($m->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
                @empty
                <div class="px-5 py-6 text-center text-sm text-gray-400">No menu items yet.</div>
                @endforelse
            </div>
            {{-- Add menu item --}}
            <div class="px-5 py-4 bg-gray-50/50 border-t border-gray-100">
                <form method="POST" action="{{ route('panel.banquet.events.menu.store', $event->id) }}"
                      class="grid grid-cols-4 gap-3">
                    @csrf
                    <input type="text" name="name" required placeholder="Item name"
                           class="col-span-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm outline-none focus:border-primary-400 transition-all">
                    <input type="number" name="qty" required min="1" placeholder="Qty"
                           class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm outline-none focus:border-primary-400 transition-all">
                    <input type="number" name="unit_price" step="1" required placeholder="Price"
                           class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm outline-none focus:border-primary-400 transition-all">
                    <button type="submit"
                            class="col-span-4 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Add Menu Item
                    </button>
                </form>
            </div>
        </div>

    </div>

    {{-- Quick info sidebar --}}
    <div class="space-y-5">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Event Type</div>
            <span class="text-sm font-semibold text-gray-800 capitalize">{{ str_replace('_', ' ', $event->event_type) }}</span>
        </div>

        {{-- Ubah Status --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Ubah Status</div>
            <form method="POST" action="{{ route('panel.banquet.events.status', $event->id) }}" class="space-y-3">
                @csrf
                @method('PATCH')
                <select name="status"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="inquiry" {{ $event->status === 'inquiry' ? 'selected' : '' }}>Inquiry</option>
                    <option value="tentative" {{ $event->status === 'tentative' ? 'selected' : '' }}>Tentative</option>
                    <option value="definite" {{ $event->status === 'definite' ? 'selected' : '' }}>Definite</option>
                    <option value="completed" {{ $event->status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $event->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <button type="submit"
                        class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2 rounded-xl transition-colors">
                    Perbarui Status
                </button>
            </form>
        </div>
        @if ($event->notes)
        <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
            <div class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-2">Notes</div>
            <p class="text-sm text-amber-800">{{ $event->notes }}</p>
        </div>
        @endif
    </div>

</div>

@endsection
