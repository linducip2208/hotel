<?php

namespace App\Http\Controllers\Panel\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorContract;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::where('property_id', app('current_property')->id);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        $vendors = $query->orderBy('name')->paginate(50);

        $categories = Vendor::where('property_id', app('current_property')->id)
            ->distinct()->pluck('category');

        return view('panel.inventory.vendors', compact('vendors', 'categories'));
    }

    public function create()
    {
        return view('panel.inventory.vendors');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'category' => 'required|string|in:maintenance,supplies,fnb,laundry,cleaning,it,other',
            'contact_person' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string',
            'payment_terms_days' => 'nullable|integer|min:0',
        ]);

        Vendor::create($data + [
            'property_id' => app('current_property')->id,
            'is_active' => true,
        ]);

        return redirect()->route('panel.inventory.vendors.index')->with('success', 'Vendor berhasil ditambahkan.');
    }

    public function show(int $id)
    {
        $vendor = Vendor::where('property_id', app('current_property')->id)
            ->with(['contracts', 'purchaseOrders.lines'])
            ->findOrFail($id);

        $totalSpend = PurchaseOrder::where('vendor_id', $vendor->id)
            ->where('status', '!=', 'draft')
            ->sum('total');

        return view('panel.inventory.vendors-show', compact('vendor', 'totalSpend'));
    }

    public function edit(int $id)
    {
        $vendor = Vendor::where('property_id', app('current_property')->id)->findOrFail($id);

        return view('panel.inventory.vendors', compact('vendor'));
    }

    public function update(Request $request, int $id)
    {
        $vendor = Vendor::where('property_id', app('current_property')->id)->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string',
            'category' => 'required|string|in:maintenance,supplies,fnb,laundry,cleaning,it,other',
            'contact_person' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string',
            'payment_terms_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $vendor->update($data);

        return redirect()->route('panel.inventory.vendors.index')->with('success', 'Vendor diperbarui.');
    }

    public function destroy(int $id)
    {
        $vendor = Vendor::where('property_id', app('current_property')->id)->findOrFail($id);
        $vendor->delete();

        return redirect()->route('panel.inventory.vendors.index')->with('success', 'Vendor dihapus.');
    }

    public function contracts(int $id)
    {
        $vendor = Vendor::where('property_id', app('current_property')->id)
            ->with('contracts')
            ->findOrFail($id);

        return view('panel.inventory.vendors-show', compact('vendor'));
    }

    public function storeContract(Request $request, int $id)
    {
        $vendor = Vendor::where('property_id', app('current_property')->id)->findOrFail($id);

        $data = $request->validate([
            'contract_number' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'value' => 'nullable|numeric',
            'scope_of_work' => 'nullable|string',
        ]);

        VendorContract::create($data + [
            'property_id' => app('current_property')->id,
            'vendor_id' => $vendor->id,
            'status' => 'active',
        ]);

        return back()->with('success', 'Kontrak berhasil ditambahkan.');
    }

    public function toggleActive(int $id)
    {
        $vendor = Vendor::where('property_id', app('current_property')->id)->findOrFail($id);
        $vendor->update(['is_active' => !$vendor->is_active]);

        return back()->with('success', 'Status vendor diubah.');
    }
}
