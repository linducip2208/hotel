<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PosCategory;
use App\Models\PosMenuItem;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\Reservation;
use Illuminate\Http\Request;

class RoomServiceController extends Controller
{
    public function index(Request $request)
    {
        $guest = auth('customer')->user();

        $categories = PosCategory::where('property_id', $guest->property_id)
            ->where('is_active', true)
            ->with(['items' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        $activeStay = Reservation::where('primary_guest_id', $guest->id)
            ->whereIn('status', ['checked_in', 'confirmed'])
            ->whereDate('check_in', '<=', now())
            ->whereDate('check_out', '>=', now())
            ->latest('check_in')
            ->first();

        return view('portal.guest.room-service', compact('guest', 'categories', 'activeStay'));
    }

    public function order(Request $request)
    {
        $guest = auth('customer')->user();

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:pos_menu_items,id',
            'items.*.qty' => 'required|integer|min:1|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        $activeStay = Reservation::where('primary_guest_id', $guest->id)
            ->whereIn('status', ['checked_in', 'confirmed'])
            ->whereDate('check_in', '<=', now())
            ->whereDate('check_out', '>=', now())
            ->latest('check_in')
            ->first();

        $order = PosOrder::create([
            'property_id' => $guest->property_id,
            'reservation_id' => $activeStay?->id,
            'guest_id' => $guest->id,
            'room_id' => $activeStay?->room_id,
            'type' => 'room_service',
            'status' => 'pending',
            'notes' => $request->notes,
            'source' => 'guest_app',
        ]);

        foreach ($request->items as $item) {
            $menuItem = PosMenuItem::findOrFail($item['menu_item_id']);
            PosOrderItem::create([
                'pos_order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'name' => $menuItem->name,
                'qty' => $item['qty'],
                'price' => $menuItem->price,
                'subtotal' => $menuItem->price * $item['qty'],
            ]);
        }

        $order->update([
            'total' => $order->items->sum('subtotal'),
        ]);

        return back()->with('success', 'Pesanan room service berhasil dikirim!');
    }
}
