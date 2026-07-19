@extends('portal.guest-app-layout')
@section('title', 'Room Service')
@section('content')

<h1 class="text-xl font-bold text-stone-900 mb-2">Room Service</h1>
<p class="text-sm text-stone-500 mb-6">Pesan makanan &amp; minuman langsung ke kamar Anda</p>

@if (!$activeStay)
    <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 text-sm mb-6">
        Anda harus check-in terlebih dahulu untuk memesan room service.
    </div>
@else
    <form method="POST" action="{{ route('customer.guest.room-service.order') }}" id="roomServiceForm">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Menu Categories & Items --}}
            <div class="md:col-span-2 space-y-6">
                @foreach($categories as $category)
                    @if($category->items->isNotEmpty())
                    <div>
                        <h3 class="text-sm font-semibold text-stone-500 uppercase tracking-wide mb-3">{{ $category->name }}</h3>
                        <div class="space-y-2">
                            @foreach($category->items as $item)
                                <div class="bg-white rounded-xl p-3 border border-stone-100 shadow-sm flex items-center justify-between gap-3"
                                     x-data="{ qty: 0 }">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-stone-900 text-sm truncate">{{ $item->name }}</p>
                                        @if($item->description)
                                            <p class="text-xs text-stone-400 truncate">{{ $item->description }}</p>
                                        @endif
                                        <p class="text-sm font-semibold text-stone-700 mt-1">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" @click="qty = Math.max(0, qty - 1)"
                                                class="w-7 h-7 rounded-lg bg-stone-100 hover:bg-stone-200 flex items-center justify-center text-stone-600 font-bold text-sm transition">−</button>
                                        <span class="w-6 text-center text-sm font-semibold text-stone-900" x-text="qty">0</span>
                                        <button type="button" @click="qty = Math.min(20, qty + 1)"
                                                class="w-7 h-7 rounded-lg bg-indigo-100 hover:bg-indigo-200 flex items-center justify-center text-indigo-600 font-bold text-sm transition">+</button>
                                    </div>
                                    <input type="hidden" name="items[][menu_item_id]" value="{{ $item->id }}" x-bind:disabled="qty === 0">
                                    <input type="hidden" name="items[][qty]" x-bind:value="qty" x-bind:disabled="qty === 0">
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach

                @if($categories->isEmpty() || $categories->every(fn($c) => $c->items->isEmpty()))
                    <div class="bg-white rounded-2xl p-8 text-center border border-stone-100">
                        <p class="text-sm text-stone-400">Menu belum tersedia.</p>
                    </div>
                @endif
            </div>

            {{-- Order Summary (Cart) --}}
            <div class="md:col-span-1">
                <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-4 sticky top-20">
                    <h3 class="text-sm font-semibold text-stone-900 mb-3">Pesanan Anda</h3>
                    <div class="text-xs text-stone-400 mb-3">Pilih item dari menu</div>

                    <div class="mb-4">
                        <label class="block text-xs font-medium text-stone-500 mb-1">Catatan Khusus</label>
                        <textarea name="notes" rows="2" placeholder="cth: Tidak pedas, tanpa gula..."
                                  class="w-full text-sm border border-stone-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    @if ($activeStay->room)
                    <div class="bg-stone-50 rounded-xl p-3 mb-4 text-sm">
                        <p class="text-stone-500 text-xs">Diantar ke</p>
                        <p class="font-semibold text-stone-900">Kamar {{ $activeStay->room->number }}, Lt. {{ $activeStay->room->floor }}</p>
                    </div>
                    @endif

                    <button type="submit"
                            class="w-full bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-semibold py-2.5 rounded-xl text-sm transition shadow-md shadow-indigo-500/30">
                        Pesan Sekarang
                    </button>
                </div>
            </div>
        </div>
    </form>
@endif

@push('head')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js" defer></script>
@endpush

@endsection
