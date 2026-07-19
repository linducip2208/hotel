<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>KDS — Kitchen Display</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0f172a; color: #f1f5f9; font-family: 'Inter', system-ui, sans-serif; overflow-x: hidden; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        @keyframes pulse-border {
            0%, 100% { border-color: #ef4444; }
            50% { border-color: #fca5a5; }
        }
        .overdue-card { animation: pulse-border 1s infinite; }
    </style>
</head>
<body x-data="kds()" x-init="startPolling()" class="min-h-screen">

{{-- Header --}}
<header class="bg-navy-800 border-b border-navy-700 px-5 py-3 flex items-center justify-between sticky top-0 z-10">
    <div>
        <h1 class="text-lg font-bold text-white">Kitchen Display</h1>
        <p class="text-xs text-gray-400">
            <span x-text="activeCount"></span> active · <span x-text="completedCount"></span> completed
        </p>
    </div>
    <div class="flex items-center gap-3 text-xs">
        <span class="text-gray-400" x-text="'Updated: ' + lastUpdate"></span>
        <span class="w-2 h-2 rounded-full bg-emerald-400" x-show="connected"></span>
        <span class="w-2 h-2 rounded-full bg-red-400" x-show="!connected"></span>
    </div>
</header>

{{-- Active orders grid --}}
<div class="p-4">
    <template x-if="activeOrders.length === 0 && completedOrders.length === 0">
        <div class="flex items-center justify-center py-20 text-gray-500 text-center">
            <div>
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="text-sm font-medium">No orders</p>
                <p class="text-xs text-gray-600 mt-1">Waiting for new orders...</p>
            </div>
        </div>
    </template>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
        {{-- Active order cards --}}
        <template x-for="order in activeOrders" :key="'active-'+order.id">
            <div class="rounded-2xl border-2 p-4 flex flex-col gap-2 transition-all"
                 :class="{
                    'border-red-500 bg-red-500/10 overdue-card': order.priority === 'overdue',
                    'border-amber-400 bg-amber-400/10': order.priority === 'warning',
                    'border-navy-600 bg-navy-800': order.priority === 'normal',
                 }">
                {{-- Header --}}
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-white" x-text="order.order_no"></span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                          :class="{
                            'bg-red-500/30 text-red-300': order.priority === 'overdue',
                            'bg-amber-500/30 text-amber-300': order.priority === 'warning',
                            'bg-navy-600 text-gray-300': order.priority === 'normal',
                          }"
                          x-text="order.minutes_elapsed + 'm'"></span>
                </div>

                {{-- Table + outlet --}}
                <div class="text-xs text-gray-400">
                    <span x-text="'Table ' + order.table"></span>
                    <span class="text-gray-600 mx-1">·</span>
                    <span x-text="order.outlet"></span>
                </div>

                {{-- Items --}}
                <div class="flex-1 space-y-1">
                    <template x-for="item in order.items" :key="item.id">
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-5 h-5 rounded bg-navy-700 flex items-center justify-center text-xs font-bold text-gray-300" x-text="item.qty"></span>
                            <span class="text-gray-200" x-text="item.name"></span>
                            <span x-show="item.notes" class="text-xs text-amber-400 truncate max-w-[120px]" x-text="'('+item.notes+')'"></span>
                        </div>
                    </template>
                </div>

                @if ($order->notes)
                <div class="text-xs text-amber-400 bg-amber-400/10 px-2 py-1 rounded-lg" x-show="order.notes" x-text="'Note: ' + order.notes"></div>
                @endif

                {{-- Actions --}}
                <div class="flex gap-2 mt-1 pt-2 border-t border-navy-700" x-show="order.id">
                    <button @click="startPreparing(order.id)"
                            x-show="order.status === 'open' || order.status === 'sent'"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium py-1.5 rounded-lg transition-colors">
                        Start
                    </button>
                    <button @click="markReady(order.id)"
                            x-show="order.status === 'preparing'"
                            class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-medium py-1.5 rounded-lg transition-colors">
                        Ready
                    </button>
                </div>
            </div>
        </template>
    </div>

    {{-- Completed section (bump bar) --}}
    <template x-if="completedOrders.length > 0">
        <div class="mt-6">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Completed</h2>
            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                <template x-for="order in completedOrders" :key="'done-'+order.id">
                    <div class="shrink-0 bg-navy-800 border border-navy-700 rounded-xl p-3 min-w-[160px]">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-gray-300" x-text="order.order_no"></span>
                            <button @click="recallOrder(order.id)" class="text-[10px] text-indigo-400 hover:text-indigo-300 font-medium">Recall</button>
                        </div>
                        <p class="text-xs text-gray-500">
                            <span x-text="'Table ' + order.table"></span>
                            <span class="mx-1">·</span>
                            <span x-text="order.minutes_since_done + 'm ago'"></span>
                        </p>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>

{{-- Sound alert trigger (hidden) --}}
<audio id="new-order-sound" preload="auto" style="display:none">
    <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACAf39/f4B/f3+AgH9/f3+Af39/gIB/f39/gH9/f4CAf39/f4B/f3+AgH9/f3+Af39/gIB/f39/gH9/f4CAf39/f4B/f3+AgH9/f3+Af39/gIB/f39/gH9/f4CAf39/f4B/f3+AgH9/f3+Af39/gIB/f39/gH9/f4CAf39/f4B/f3+AgH9/f3+Af39/gIB/f39/gH9/f4CAf39/f4B/f3+AgH9/f3+Af39/gA==" type="audio/wav">
</audio>

<script>
function kds() {
    return {
        activeOrders: [],
        completedOrders: [],
        connected: true,
        lastUpdate: '--',
        pollTimer: null,
        prevActiveCount: 0,
        playedSound: false,

        get activeCount() { return this.activeOrders.length; },
        get completedCount() { return this.completedOrders.length; },

        async fetchOrders() {
            try {
                const resp = await fetch('/panel/pos/kds/orders', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!resp.ok) throw new Error('Network error');
                const data = await resp.json();
                this.connected = true;

                // Sound alert on new orders
                const newCount = data.active?.length || 0;
                if (newCount > this.prevActiveCount && !this.playedSound) {
                    try {
                        document.getElementById('new-order-sound')?.play().catch(() => {});
                    } catch(e) {}
                }
                this.prevActiveCount = newCount;

                this.activeOrders = data.active || [];
                this.completedOrders = data.completed || [];
                this.lastUpdate = new Date().toLocaleTimeString();
            } catch (e) {
                this.connected = false;
            }
        },

        startPolling() {
            this.fetchOrders();
            this.pollTimer = setInterval(() => this.fetchOrders(), 10000);
        },

        async startPreparing(id) {
            try {
                await fetch('/panel/pos/kds/' + id + '/prepare', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                this.fetchOrders();
            } catch(e) {}
        },

        async markReady(id) {
            try {
                await fetch('/panel/pos/kds/' + id + '/ready', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                this.fetchOrders();
            } catch(e) {}
        },

        async recallOrder(id) {
            try {
                await fetch('/panel/pos/kds/' + id + '/recall', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                this.fetchOrders();
            } catch(e) {}
        }
    };
}
</script>

</body>
</html>
