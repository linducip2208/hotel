@extends('panel.layout')
@section('title', 'Guest Requests')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Guest Requests</h1>
        <p class="text-sm text-gray-500 mt-0.5">Amenities, housekeeping, maintenance & concierge</p>
    </div>
    <form method="GET">
        <select name="status" onchange="this.form.submit()"
                class="rounded-xl border border-gray-200 bg-white text-sm text-gray-700 px-3.5 py-2 shadow-card focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition-all">
            <option value="" @selected(!request('status'))>All Requests</option>
            <option value="open" @selected(request('status') === 'open')>Open</option>
            <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
            <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
        </select>
    </form>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Requests list --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="divide-y divide-gray-50">
                @forelse ($requests as $r)
                @php
                    $priorityColors = ['urgent' => 'red', 'high' => 'orange', 'normal' => 'blue', 'low' => 'gray'];
                    $statusColors = ['open' => 'amber', 'in_progress' => 'blue', 'resolved' => 'emerald'];
                    $pc = $priorityColors[$r->priority] ?? 'gray';
                    $sc = $statusColors[$r->status] ?? 'gray';
                @endphp
                <div class="flex items-start gap-4 px-5 py-4 hover:bg-gray-50/60 transition-colors">
                    <div class="w-9 h-9 rounded-xl bg-{{ $pc }}-50 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5 text-{{ $pc }}-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-sm font-semibold text-gray-900 truncate">{{ $r->subject }}</span>
                            <span class="text-xs font-medium bg-{{ $pc }}-50 text-{{ $pc }}-700 px-2 py-0.5 rounded-full capitalize shrink-0">{{ $r->priority }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-gray-400">
                            @if ($r->guest?->full_name) <span>{{ $r->guest->full_name }}</span> @endif
                            @if ($r->room?->number) <span>· Room {{ $r->room->number }}</span> @endif
                            <span>· {{ $r->opened_at->diffForHumans() }}</span>
                            <span class="text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full capitalize">{{ $r->category }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">{{ str_replace('_', ' ', $r->status) }}</span>
                        <form method="POST" action="{{ route('panel.concierge.requests.update', $r->id) }}">
                            @csrf @method('PATCH')
                            <select name="action" onchange="this.form.submit()"
                                    class="text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 bg-white text-gray-700 hover:border-primary-400 outline-none transition-all cursor-pointer">
                                <option value="" disabled selected>Update</option>
                                <option value="respond">Respond</option>
                                <option value="resolve">Resolve</option>
                            </select>
                        </form>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                    <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-sm text-gray-500">No requests</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- New request form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">New Request</h2>
        </div>
        <form method="POST" action="{{ route('panel.concierge.requests.store') }}" class="p-5 space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Category <span class="text-red-500">*</span></label>
                    <select name="category" required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        <option value="amenity">Amenity</option>
                        <option value="housekeeping">Housekeeping</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="fnb">F&B</option>
                        <option value="concierge">Concierge</option>
                        <option value="complaint">Complaint</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Priority</label>
                    <select name="priority"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        <option value="low">Low</option>
                        <option value="normal" selected>Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Room ID</label>
                <input type="number" name="room_id" placeholder="Optional"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Subject <span class="text-red-500">*</span></label>
                <input type="text" name="subject" required placeholder="Extra towel, AC not working…"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Description</label>
                <textarea name="description" rows="3" placeholder="Details…"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none"></textarea>
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Create Request
            </button>
        </form>
    </div>

</div>

@endsection
