<?php

namespace App\Http\Controllers\Panel\Finance;

use App\Http\Controllers\Controller;
use App\Models\OwnerDistribution;
use App\Models\PropertyOwner;
use App\Models\User;
use App\Services\Finance\OwnerPortalService;
use Illuminate\Http\Request;

class OwnerManagementController extends Controller
{
    public function __construct(protected OwnerPortalService $ownerService) {}

    public function index(Request $request)
    {
        $property = app('current_property');

        $owners = PropertyOwner::where('property_id', $property->id)
            ->with('user')
            ->orderBy('ownership_pct', 'desc')
            ->get();

        $users = User::orderBy('name')->get();

        $period = $request->query('period', now()->startOfMonth()->toDateString());
        $calculate = $this->ownerService->calculateDistribution($property, $period);

        $distributions = OwnerDistribution::where('property_id', $property->id)
            ->with('owner', 'createdBy')
            ->orderByDesc('period_start')
            ->paginate(20);

        return view('panel.finance.owner-management', compact(
            'owners', 'users', 'calculate', 'distributions', 'period'
        ));
    }

    public function store(Request $request)
    {
        $property = app('current_property');
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'ownership_pct' => 'required|numeric|min:0|max:100',
            'investment_amount' => 'nullable|numeric|min:0',
            'joined_at' => 'nullable|date',
        ]);

        PropertyOwner::updateOrCreate(
            ['property_id' => $property->id, 'user_id' => $data['user_id']],
            [
                'ownership_pct' => $data['ownership_pct'],
                'investment_amount' => $data['investment_amount'] ?? 0,
                'joined_at' => $data['joined_at'] ?? now()->toDateString(),
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Pemilik properti berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $owner = PropertyOwner::findOrFail($id);
        $data = $request->validate([
            'ownership_pct' => 'required|numeric|min:0|max:100',
            'investment_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $owner->update($data);
        return back()->with('success', 'Data pemilik diperbarui.');
    }

    public function destroy($id)
    {
        $owner = PropertyOwner::findOrFail($id);
        $owner->delete();
        return back()->with('success', 'Pemilik dihapus dari properti.');
    }

    public function storeDistribution(Request $request)
    {
        $property = app('current_property');
        $data = $request->validate([
            'owner_user_id' => 'required|integer|exists:users,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'distribution_amount' => 'required|numeric|min:0',
            'distribution_pct' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'nullable|string',
            'reference_number' => 'nullable|string',
        ]);

        OwnerDistribution::create($data + [
            'property_id' => $property->id,
            'status' => 'pending',
            'created_by_user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Distribusi berhasil dicatat.');
    }

    public function markDistributionPaid($id, Request $request)
    {
        $dist = OwnerDistribution::findOrFail($id);
        $dist->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $request->input('payment_method', $dist->payment_method),
            'reference_number' => $request->input('reference_number', $dist->reference_number),
        ]);

        return back()->with('success', 'Distribusi ditandai sebagai dibayar.');
    }

    public function uploadDocument(Request $request)
    {
        $property = app('current_property');
        $data = $request->validate([
            'owner_user_id' => 'required|integer|exists:users,id',
            'title' => 'required|string|max:255',
            'document_type' => 'required|string',
            'file' => 'required|file|max:10240',
        ]);

        $path = $request->file('file')->store('owner-documents');

        \App\Models\OwnerDocument::create([
            'property_id' => $property->id,
            'owner_user_id' => $data['owner_user_id'],
            'title' => $data['title'],
            'document_type' => $data['document_type'],
            'file_path' => $path,
            'uploaded_at' => now(),
        ]);

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }
}
