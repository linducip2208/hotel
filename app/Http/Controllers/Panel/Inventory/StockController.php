<?php

namespace App\Http\Controllers\Panel\Inventory;

use App\Http\Controllers\Controller;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $items = StockItem::where('property_id', app('current_property')->id)->paginate(50);
        return view('panel.inventory.index', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => 'required|string|max:32',
            'name' => 'required|string',
            'category' => 'required|string',
            'uom' => 'nullable|string',
            'reorder_point' => 'nullable|numeric',
        ]);
        StockItem::create($data + ['property_id' => app('current_property')->id]);
        return back();
    }

    public function recordMovement(Request $request)
    {
        $data = $request->validate([
            'stock_item_id' => 'required|integer',
            'movement_type' => 'required|in:in,out,adjust,transfer',
            'qty' => 'required|numeric',
            'unit_cost' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);
        StockMovement::create($data + ['performed_by_user_id' => $request->user()?->id]);
        $sign = in_array($data['movement_type'], ['out', 'transfer']) ? -1 : 1;
        StockItem::where('id', $data['stock_item_id'])->update([
            'current_qty' => \DB::raw("current_qty + ".($sign * $data['qty'])),
        ]);
        return back();
    }
}
