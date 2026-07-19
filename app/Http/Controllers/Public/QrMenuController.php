<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PosMenuItem;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOutlet;
use App\Models\PosTable;
use App\Services\Accounting\PpnCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QrMenuController extends Controller
{
    public function show(int $outletId, int $tableId)
    {
        $outlet = PosOutlet::findOrFail($outletId);
        $table = PosTable::where('outlet_id', $outletId)->findOrFail($tableId);

        $categories = $outlet->categories()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $menuItems = PosMenuItem::with('category')
            ->where('outlet_id', $outletId)
            ->where('is_available', true)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $groupedItems = [];
        foreach ($menuItems as $item) {
            $catName = $item->category?->name ?? 'Other';
            $groupedItems[$catName][] = $item;
        }

        return view('public.qr-menu', compact('outlet', 'table', 'categories', 'groupedItems'));
    }

    public function placeOrder(Request $request, PpnCalculator $ppn)
    {
        $data = $request->validate([
            'outlet_id' => 'required|integer',
            'table_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        // Verify outlet/table
        $outlet = PosOutlet::findOrFail($data['outlet_id']);
        PosTable::where('outlet_id', $outlet->id)->findOrFail($data['table_id']);

        $order = DB::transaction(function () use ($data, $outlet) {
            $orderNo = 'QR-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));

            $order = PosOrder::create([
                'outlet_id' => $outlet->id,
                'property_id' => $outlet->property_id,
                'table_id' => $data['table_id'],
                'order_no' => $orderNo,
                'type' => 'dine_in',
                'status' => 'open',
            ]);

            $subtotal = 0;
            foreach ($data['items'] as $i) {
                $menu = PosMenuItem::where('outlet_id', $outlet->id)
                    ->where('is_active', true)
                    ->findOrFail($i['menu_id']);

                $lineSubtotal = $menu->price * $i['qty'];
                PosOrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menu->id,
                    'name' => $menu->name,
                    'unit_price' => $menu->price,
                    'qty' => $i['qty'],
                    'subtotal' => $lineSubtotal,
                    'modifiers' => !empty($i['notes']) ? ['notes' => $i['notes']] : null,
                ]);
                $subtotal += $lineSubtotal;
            }

            $service = round($subtotal * 0.10, 2);
            $tax = app(PpnCalculator::class)->calculate($subtotal + $service);

            $order->update([
                'subtotal' => $subtotal,
                'service_charge' => $service,
                'tax_total' => $tax,
                'grand_total' => $subtotal + $service + $tax,
            ]);

            return $order->load('items');
        });

        return response()->json([
            'ok' => true,
            'order_no' => $order->order_no,
            'status' => $order->status,
            'message' => 'Order placed! We will serve shortly.',
        ]);
    }
}
