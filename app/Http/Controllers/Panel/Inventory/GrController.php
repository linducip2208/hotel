<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Inventory;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptLine;
use App\Models\PurchaseOrder;
use App\Models\StockItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

final class GrController extends Controller
{
    public function index()
    {
        $grs = GoodsReceipt::where('property_id', app('current_property')->id)
            ->with('purchaseOrder.vendor', 'receiver')
            ->orderByDesc('id')->paginate(50);
        return view('panel.inventory.gr.index', compact('grs'));
    }

    public function create(Request $request)
    {
        $poId = $request->query('po_id');
        $po = $poId ? PurchaseOrder::where('property_id', app('current_property')->id)->with('lines.stockItem')->find($poId) : null;
        return view('panel.inventory.gr.create', compact('po'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'po_id' => 'required|integer|exists:purchase_orders,id',
            'received_date' => 'required|date',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.stock_item_id' => 'nullable|integer',
            'lines.*.quantity_received' => 'required|numeric|min:0',
        ]);

        $gr = GoodsReceipt::create([
            'property_id' => app('current_property')->id,
            'gr_number' => 'GR-'.now()->format('Ym').'-'.Str::upper(Str::random(4)),
            'po_id' => $data['po_id'],
            'received_by' => $request->user()?->id,
            'received_date' => $data['received_date'],
            'notes' => $data['notes'],
            'status' => 'pending',
        ]);

        foreach ($data['lines'] as $line) {
            GoodsReceiptLine::create([
                'gr_id' => $gr->id,
                'stock_item_id' => $line['stock_item_id'] ?? null,
                'quantity_received' => $line['quantity_received'],
                'quantity_accepted' => $line['quantity_accepted'] ?? $line['quantity_received'],
                'notes' => $line['notes'] ?? null,
            ]);
        }

        return redirect()->route('panel.inventory.gr.index')->with('success', 'GR created: '.$gr->gr_number);
    }

    public function show(int $id)
    {
        $gr = GoodsReceipt::where('property_id', app('current_property')->id)
            ->with('lines.stockItem', 'purchaseOrder.vendor', 'receiver')->findOrFail($id);
        return view('panel.inventory.gr.show', compact('gr'));
    }

    /** Accept goods receipt and auto-update stock quantities. */
    public function accept(Request $request, int $id)
    {
        $gr = GoodsReceipt::where('property_id', app('current_property')->id)
            ->with('lines')->findOrFail($id);

        if ($gr->status !== 'pending') {
            return back()->with('error', 'GR already processed.');
        }

        DB::transaction(function () use ($gr, $request) {
            foreach ($gr->lines as $line) {
                $qty = $line->quantity_accepted > 0 ? $line->quantity_accepted : $line->quantity_received;

                if ($line->stock_item_id) {
                    StockItem::where('id', $line->stock_item_id)->increment('current_qty', $qty);

                    // Record movement
                    \App\Models\StockMovement::create([
                        'stock_item_id' => $line->stock_item_id,
                        'movement_type' => 'in',
                        'qty' => $qty,
                        'reference_type' => 'goods_receipt',
                        'reference_id' => $gr->id,
                        'notes' => "GR {$gr->gr_number}",
                        'performed_by_user_id' => $request->user()?->id,
                        'moved_at' => now(),
                    ]);
                }

                $line->update(['quantity_accepted' => $qty]);
            }

            $gr->update(['status' => 'accepted']);
        });

        return back()->with('success', 'Goods receipt accepted. Stock updated.');
    }
}
