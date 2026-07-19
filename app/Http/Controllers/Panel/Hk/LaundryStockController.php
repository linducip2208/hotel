<?php

namespace App\Http\Controllers\Panel\Hk;

use App\Http\Controllers\Controller;
use App\Models\LinenCategory;
use App\Models\UniformAssignment;
use App\Services\Hk\LinenService;
use Illuminate\Http\Request;

class LaundryStockController extends Controller
{
    protected LinenService $service;

    public function __construct(LinenService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $propertyId = app('current_property')->id;
        $categories = $this->service->getStockLevels($propertyId);
        $transactions = $this->service->getRecentTransactions($propertyId);
        $alerts = $this->service->getParAlerts($propertyId);
        $uniforms = $this->service->getUniforms($propertyId);

        $totalStock = $categories->sum('current_stock');
        $totalDamaged = $categories->sum('damaged_count');
        $belowParCount = $categories->filter(fn ($c) => $c->stock_status === 'below_par' || $c->stock_status === 'empty')->count();

        return view('panel.hk.linen-stock', compact(
            'categories', 'transactions', 'alerts', 'uniforms',
            'totalStock', 'totalDamaged', 'belowParCount'
        ));
    }

    public function storeTransaction(Request $request)
    {
        $data = $request->validate([
            'linen_category_id' => 'required|exists:linen_categories,id',
            'transaction_type' => 'required|in:issue,return,wash,discard,audit',
            'quantity' => 'required|integer|min:1',
            'room_id' => 'nullable|exists:rooms,id',
            'location_from' => 'nullable|string|max:255',
            'location_to' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->service->recordTransaction($data);
            return back()->with('success', 'Transaksi linen berhasil dicatat.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function uniforms()
    {
        $propertyId = app('current_property')->id;
        $uniforms = $this->service->getUniforms($propertyId);

        return view('panel.hk.linen-uniforms', compact('uniforms'));
    }

    public function assignUniform(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'linen_category_id' => 'required|exists:linen_categories,id',
            'quantity_assigned' => 'required|integer|min:1',
            'assigned_date' => 'required|date',
            'condition' => 'required|in:baik,rusak,perlu_ganti',
        ]);

        $data['property_id'] = app('current_property')->id;
        $this->service->assignUniform($data);

        return back()->with('success', 'Seragam berhasil ditugaskan ke karyawan.');
    }

    public function returnUniform(Request $request, int $id)
    {
        $request->validate([
            'condition' => 'required|in:baik,rusak,perlu_ganti',
        ]);

        $propertyId = app('current_property')->id;
        $assignment = UniformAssignment::where('property_id', $propertyId)->findOrFail($id);
        $this->service->returnUniform($assignment->id);

        return back()->with('success', 'Pengembalian seragam berhasil dicatat.');
    }
}
