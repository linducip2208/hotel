<?php

namespace App\Http\Controllers\Panel\Finance;

use App\Http\Controllers\Controller;
use App\Models\Chargeback;
use App\Models\ChargebackEvidence;
use App\Models\FolioPayment;
use App\Services\Finance\ChargebackService;
use Illuminate\Http\Request;

class ChargebackController extends Controller
{
    public function __construct(protected ChargebackService $chargebackService) {}

    public function index(Request $request)
    {
        $property = app('current_property');
        $stats = $this->chargebackService->getStats($property);
        $alerts = $this->chargebackService->getDeadlineAlerts($property);

        $query = Chargeback::where('property_id', $property->id)
            ->with(['reservation.primaryGuest', 'paymentTransaction']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($from = $request->query('from')) {
            $query->whereDate('chargeback_date', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->whereDate('chargeback_date', '<=', $to);
        }

        $chargebacks = $query->orderByDesc('created_at')->paginate(30)->appends($request->query());

        return view('panel.finance.chargebacks', compact('stats', 'alerts', 'chargebacks'));
    }

    public function show($id)
    {
        $chargeback = Chargeback::with([
            'reservation.primaryGuest',
            'paymentTransaction',
            'folioCharge',
            'evidence',
        ])->findOrFail($id);

        $payments = FolioPayment::where('property_id', app('current_property')->id)
            ->orderByDesc('payment_date')
            ->get();

        return view('panel.finance.chargeback-detail', compact('chargeback', 'payments'));
    }

    public function register(Request $request)
    {
        $property = app('current_property');
        $data = $request->validate([
            'payment_transaction_id' => 'required|integer|exists:folio_payments,id',
            'reservation_id' => 'nullable|integer|exists:reservations,id',
            'folio_charge_id' => 'nullable|integer|exists:folio_charges,id',
            'chargeback_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'reason_code' => 'nullable|string',
            'reason_description' => 'nullable|string',
            'card_brand' => 'nullable|string',
            'card_last_4' => 'nullable|string|max:4',
            'disputed_by' => 'nullable|string',
            'evidence_deadline' => 'nullable|date',
            'internal_notes' => 'nullable|string',
        ]);

        $transaction = FolioPayment::findOrFail($data['payment_transaction_id']);
        $this->chargebackService->register($property, $transaction, $data);

        return redirect()->route('panel.finance.chargebacks.index')
            ->with('success', 'Chargeback berhasil dicatat.');
    }

    public function addEvidence(Request $request, $id)
    {
        $chargeback = Chargeback::findOrFail($id);
        $data = $request->validate([
            'evidence_type' => 'required|string',
            'file' => 'required|file|max:10240',
            'description' => 'nullable|string',
        ]);

        $path = $request->file('file')->store('chargeback-evidence');
        $this->chargebackService->addEvidence($chargeback, [
            'evidence_type' => $data['evidence_type'],
            'file_path' => $path,
            'description' => $data['description'] ?? null,
        ]);

        return back()->with('success', 'Bukti berhasil diunggah.');
    }

    public function deleteEvidence($chargebackId, $evidenceId)
    {
        $evidence = ChargebackEvidence::findOrFail($evidenceId);
        if (file_exists(storage_path('app/' . $evidence->file_path))) {
            unlink(storage_path('app/' . $evidence->file_path));
        }
        $evidence->delete();

        return back()->with('success', 'Bukti dihapus.');
    }

    public function submit($id)
    {
        $chargeback = Chargeback::findOrFail($id);
        $this->chargebackService->submitResponse($chargeback);

        return back()->with('success', 'Respon chargeback telah disubmit.');
    }

    public function outcome(Request $request, $id)
    {
        $chargeback = Chargeback::findOrFail($id);
        $data = $request->validate([
            'decision' => 'required|in:won,lost,accepted',
            'recovered_amount' => 'nullable|numeric|min:0',
        ]);

        $this->chargebackService->recordOutcome(
            $chargeback,
            $data['decision'],
            $data['recovered_amount'] ?? null
        );

        return back()->with('success', 'Hasil chargeback dicatat.');
    }
}
