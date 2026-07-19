@extends('panel.layout')
@section('title', 'Function Rooms')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Function Rooms</h1>
    <p class="text-sm text-gray-500 mt-0.5">Ballrooms, meeting rooms, and event spaces</p>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Rooms list --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Room</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Capacity</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Half-Day</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Full-Day</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($rooms as $r)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $r->name }}</div>
                                        <div class="text-xs font-mono text-gray-400">{{ $r->code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-sm font-semibold text-gray-700">{{ $r->capacity_banquet }}</span>
                                <span class="text-xs text-gray-400 ml-1">pax</span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">
                                Rp {{ number_format($r->half_day_rate, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">
                                Rp {{ number_format($r->full_day_rate, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="openEditRoom('{{ $r->id }}', '{{ $r->code }}', '{{ $r->name }}', '{{ $r->capacity_classroom }}', '{{ $r->capacity_theatre }}', '{{ $r->capacity_banquet }}', '{{ $r->half_day_rate }}', '{{ $r->full_day_rate }}')"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors"
                                            title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('panel.banquet.function-rooms.destroy', $r->id) }}"
                                          onsubmit="return confirm('Hapus function room ini?')" class="inline-flex">
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
                            <td colspan="5">
                                <div class="flex flex-col items-center justify-center py-10">
                                    <p class="text-sm text-gray-400">No function rooms yet.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add room form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Tambah Function Room</h2>
        </div>
        <form method="POST" action="{{ route('panel.banquet.function-rooms.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Code <span class="text-red-500">*</span></label>
                <input type="text" name="code" required placeholder="BR1"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm font-mono outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Grand Ballroom"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Classroom</label>
                    <input type="number" name="capacity_classroom" placeholder="50"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Theatre</label>
                    <input type="number" name="capacity_theatre" placeholder="100"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Banquet</label>
                    <input type="number" name="capacity_banquet" placeholder="80"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Half-Day Rate (Rp)</label>
                <input type="number" step="1" name="half_day_rate" placeholder="3000000"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Full-Day Rate (Rp)</label>
                <input type="number" step="1" name="full_day_rate" placeholder="5000000"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Tambah Room
            </button>
        </form>
    </div>

</div>

{{-- Edit Room Modal --}}
<div id="editRoomModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" onclick="closeEditRoom()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-700">Edit Function Room</h2>
            <button type="button" onclick="closeEditRoom()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="editRoomForm" method="POST" class="space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Code <span class="text-red-500">*</span></label>
                <input type="text" name="code" id="edit_code" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm font-mono outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="edit_name" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Classroom</label>
                    <input type="number" name="capacity_classroom" id="edit_classroom"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Theatre</label>
                    <input type="number" name="capacity_theatre" id="edit_theatre"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Banquet</label>
                    <input type="number" name="capacity_banquet" id="edit_banquet"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Half-Day Rate (Rp)</label>
                <input type="number" step="1" name="half_day_rate" id="edit_half"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Full-Day Rate (Rp)</label>
                <input type="number" step="1" name="full_day_rate" id="edit_full"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<script>
    function openEditRoom(id, code, name, classroom, theatre, banquet, half, full) {
        var form = document.getElementById('editRoomForm');
        form.action = '{{ url("panel/banquet/function-rooms") }}/' + id;
        document.getElementById('edit_code').value = code;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_classroom').value = classroom || '';
        document.getElementById('edit_theatre').value = theatre || '';
        document.getElementById('edit_banquet').value = banquet || '';
        document.getElementById('edit_half').value = half || '';
        document.getElementById('edit_full').value = full || '';
        document.getElementById('editRoomModal').classList.remove('hidden');
    }

    function closeEditRoom() {
        document.getElementById('editRoomModal').classList.add('hidden');
    }
</script>

@endsection
