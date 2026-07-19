<?php

namespace App\Http\Controllers\Panel\Finance;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Guest;
use App\Models\Reservation;
use App\Services\Finance\DepositService;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    public function __construct(protected DepositService $depositService) {}

    public function index(Request $request)
    {
        $property = app('current_property');
        $stats = $this->depositService->getStats($property);

        $query = Deposit::where('property_id', $property->id)
            ->with(['reservation.primaryGuest', 'guest', 'createdBy']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($type = $request->query('deposit_type')) {
            $query->where('deposit_type', $type);
        }
        if ($from = $request->query('from')) {
            $query->whereDate('received_date', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->whereDate('received_date', '<=', $to);
        }

        $deposits = $query->orderByDesc('created_at')->paginate(30)->appends($request->query());

        return view('panel.finance.deposits', compact('stats', 'deposits'));
    }

    public function receive(Request $request)
    {
        $property = app('current_property');
        $data = $request->validate([
            'reservation_id' => 'nullable|integer|exists:reservations,id',
            'guest_id' => 'nullable|integer|exists:guests,id',
            'deposit_type' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'nullable|string',
            'payment_reference' => 'nullable|string',
            'received_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $this->depositService->receive($property, $data);
        return back()->with('success', 'Deposit berhasil diterima.');
    }

    public function refund(Request $request, $id)
    {
        $deposit = Deposit::findOrFail($id);
        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . ($deposit->amount - $deposit->refunded_amount),
            'refund_method' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        $this->depositService->refund($deposit, $data['amount'], $data['refund_method'], $data['reason'] ?? null);
        return back()->with('success', 'Refund deposit berhasil diproses.');
    }

    public function forfeit(Request $request, $id)
    {
        $deposit = Deposit::findOrFail($id);
        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->depositService->forfeit($deposit, $data['reason']);
        return back()->with('success', 'Deposit ditandai hangus.');
    }

    public function applyToFolio(Request $request, $id)
    {
        $deposit = Deposit::findOrFail($id);
        $data = $request->validate([
            'folio_charge_id' => 'required|integer|exists:folio_charges,id',
        ]);

        $charge = \App\Models\FolioCharge::findOrFail($data['folio_charge_id']);
        $this->depositService->applyToFolio($deposit, $charge);

        return back()->with('success', 'Deposit diaplikasikan ke folio.');
    }
}
