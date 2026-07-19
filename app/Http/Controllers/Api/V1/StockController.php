<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function items(Request $request)
    {
        $property = $request->user()->property;
        $items = StockItem::where('property_id', $property->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        return response()->json($items);
    }

    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'code'          => 'required|string|max:50',
            'name'          => 'required|string|max:200',
            'unit'          => 'required|string|max:20',
            'category'      => 'nullable|string',
            'reorder_point' => 'nullable|numeric|min:0',
        ]);

        $item = StockItem::create([
            ...$data,
            'property_id' => $request->user()->property->id,
            'current_qty' => 0,
            'average_cost'=> 0,
            'is_active'   => true,
        ]);

        return response()->json($item, 201);
    }

    public function movements(Request $request, int $itemId)
    {
        $property = $request->user()->property;
        $item = StockItem::where('property_id', $property->id)->findOrFail($itemId);
        $movements = $item->movements()
            ->with('performedBy')
            ->orderByDesc('moved_at')
            ->paginate(30);
        return response()->json($movements);
    }

    public function addMovement(Request $request, int $itemId)
    {
        $property = $request->user()->property;
        $item = StockItem::where('property_id', $property->id)->findOrFail($itemId);

        $data = $request->validate([
            'type'      => 'required|in:in,out,adjustment,waste',
            'qty'       => 'required|numeric',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes'     => 'nullable|string',
        ]);

        $movement = StockMovement::create([
            'stock_item_id'       => $item->id,
            'type'                => $data['type'],
            'qty'                 => $data['qty'],
            'unit_cost'           => $data['unit_cost'] ?? $item->average_cost,
            'notes'               => $data['notes'] ?? null,
            'performed_by_user_id'=> $request->user()->id,
            'moved_at'            => now(),
        ]);

        // Recalculate running stock
        $delta = in_array($data['type'], ['in']) ? $data['qty'] : -abs($data['qty']);
        $item->increment('current_qty', $delta);

        return response()->json($movement, 201);
    }
}
