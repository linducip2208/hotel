@extends('panel.layout')
@section('title', 'Door Lock Settings')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Door Lock Integration</h1>
    <p class="text-sm text-gray-500 mt-0.5">Configure lock system and manage key cards</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">

    {{-- Provider Config --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Provider Configuration</h2>
        </div>
        <form method="POST" action="{{ route('panel.settings.locks.configure') }}" class="p-5 space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Provider Name</label>
                <input type="text" name="name" required placeholder="My Lock System"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Vendor</label>
                <select name="vendor" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="salto">Salto (ProAccess)</option>
                    <option value="onity">Onity (OnPortal)</option>
                    <option value="vingcard">Vingcard (Visionline)</option>
                    <option value="dormakaba">Dormakaba (Ambiance)</option>
                    <option value="miwa">Miwa (ALV2)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Base URL</label>
                <input type="url" name="base_url" required placeholder="https://lock-system.example.com"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">API Key / Token</label>
                <input type="password" name="api_key" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Hotel Code</label>
                    <input type="text" name="hotel_code" placeholder="Optional"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Facility Key</label>
                    <input type="text" name="facility_code" placeholder="Optional"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Save Configuration
            </button>
            <button type="button" id="testConnection"
                    class="w-full bg-white border border-gray-200 hover:bg-gray-50 text-sm font-medium py-2.5 rounded-xl transition-colors">
                Test Connection
            </button>
        </form>
    </div>

    {{-- Room Key Management --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">Room Key Management</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Room</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($rooms as $room)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-3.5 font-medium text-gray-800">{{ $room->room_number ?? 'R'.$room->id }}</td>
                        <td class="px-4 py-3.5 text-gray-600">{{ $room->roomType?->name }}</td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-500">
                                <span class="w-1.5 h-1.5 rounded-full {{ $room->is_active ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                                {{ $room->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="issueKey({{ $room->id }})"
                                        class="text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 px-2.5 py-1.5 rounded-lg transition-colors">
                                    Issue Key
                                </button>
                                <button onclick="revokeKey({{ $room->id }})"
                                        class="text-xs font-medium bg-red-50 text-red-700 hover:bg-red-100 px-2.5 py-1.5 rounded-lg transition-colors">
                                    Revoke
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-10 text-center text-sm text-gray-400">No rooms configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rooms->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $rooms->links() }}</div>
        @endif
    </div>

</div>

{{-- Issue Key Modal --}}
<div x-data="{
    open: false,
    roomId: null,
    loading: false,
    response: null,
    error: null,
    issueKey(roomId) {
        this.roomId = roomId;
        this.response = null;
        this.error = null;
        this.open = true;
    },
    async submit() {
        this.loading = true;
        try {
            const fd = new FormData(document.getElementById('issueKeyForm'));
            fd.append('mobile_key', document.getElementById('mobileKeyCb')?.checked ?? false);
            const res = await fetch('/panel/settings/locks/issue/' + this.roomId, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                body: fd,
            });
            const data = await res.json();
            if (data.ok) { this.response = data.key; this.error = null; }
            else { this.error = data.error; }
        } catch(e) { this.error = e.message; }
        this.loading = false;
    }
}" x-show="open" x-on:issue-key.window="issueKey($event.detail.id)" x-on:revoke-key.window="/* handle revoke */" class="hidden">

    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none" x-transition>
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="open=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Issue Key — Room <span x-text="roomId"></span></h3>
            <form id="issueKeyForm" class="space-y-3" x-show="!response">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Guest ID</label>
                    <input type="number" name="guest_id" required
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Reservation ID</label>
                    <input type="number" name="reservation_id" required
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm">
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" id="mobileKeyCb" class="rounded border-gray-300">
                    <span class="text-gray-600">Mobile Key (BLE)</span>
                </label>
                <div class="flex gap-2 pt-2">
                    <button type="button" @click="submit()" :disabled="loading"
                            class="flex-1 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors"
                            x-text="loading ? 'Issuing...' : 'Issue Key'"></button>
                    <button type="button" @click="open=false"
                            class="px-4 bg-gray-100 hover:bg-gray-200 text-sm font-medium py-2.5 rounded-xl transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
            <div x-show="response" class="space-y-3">
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 text-sm">
                    Key issued successfully!<br>
                    Key ID: <span class="font-mono font-bold" x-text="response?.key_id"></span><br>
                    Valid: <span x-text="response?.valid_from + ' to ' + response?.valid_to"></span>
                </div>
                <button @click="open=false" class="w-full bg-primary-600 text-white text-sm font-semibold py-2.5 rounded-xl">Done</button>
            </div>
            <div x-show="error" class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 text-sm" x-text="error"></div>
        </div>
    </div>
</div>

<script>
function issueKey(roomId) {
    window.dispatchEvent(new CustomEvent('issue-key', { detail: { id: roomId } }));
    // Also try Alpine
    const el = document.querySelector('[x-data]');
    if (el && el.__x) el.__x.$data.issueKey(roomId);
}
function revokeKey(roomId) {
    if (confirm('Revoke all keys for room ' + roomId + '?')) {
        fetch('/panel/settings/locks/revoke/' + roomId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ key_id: null }),
        }).then(r => r.json()).then(d => { if (d.ok) alert('Keys revoked.'); else alert(d.error); });
    }
}

document.getElementById('testConnection')?.addEventListener('click', async function() {
    this.disabled = true;
    this.textContent = 'Testing...';
    try {
        const res = await fetch('/panel/settings/locks/test', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
        });
        const data = await res.json();
        alert(data.ok ? 'Connection OK. Status: ' + JSON.stringify(data.status) : 'Error: ' + data.error);
    } catch(e) { alert('Error: ' + e.message); }
    this.disabled = false;
    this.textContent = 'Test Connection';
});
</script>

@endsection
