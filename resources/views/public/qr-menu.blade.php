<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>{{ $outlet->name }} — Table {{ $table->label }}</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        body { overscroll-behavior: none; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="qrMenu()">
<div class="max-w-md mx-auto pb-24">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-primary-600 to-primary-700 text-white px-5 py-5 sticky top-0 z-10 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-white/70">Welcome to</p>
                <h1 class="text-xl font-bold">{{ $outlet->name }}</h1>
            </div>
            <div class="text-right">
                <p class="text-xs text-white/70">Table</p>
                <p class="text-lg font-bold">{{ $table->label }}</p>
            </div>
        </div>
    </div>

    {{-- Cart summary (sticky) --}}
    <div x-show="cart.length > 0" class="sticky top-[100px] z-10 bg-white shadow-md border-b border-gray-200 px-4 py-3 flex items-center justify-between text-sm"
         x-transition>
        <span><strong x-text="cartCount"></strong> items · Rp <span x-text="formatPrice(cartTotal)"></span></span>
        <button @click="showCart = true" class="bg-primary-600 text-white px-4 py-1.5 rounded-full text-xs font-semibold">
            View Cart
        </button>
    </div>

    {{-- Category tabs --}}
    <div class="flex overflow-x-auto gap-2 px-4 py-3 bg-white border-b border-gray-100 sticky top-[60px] z-10 scrollbar-hide">
        <button @click="activeCategory = ''" :class="activeCategory === '' ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-600'"
                class="shrink-0 px-3 py-1.5 rounded-full text-xs font-medium">All</button>
        @foreach ($categories as $cat)
        <button @click="activeCategory = '{{ $cat->name }}'" :class="activeCategory === '{{ $cat->name }}' ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-600'"
                class="shrink-0 px-3 py-1.5 rounded-full text-xs font-medium">{{ $cat->name }}</button>
        @endforeach
    </div>

    {{-- Menu grid --}}
    <div class="px-4 py-3 space-y-6">
        @foreach ($groupedItems as $catName => $items)
        <div x-show="activeCategory === '' || activeCategory === '{{ $catName }}'">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3">{{ $catName }}</h2>
            <div class="space-y-3">
                @foreach ($items as $item)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex">
                    @if (!empty($item->photos))
                    <div class="w-24 h-24 shrink-0 bg-gray-100 flex items-center justify-center text-gray-300 text-xs">
                        📷
                    </div>
                    @endif
                    <div class="flex-1 p-3 flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">{{ $item->name }}</h3>
                            @if ($item->description)
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $item->description }}</p>
                            @endif
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-sm font-bold text-primary-700">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                            <button @click="addItem({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->price }})"
                                    class="bg-primary-600 text-white w-8 h-8 rounded-full flex items-center justify-center text-lg font-bold shadow-sm hover:bg-primary-700 transition-colors leading-none">
                                +
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        @if (empty($groupedItems))
        <div class="py-16 text-center text-gray-400">
            <p class="text-sm font-medium">Menu is currently unavailable</p>
        </div>
        @endif
    </div>
</div>

{{-- Cart sidebar modal --}}
<div x-show="showCart" @click.self="showCart = false"
     class="fixed inset-0 z-50 bg-black/50 flex items-end sm:items-center justify-center"
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <div class="bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl p-5 max-h-[80vh] overflow-y-auto"
         @click.stop>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">Your Order</h2>
            <button @click="showCart = false" class="text-gray-400 hover:text-gray-600 p-1">✕</button>
        </div>

        <template x-if="cart.length === 0">
            <div class="py-8 text-center text-gray-400">Cart is empty</div>
        </template>

        <div class="space-y-2">
            <template x-for="(item, idx) in cart" :key="idx">
                <div class="flex items-center gap-3 py-2 border-b border-gray-50">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900" x-text="item.name"></p>
                        <p class="text-xs text-gray-500" x-text="item.notes || ''"></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="decrementItem(idx)" class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-sm">−</button>
                        <span class="text-sm font-medium w-5 text-center" x-text="item.qty"></span>
                        <button @click="incrementItem(idx)" class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-sm">+</button>
                    </div>
                    <span class="text-sm font-semibold text-gray-900 w-20 text-right" x-text="formatPrice(item.price * item.qty)"></span>
                </div>
            </template>
        </div>

        <div class="border-t border-gray-100 mt-4 pt-4 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-semibold" x-text="'Rp ' + formatPrice(cartTotal)"></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Service (10%)</span>
                <span class="font-semibold" x-text="'Rp ' + formatPrice(serviceCharge)"></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Tax (11%)</span>
                <span class="font-semibold" x-text="'Rp ' + formatPrice(taxTotal)"></span>
            </div>
            <div class="flex justify-between text-base font-bold pt-2 border-t border-gray-100">
                <span class="text-gray-900">Total</span>
                <span class="text-primary-700" x-text="'Rp ' + formatPrice(grandTotal)"></span>
            </div>
        </div>

        <div class="mt-5 space-y-2">
            <button @click="placeOrder()"
                    x-bind:disabled="submitting || cart.length === 0"
                    x-text="submitting ? 'Placing order...' : 'Place Order'"
                    class="w-full bg-primary-600 text-white py-3 rounded-2xl font-semibold text-sm disabled:opacity-50 shadow-sm hover:bg-primary-700 transition-colors">
            </button>
        </div>
    </div>
</div>

{{-- Order confirmation modal --}}
<div x-show="orderPlaced" @click.self="orderPlaced = false"
     class="fixed inset-0 z-[60] bg-black/50 flex items-center justify-center px-4">
    <div class="bg-white rounded-3xl p-6 max-w-sm w-full text-center shadow-2xl">
        <div class="w-14 h-14 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">Order Placed!</h3>
        <p class="text-sm text-gray-500">Order #<span x-text="lastOrderNo"></span></p>
        <p class="text-xs text-gray-400 mt-2">We will serve your order shortly.</p>
        <button @click="orderPlaced = false; showCart = false; cart = []"
                class="mt-4 bg-primary-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold shadow-sm">
            OK
        </button>
    </div>
</div>

<script>
function qrMenu() {
    return {
        cart: [],
        showCart: false,
        activeCategory: '',
        submitting: false,
        orderPlaced: false,
        lastOrderNo: '',

        get cartCount() { return this.cart.reduce((s, i) => s + i.qty, 0); },
        get cartTotal() { return this.cart.reduce((s, i) => s + (i.price * i.qty), 0); },
        get serviceCharge() { return Math.round(this.cartTotal * 0.10); },
        get taxTotal() { return Math.round((this.cartTotal + this.serviceCharge) * 0.11); },
        get grandTotal() { return this.cartTotal + this.serviceCharge + this.taxTotal; },

        addItem(id, name, price) {
            const existing = this.cart.find(i => i.menu_id === id);
            if (existing) {
                existing.qty++;
            } else {
                this.cart.push({ menu_id: id, name: name, price: price, qty: 1, notes: '' });
            }
        },
        incrementItem(idx) { this.cart[idx].qty++; },
        decrementItem(idx) {
            if (this.cart[idx].qty > 1) {
                this.cart[idx].qty--;
            } else {
                this.cart.splice(idx, 1);
            }
        },
        formatPrice(v) { return new Intl.NumberFormat('id-ID').format(v || 0); },

        async placeOrder() {
            if (this.cart.length === 0) return;
            this.submitting = true;
            try {
                const resp = await fetch('/menu/order', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        outlet_id: {{ $outlet->id }},
                        table_id: {{ $table->id }},
                        items: this.cart.map(i => ({ menu_id: i.menu_id, qty: i.qty, notes: i.notes })),
                    }),
                });
                const data = await resp.json();
                if (data.ok) {
                    this.lastOrderNo = data.order_no;
                    this.orderPlaced = true;
                }
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>

</body>
</html>
