<?php

namespace App\Http\Controllers\Panel\Hk;

use App\Http\Controllers\Controller;
use App\Models\LinenItem;
use App\Models\LinenTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LinenController extends Controller
{
    public function index()
    {
        $items = LinenItem::where('property_id', app('current_property')->id)
            ->with('transactions')
            ->orderBy('type')->orderBy('name')->get();

        $totalStock = $items->sum('current_stock');
        $totalDamaged = $items->sum('damaged');
        $deficitCount = $items->filter(fn ($i) => $i->status === 'deficit')->count();

        return view('panel.hk.linen.index', compact('items', 'totalStock', 'totalDamaged', 'deficitCount'));
    }

    public function create()
    {
        return view('panel.hk.linen.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:bed_sheet,pillow_case,towel,bathrobe,blanket,table_cloth',
            'initial_stock' => 'required|integer|min:0',
            'current_stock' => 'required|integer|min:0',
        ]);

        LinenItem::create($data + ['property_id' => app('current_property')->id]);

        return redirect()->route('panel.hk.linen.index')->with('success', 'Linen item created.');
    }

    public function stockIn(Request $request, int $id)
    {
        $item = $this->findItem($id);
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($item, $data) {
            $item->increment('current_stock', $data['quantity']);
            LinenTransaction::create([
                'property_id' => app('current_property')->id,
                'linen_item_id' => $item->id,
                'type' => 'in',
                'quantity' => $data['quantity'],
                'reference' => $data['reference'] ?? null,
                'staff_id' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return back()->with('success', 'Stock added.');
    }

    public function stockOut(Request $request, int $id)
    {
        $item = $this->findItem($id);
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:out,damaged,discarded',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($item, $data) {
            if (in_array($data['type'], ['damaged', 'discarded'])) {
                $item->increment('damaged', $data['quantity']);
            }
            $item->decrement('current_stock', $data['quantity']);
            $item->save();

            LinenTransaction::create([
                'property_id' => app('current_property')->id,
                'linen_item_id' => $item->id,
                'type' => $data['type'],
                'quantity' => $data['quantity'],
                'reference' => $data['reference'] ?? null,
                'staff_id' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return back()->with('success', 'Stock deducted.');
    }

    public function audit()
    {
        $items = LinenItem::where('property_id', app('current_property')->id)
            ->orderBy('type')->orderBy('name')->get();

        return view('panel.hk.linen.audit', compact('items'));
    }

    public function auditSave(Request $request)
    {
        $data = $request->validate([
            'counts' => 'required|array',
            'counts.*' => 'required|integer|min:0',
        ]);

        $propertyId = app('current_property')->id;

        DB::transaction(function () use ($data, $propertyId) {
            foreach ($data['counts'] as $itemId => $actualCount) {
                $item = LinenItem::where('property_id', $propertyId)->findOrFail($itemId);
                $diff = (int) $actualCount - $item->current_stock;

                if ($diff !== 0) {
                    $item->update([
                        'current_stock' => (int) $actualCount,
                        'last_audit_at' => now(),
                    ]);

                    $label = $diff > 0 ? 'adjustment_in' : 'adjustment_out';
                    LinenTransaction::create([
                        'property_id' => $propertyId,
                        'linen_item_id' => $item->id,
                        'type' => $diff > 0 ? 'in' : 'out',
                        'quantity' => abs($diff),
                        'reference' => 'Physical audit ' . now()->toDateString(),
                        'staff_id' => auth()->id(),
                        'notes' => "Audit adjustment: system={$item->getOriginal('current_stock')}, actual={$actualCount}, diff={$diff}",
                    ]);
                } else {
                    $item->update(['last_audit_at' => now()]);
                }
            }
        });

        return redirect()->route('panel.hk.linen.index')->with('success', 'Audit completed.');
    }

    public function history(int $id)
    {
        $item = $this->findItem($id);
        $transactions = LinenTransaction::where('linen_item_id', $item->id)
            ->with('staff')
            ->latest()
            ->paginate(25);

        return view('panel.hk.linen.history', compact('item', 'transactions'));
    }

    protected function findItem(int $id): LinenItem
    {
        return LinenItem::where('property_id', app('current_property')->id)->findOrFail($id);
    }
}
