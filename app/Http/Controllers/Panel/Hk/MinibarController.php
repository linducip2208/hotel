<?php

namespace App\Http\Controllers\Panel\Hk;

use App\Http\Controllers\Controller;
use App\Models\MinibarConsumption;
use App\Models\MinibarProduct;
use App\Models\MinibarStock;
use App\Models\Room;
use App\Services\Hk\MinibarService;
use Illuminate\Http\Request;

class MinibarController extends Controller
{
    public function __construct(protected MinibarService $svc) {}

    public function index()
    {
        $propertyId = app('current_property')->id;
        $products = MinibarProduct::where('property_id', $propertyId)->orderBy('name')->get();
        return view('panel.hk.minibar-products', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:beverage,snack,alcohol,other',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:32',
        ]);

        MinibarProduct::create([
            'property_id' => app('current_property')->id,
            'name' => $request->name,
            'category' => $request->category,
            'selling_price' => $request->selling_price,
            'cost_price' => $request->cost_price ?? 0,
            'sku' => $request->sku,
        ]);

        return back()->with('success', 'Produk minibar berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $product = MinibarProduct::where('property_id', app('current_property')->id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:beverage,snack,alcohol,other',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:32',
            'is_active' => 'boolean',
        ]);

        $product->update($request->only(['name', 'category', 'selling_price', 'cost_price', 'sku', 'is_active']));

        return back()->with('success', 'Produk minibar berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $product = MinibarProduct::where('property_id', app('current_property')->id)->findOrFail($id);
        $product->delete();

        return back()->with('success', 'Produk minibar berhasil dihapus.');
    }

    public function rooms()
    {
        $propertyId = app('current_property')->id;
        $rooms = Room::where('property_id', $propertyId)
            ->where('is_active', true)
            ->with(['roomType', 'minibarStocks' => fn ($q) => $q->with('product')])
            ->orderBy('room_number')
            ->get();
        $products = MinibarProduct::where('property_id', $propertyId)->where('is_active', true)->orderBy('name')->get();
        return view('panel.hk.minibar-rooms', compact('rooms', 'products'));
    }

    public function roomStock($id)
    {
        $propertyId = app('current_property')->id;
        $room = Room::where('property_id', $propertyId)->findOrFail($id);
        $stocks = MinibarStock::where('room_id', $id)->with('product')->get();
        $products = MinibarProduct::where('property_id', $propertyId)->where('is_active', true)->orderBy('name')->get();
        $consumptions = MinibarConsumption::where('room_id', $id)
            ->with(['product', 'reservation.primaryGuest'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('panel.hk.minibar-room-stock', compact('room', 'stocks', 'products', 'consumptions'));
    }

    public function record(Request $request)
    {
        $request->validate([
            'room_id' => 'required|integer|exists:rooms,id',
            'reservation_id' => 'required|integer|exists:reservations,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:minibar_products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        $this->svc->recordConsumption(
            $request->room_id,
            $request->reservation_id,
            $request->items,
            auth()->id()
        );

        return back()->with('success', 'Konsumsi minibar berhasil dicatat dan ditagihkan ke folio.');
    }

    public function restock(Request $request)
    {
        $request->validate([
            'room_id' => 'required|integer|exists:rooms,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:minibar_products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        $this->svc->restock($request->room_id, $request->items);

        return back()->with('success', 'Stok minibar berhasil diisi ulang.');
    }
}
