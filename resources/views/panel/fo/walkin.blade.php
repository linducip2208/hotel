@extends('panel.layout')
@section('title', 'Walk-in Check-in')
@section('content')
<style>
    .walkin-room-card {
        transition: all 0.15s ease;
    }
    .walkin-room-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,.1);
    }
    .walkin-room-card.selected {
        border-color: #6366f1;
        background: #eef2ff;
        box-shadow: 0 0 0 2px rgba(99,102,241,.3);
    }
    .walkin-room-card.occupied {
        opacity: .55;
        cursor: not-allowed;
    }
    .walkin-room-card.dirty {
        opacity: .45;
        cursor: not-allowed;
    }
    .cart-item {
        animation: slideIn .2s ease;
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(10px); }
        to { opacity: 1; transform: translateX(0); }
    }
</style>

<div id="walkin-app" class="flex flex-col h-[calc(100vh-8rem)] -mx-4 lg:-mx-6 -mb-4 lg:-mb-6">
    {{-- Top toolbar --}}
    <div class="flex items-center gap-3 px-3 lg:px-6 py-3 border-b border-gray-100 bg-white shrink-0">
        <a href="{{ route('panel.fo.reservations.index') }}"
           class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-200 text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <h1 class="text-base lg:text-lg font-bold text-gray-900 truncate">Walk-in Check-in</h1>
        <span class="hidden sm:inline text-xs text-gray-400 bg-gray-100 rounded-full px-2.5 py-0.5 font-medium">POS</span>

        <div class="flex-1"></div>

        {{-- Cart badge (always visible) --}}
        <div class="flex items-center gap-2 text-sm">
            <span class="text-gray-500"><b id="cartCount">0</b> <span class="hidden sm:inline">kamar</span></span>
            <span class="font-bold text-indigo-600" id="cartTotal">Rp 0</span>
        </div>

        {{-- Mobile cart toggle --}}
        <button onclick="toggleCart()" id="cartToggle" class="md:hidden inline-flex items-center gap-1 bg-indigo-600 text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
            Keranjang
        </button>
    </div>

    {{-- Tab bar --}}
    <div class="flex items-center gap-1 px-4 lg:px-6 py-2 border-b border-gray-100 bg-gray-50/80 shrink-0">
        <button onclick="switchTab('available')" id="tabAvail" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-white shadow-sm text-gray-900">Kamar Tersedia <span class="ml-1 text-gray-400" id="availCount">0</span></button>
        <button onclick="switchTab('all')" id="tabAll" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-gray-500 hover:text-gray-700">Semua Kamar</button>
        <div class="flex-1"></div>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" oninput="filterRooms()" id="searchInput" placeholder="Cari nomor kamar..." class="w-48 rounded-lg border border-gray-200 bg-white pl-8 pr-3 py-1.5 text-xs focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none">
        </div>
    </div>

    {{-- Main grid: rooms + cart --}}
    <div class="flex-1 flex flex-col md:flex-row overflow-hidden">
        {{-- LEFT: Room Grid --}}
        <div class="flex-1 overflow-y-auto p-3 lg:p-4" style="scrollbar-width:thin">
            <div class="flex items-center gap-1.5 mb-4 flex-wrap" id="typeFilters"></div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 lg:gap-2.5" id="roomGrid"></div>
        </div>

        {{-- RIGHT: Cart Panel — hidden on mobile, shown as overlay when toggled --}}
        <div id="cartPanel" class="hidden md:flex md:w-80 xl:w-96 border-l border-gray-200 bg-white flex-col shrink-0
                    md:relative fixed inset-0 z-50 md:z-auto">
            {{-- Mobile close button --}}
            <button onclick="toggleCart()" class="md:hidden absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center z-10">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div class="px-4 py-3 border-b border-gray-100 font-semibold text-sm text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                Keranjang <span id="cartBadge" class="text-xs bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded-full ml-1 hidden">0</span>
            </div>

            <div id="cartItems" class="flex-1 overflow-y-auto p-3 space-y-2 text-sm">
                <div class="text-center py-12 text-gray-400">
                    <div class="text-4xl mb-2">🛒</div>
                    <p class="text-sm">Pilih kamar dari grid</p>
                    <p class="text-xs mt-1">Klik kartu kamar hijau</p>
                </div>
            </div>

            <div id="cartForm" class="border-t border-gray-100 p-4 space-y-3 hidden">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Tamu *</label>
                    <input type="text" id="guestName" placeholder="Nama lengkap" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Telepon</label>
                        <input type="text" id="guestPhone" placeholder="08xxx" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Check-out *</label>
                        <input type="date" id="checkOut" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Dewasa</label>
                        <select id="adults" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 outline-none">
                            <option value="1">1 Dewasa</option>
                            <option value="2" selected>2 Dewasa</option>
                            <option value="3">3 Dewasa</option>
                            <option value="4">4 Dewasa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Pembayaran</label>
                        <select id="paymentMethod" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 outline-none">
                            <option value="cash">Cash</option>
                            <option value="card">Kartu</option>
                            <option value="qris">QRIS</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Jumlah Bayar</label>
                    <input type="number" id="paymentAmount" placeholder="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Catatan</label>
                    <input type="text" id="notes" placeholder="Opsional" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 outline-none">
                </div>
                <button onclick="submitWalkin()" id="submitBtn"
                        class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-bold text-sm rounded-xl py-3 shadow-lg shadow-emerald-500/30 transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    CHECK-IN SEKARANG
                </button>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div id="toast" class="hidden fixed bottom-6 right-6 bg-emerald-600 text-white rounded-xl px-5 py-3 shadow-2xl flex items-center gap-3 z-50 animate-pulse">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <span id="toastMsg"></span>
    </div>
</div>

<script>
// ---- DATA ----
const availableRooms = {!! \Illuminate\Support\Js::from($availableRooms->map(fn($r) => ['id' => $r->id, 'number' => $r->number, 'floor' => $r->floor, 'fo_status' => $r->fo_status, 'hk_status' => $r->hk_status, 'room_type_id' => $r->room_type_id, 'room_type' => $r->roomType ? ['name' => $r->roomType->name, 'base_rate' => $r->roomType->base_rate, 'max_occupancy' => $r->roomType->max_occupancy] : null])) !!};
const occupiedRooms = {!! \Illuminate\Support\Js::from($occupiedRooms->map(fn($r) => [
    'id' => $r->id, 
    'number' => $r->number, 
    'floor' => $r->floor, 
    'fo_status' => $r->fo_status, 
    'hk_status' => $r->hk_status, 
    'room_type_id' => $r->room_type_id, 
    'room_type' => $r->roomType ? ['name' => $r->roomType->name, 'base_rate' => $r->roomType->base_rate, 'max_occupancy' => $r->roomType->max_occupancy] : null, 
    'current_guest' => optional(optional($r->reservationRooms->first())->reservation)->primaryGuest?->full_name ?? 'Ditempati'
])) !!};

// ---- STATE ----
let state = {
    tab: 'available',
    search: '',
    typeFilter: 'all',
    cart: [],
    guestName: '',
    guestPhone: '',
    guestEmail: '',
    checkOut: '',
    adults: 2,
    paymentMethod: 'cash',
    paymentAmount: 0,
    notes: '',
    submitting: false,
};

// ---- INIT ----
function init() {
    document.getElementById('checkOut').value = new Date(Date.now() + 86400000).toISOString().slice(0,10);
    document.getElementById('availCount').textContent = availableRooms.length;
    renderTypeFilters();
    renderRooms();
}

// ---- TAB ----
function switchTab(tab) {
    state.tab = tab;
    document.getElementById('tabAvail').className = tab === 'available' ? 'px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-white shadow-sm text-gray-900' : 'px-3.5 py-1.5 rounded-lg text-xs font-semibold text-gray-500 hover:text-gray-700';
    document.getElementById('tabAll').className = tab === 'all' ? 'px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-white shadow-sm text-gray-900' : 'px-3.5 py-1.5 rounded-lg text-xs font-semibold text-gray-500 hover:text-gray-700';
    renderRooms();
}

// ---- TYPE FILTER ----
function renderTypeFilters() {
    let html = '<button onclick="filterByType(\'all\')" class="px-3 py-1 rounded-full text-xs font-medium ' + (state.typeFilter === 'all' ? 'bg-indigo-100 text-indigo-700' : 'bg-white border border-gray-200 text-gray-500') + '">Semua Tipe</button>';
    roomTypes.forEach(rt => {
        let cnt = availableRooms.filter(r => r.room_type_id === rt.id).length;
        html += '<button onclick="filterByType(\'' + rt.id + '\')" class="px-3 py-1 rounded-full text-xs font-medium border ' + (state.typeFilter == rt.id ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-white border-gray-200 text-gray-500') + '">' + rt.name + ' (' + cnt + ')</button>';
    });
    document.getElementById('typeFilters').innerHTML = html;
}

function filterByType(id) {
    state.typeFilter = state.typeFilter == id ? 'all' : id;
    renderTypeFilters();
    renderRooms();
}

// ---- FILTER ----
function getFilteredRooms() {
    let rooms = state.tab === 'available' ? availableRooms : [...availableRooms, ...occupiedRooms];
    if (state.typeFilter !== 'all') rooms = rooms.filter(r => r.room_type_id == state.typeFilter);
    if (state.search) rooms = rooms.filter(r => String(r.number).includes(state.search.toLowerCase()));
    return rooms;
}

function filterRooms() {
    state.search = document.getElementById('searchInput').value;
    renderRooms();
}

// ---- RENDER ROOMS ----
function renderRooms() {
    let rooms = getFilteredRooms();
    let html = '';
    rooms.forEach(room => {
        let inCart = state.cart.some(r => r.id === room.id);
        let isAvailable = room.fo_status === 'vacant' && ['clean','inspected'].includes(room.hk_status);
        let cardClass = 'walkin-room-card relative rounded-xl border-2 p-3 cursor-pointer ';
        if (inCart) cardClass += 'selected';
        else if (!isAvailable) cardClass += 'occupied';
        else cardClass += 'border-green-200 bg-green-50/60';

        let statusLabel = room.fo_status === 'occupied' ? 'Ditempati' : room.fo_status === 'reserved' ? 'Dipesan' : room.hk_status === 'dirty' ? 'Kotor' : room.hk_status === 'out_of_order' ? 'Rusak' : 'Tersedia';
        let statusColor = !isAvailable ? 'text-red-400' : 'text-green-600';
        let numColor = !isAvailable ? 'text-red-400' : 'text-green-700';

        html += '<div onclick="toggleRoom(' + room.id + ')" class="' + cardClass + '">';
        if (inCart) html += '<div class="absolute top-2 right-2 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center shadow-sm"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>';
        html += '<div class="text-base font-extrabold ' + numColor + '">' + room.number + '</div>';
        html += '<div class="text-[10px] font-medium mt-0.5 truncate text-gray-500">' + (room.room_type?.name || '—') + '</div>';
        html += '<div class="text-xs font-bold mt-1.5 text-indigo-600">Rp ' + Number(room.room_type?.base_rate || 0).toLocaleString('id-ID') + '</div>';
        html += '<div class="text-[9px] mt-0.5 ' + statusColor + '">' + statusLabel + '</div>';
        if (!isAvailable && room.current_guest) {
            html += '<div class="mt-1.5 pt-1.5 border-t border-gray-100"><div class="text-[9px] text-gray-400 truncate">' + room.current_guest + '</div></div>';
        }
        html += '</div>';
    });

    if (rooms.length === 0) {
        html = '<div class="col-span-full py-16 text-center"><div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg></div><p class="text-sm text-gray-500">Tidak ada kamar</p></div>';
    }

    document.getElementById('roomGrid').innerHTML = html;
    renderCart();
}

// ---- CART ----
function toggleRoom(roomId) {
    let room = [...availableRooms, ...occupiedRooms].find(r => r.id === roomId);
    if (!room) return;
    if (room.fo_status !== 'vacant' || !['clean','inspected'].includes(room.hk_status)) return;

    let idx = state.cart.findIndex(r => r.id === roomId);
    if (idx >= 0) state.cart.splice(idx, 1);
    else state.cart.push(room);
    renderRooms();
}

function removeFromCart(roomId) {
    state.cart = state.cart.filter(r => r.id !== roomId);
    renderRooms();
}

function renderCart() {
    let itemsDiv = document.getElementById('cartItems');
    let formDiv = document.getElementById('cartForm');
    let badge = document.getElementById('cartBadge');

    if (state.cart.length === 0) {
        itemsDiv.innerHTML = '<div class="text-center py-12 text-gray-400"><div class="text-4xl mb-2">🛒</div><p class="text-sm">Pilih kamar dari grid</p><p class="text-xs mt-1">Klik kartu kamar hijau</p></div>';
        formDiv.classList.add('hidden');
        badge.classList.add('hidden');
    } else {
        let nights = Math.max(1, Math.floor((new Date(document.getElementById('checkOut').value || Date.now() + 86400000) - new Date()) / 86400000));
        let total = 0;
        let html = '';
        state.cart.forEach(room => {
            let rate = room.room_type?.base_rate || 0;
            let subtotal = rate * nights;
            total += subtotal;
            html += '<div class="cart-item flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">';
            html += '<div><div class="font-semibold text-gray-800">#' + room.number + ' <span class="text-xs text-gray-500">' + (room.room_type?.name || '') + '</span></div><div class="text-xs text-gray-500">' + nights + ' malam × Rp ' + rate.toLocaleString('id-ID') + '</div></div>';
            html += '<div class="flex items-center gap-2"><span class="text-sm font-bold text-indigo-600">Rp ' + subtotal.toLocaleString('id-ID') + '</span><button onclick="removeFromCart(' + room.id + ')" class="text-gray-400 hover:text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button></div>';
            html += '</div>';
        });
        html += '<div class="flex justify-between items-center pt-2 border-t border-gray-200 mt-2"><span class="font-bold text-gray-700">Total</span><span class="font-bold text-lg text-indigo-700">Rp ' + total.toLocaleString('id-ID') + '</span></div>';
        itemsDiv.innerHTML = html;
        formDiv.classList.remove('hidden');
        badge.classList.remove('hidden');
        badge.textContent = state.cart.length;
    }

    document.getElementById('cartCount').textContent = state.cart.length;
    let nights = Math.max(1, Math.floor((new Date(document.getElementById('checkOut').value || Date.now() + 86400000) - new Date()) / 86400000));
    let total = state.cart.reduce((sum, r) => sum + (r.room_type?.base_rate || 0) * nights, 0);
    document.getElementById('cartTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');

    if (total > 0) document.getElementById('paymentAmount').placeholder = total;
}

// ---- SUBMIT ----
async function submitWalkin() {
    if (state.cart.length === 0) return alert('Pilih minimal 1 kamar.');
    let name = document.getElementById('guestName').value.trim();
    if (!name) return alert('Nama tamu wajib diisi.');
    if (state.submitting) return;

    state.submitting = true;
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Memproses...';

    let checkOut = document.getElementById('checkOut').value || new Date(Date.now() + 86400000).toISOString().slice(0,10);

    try {
        let csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
        let res = await fetch('{{ route('panel.fo.walkin.register') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({
                room_ids: state.cart.map(r => r.id),
                guest_name: name,
                guest_phone: document.getElementById('guestPhone').value,
                guest_email: '',
                check_out: checkOut,
                adults: parseInt(document.getElementById('adults').value) || 2,
                payment_method: document.getElementById('paymentMethod').value,
                payment_amount: parseInt(document.getElementById('paymentAmount').value) || 0,
                notes: document.getElementById('notes').value,
            }),
        });
        let data = await res.json();
        if (data.success) {
            showToast('✅ ' + data.message);
            state.cart = [];
            state.guestName = '';
            document.getElementById('guestName').value = '';
            document.getElementById('guestPhone').value = '';
            document.getElementById('paymentAmount').value = '';
            document.getElementById('notes').value = '';
            setTimeout(() => { if (data.redirect) window.location = data.redirect; }, 1500);
        } else {
            alert(data.message || 'Gagal check-in.');
        }
    } catch (e) {
        alert('Gagal terhubung ke server.');
    } finally {
        state.submitting = false;
        document.getElementById('submitBtn').disabled = false;
        document.getElementById('submitBtn').innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>CHECK-IN SEKARANG';
        renderRooms();
    }
}

function showToast(msg) {
    let t = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    t.classList.remove('hidden');
    setTimeout(() => t.classList.add('hidden'), 4000);
}

// ---- START ----
init();

// Mobile cart toggle
function toggleCart() {
    var panel = document.getElementById('cartPanel');
    if (panel.classList.contains('hidden')) {
        panel.classList.remove('hidden');
        panel.classList.add('flex');
    } else if (panel.classList.contains('flex')) {
        panel.classList.add('hidden');
        panel.classList.remove('flex');
    } else {
        panel.classList.remove('hidden');
        panel.classList.add('flex');
    }
}

// Update cart on date change
document.getElementById('checkOut').addEventListener('change', renderCart);
</script>
@endsection
