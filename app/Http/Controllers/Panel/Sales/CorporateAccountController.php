<?php

namespace App\Http\Controllers\Panel\Sales;

use App\Http\Controllers\Controller;
use App\Models\CorporateAccount;
use App\Models\RoomType;
use App\Services\Sales\CorporateAccountService;
use Illuminate\Http\Request;

class CorporateAccountController extends Controller
{
    public function __construct(protected CorporateAccountService $service) {}

    public function index(Request $request)
    {
        $accounts = $this->service->list(app('current_property'), $request->only(['status', 'search']));
        return view('panel.sales.corporate-index', compact('accounts'));
    }

    public function create()
    {
        return view('panel.sales.corporate-form', [
            'account' => new CorporateAccount(),
            'roomTypes' => RoomType::where('property_id', app('current_property')->id)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'rate_agreement_type' => 'required|in:fixed,percentage_discount,dynamic',
            'discount_pct' => 'nullable|numeric|min:0|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date',
            'annual_room_night_commitment' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);
        $this->service->create(app('current_property'), $data);
        return redirect()->route('panel.sales.corporate.index')->with('success', 'Corporate account berhasil dibuat.');
    }

    public function show($id)
    {
        $account = CorporateAccount::where('property_id', app('current_property')->id)
            ->with(['rates.roomType', 'bookings.reservation'])
            ->findOrFail($id);
        $performance = $this->service->performanceReport($account);
        $roomTypes = RoomType::where('property_id', app('current_property')->id)->orderBy('name')->get();
        return view('panel.sales.corporate-show', compact('account', 'performance', 'roomTypes'));
    }

    public function edit($id)
    {
        $account = CorporateAccount::where('property_id', app('current_property')->id)->findOrFail($id);
        return view('panel.sales.corporate-form', [
            'account' => $account,
            'roomTypes' => RoomType::where('property_id', app('current_property')->id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $account = CorporateAccount::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'rate_agreement_type' => 'required|in:fixed,percentage_discount,dynamic',
            'discount_pct' => 'nullable|numeric|min:0|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date',
            'annual_room_night_commitment' => 'nullable|integer|min:0',
            'status' => 'required|in:active,suspended,expired',
            'notes' => 'nullable|string',
        ]);
        $this->service->update($account, $data);
        return back()->with('success', 'Corporate account berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $account = CorporateAccount::where('property_id', app('current_property')->id)->findOrFail($id);
        $account->delete();
        return back()->with('success', 'Corporate account berhasil dihapus.');
    }

    public function rates(Request $request, $id)
    {
        $account = CorporateAccount::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'negotiated_rate' => 'required|numeric|min:0',
            'blackout_dates' => 'nullable|array',
            'is_active' => 'boolean',
        ]);
        $this->service->saveRate(app('current_property'), $account, $data);
        return back()->with('success', 'Rate berhasil disimpan.');
    }

    public function deleteRate($id, $rateId)
    {
        $rate = \App\Models\CorporateRate::whereHas('corporateAccount', fn($q) =>
            $q->where('property_id', app('current_property')->id)
        )->findOrFail($rateId);
        $this->service->deleteRate($rate);
        return back()->with('success', 'Rate berhasil dihapus.');
    }

    public function bookings($id)
    {
        $account = CorporateAccount::where('property_id', app('current_property')->id)->findOrFail($id);
        $bookings = $account->bookings()->with('reservation')->latest()->paginate(20);
        return view('panel.sales.corporate-show', compact('account', 'bookings'));
    }
}
