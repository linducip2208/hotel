<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Inventory;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestLine;
use App\Models\StockItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class PrController extends Controller
{
    public function index()
    {
        $prs = PurchaseRequest::where('property_id', app('current_property')->id)
            ->with('requester', 'lines')
            ->orderByDesc('id')->paginate(50);
        return view('panel.inventory.pr.index', compact('prs'));
    }

    public function create()
    {
        $items = StockItem::where('property_id', app('current_property')->id)->where('is_active', true)->get();
        return view('panel.inventory.pr.create', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'department' => 'nullable|string',
            'required_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.stock_item_id' => 'nullable|integer',
            'lines.*.description' => 'required|string',
            'lines.*.quantity' => 'required|numeric|min:0.001',
            'lines.*.unit' => 'nullable|string',
            'lines.*.estimated_price' => 'nullable|numeric',
        ]);

        $pr = PurchaseRequest::create([
            'property_id' => app('current_property')->id,
            'pr_number' => 'PR-'.now()->format('Ym').'-'.Str::upper(Str::random(5)),
            'requested_by' => $request->user()?->id,
            'department' => $data['department'],
            'required_date' => $data['required_date'],
            'notes' => $data['notes'],
            'status' => 'pending',
        ]);

        foreach ($data['lines'] as $line) {
            PurchaseRequestLine::create([
                'pr_id' => $pr->id,
                'stock_item_id' => $line['stock_item_id'] ?? null,
                'description' => $line['description'],
                'quantity' => $line['quantity'],
                'unit' => $line['unit'] ?? 'pcs',
                'estimated_price' => $line['estimated_price'] ?? null,
            ]);
        }

        return redirect()->route('panel.inventory.pr.index')->with('success', 'PR created: '.$pr->pr_number);
    }

    public function show(int $id)
    {
        $pr = PurchaseRequest::where('property_id', app('current_property')->id)
            ->with('lines.stockItem', 'requester')->findOrFail($id);
        return view('panel.inventory.pr.show', compact('pr'));
    }

    public function approve(Request $request, int $id)
    {
        $pr = PurchaseRequest::where('property_id', app('current_property')->id)->findOrFail($id);
        $pr->update(['status' => 'approved']);
        return back()->with('success', 'PR approved.');
    }

    public function reject(Request $request, int $id)
    {
        $pr = PurchaseRequest::where('property_id', app('current_property')->id)->findOrFail($id);
        $pr->update(['status' => 'rejected', 'notes' => $pr->notes . "\nRejection: " . $request->input('reason', '')]);
        return back()->with('success', 'PR rejected.');
    }
}
