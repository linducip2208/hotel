<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Inventory;

use App\Http\Controllers\Controller;
use App\Models\ApSupplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class PoController extends Controller
{
    public function index()
    {
        $pos = PurchaseOrder::where('property_id', app('current_property')->id)
            ->with('vendor', 'purchaseRequest', 'orderedBy')
            ->orderByDesc('id')->paginate(50);
        return view('panel.inventory.po.index', compact('pos'));
    }

    public function create(Request $request)
    {
        $suppliers = ApSupplier::where('property_id', app('current_property')->id)->where('is_active', true)->get();
        $prId = $request->query('pr_id');
        $pr = $prId ? PurchaseRequest::where('property_id', app('current_property')->id)->with('lines')->find($prId) : null;
        return view('panel.inventory.po.create', compact('suppliers', 'pr'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vendor_id' => 'required|integer|exists:ap_suppliers,id',
            'pr_id' => 'nullable|integer',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.stock_item_id' => 'nullable|integer',
            'lines.*.description' => 'required|string',
            'lines.*.quantity' => 'required|numeric|min:0.001',
            'lines.*.unit_price' => 'required|numeric|min:0',
        ]);

        $total = 0;
        $lineModels = [];
        foreach ($data['lines'] as $line) {
            $lineTotal = round($line['quantity'] * $line['unit_price'], 2);
            $total += $lineTotal;
            $lineModels[] = new PurchaseOrderLine([
                'stock_item_id' => $line['stock_item_id'] ?? null,
                'description' => $line['description'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'total' => $lineTotal,
            ]);
        }

        $po = PurchaseOrder::create([
            'property_id' => app('current_property')->id,
            'po_number' => 'PO-'.now()->format('Ym').'-'.Str::upper(Str::random(4)),
            'vendor_id' => $data['vendor_id'],
            'pr_id' => $data['pr_id'],
            'ordered_by' => $request->user()?->id,
            'order_date' => $data['order_date'],
            'expected_date' => $data['expected_date'],
            'total' => $total,
            'notes' => $data['notes'],
            'status' => 'draft',
        ]);

        $po->lines()->saveMany($lineModels);

        if ($data['pr_id']) {
            PurchaseRequest::where('id', $data['pr_id'])->update(['status' => 'ordered']);
        }

        return redirect()->route('panel.inventory.po.index')->with('success', 'PO created: '.$po->po_number);
    }

    public function show(int $id)
    {
        $po = PurchaseOrder::where('property_id', app('current_property')->id)
            ->with('lines.stockItem', 'vendor', 'purchaseRequest')->findOrFail($id);
        return view('panel.inventory.po.show', compact('po'));
    }

    public function send(Request $request, int $id)
    {
        $po = PurchaseOrder::where('property_id', app('current_property')->id)->findOrFail($id);
        $po->update(['status' => 'sent']);
        return back()->with('success', 'PO marked as sent.');
    }

    public function generateFromPr(int $prId, Request $request)
    {
        $pr = PurchaseRequest::where('property_id', app('current_property')->id)->with('lines')->findOrFail($prId);

        return redirect()->route('panel.inventory.po.create', ['pr_id' => $prId]);
    }
}
