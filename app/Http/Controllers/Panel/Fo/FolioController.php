<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\Folio;
use App\Services\Fo\FolioService;
use Illuminate\Http\Request;

class FolioController extends Controller
{
    public function __construct(protected FolioService $svc) {}

    public function show(int $id)
    {
        $folio = Folio::where('property_id', app('current_property')->id)->with(['charges', 'payments', 'reservation'])->findOrFail($id);
        return view('panel.fo.folios.show', compact('folio'));
    }

    public function addCharge(Request $request, int $id)
    {
        $folio = Folio::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'qty' => ['nullable', 'integer'],
            'tax_code' => ['nullable', 'string'],
            'is_taxable' => ['nullable', 'boolean'],
        ]);
        $data['posted_by_user_id'] = $request->user()?->id;
        $this->svc->postCharge($folio, $data);
        return back();
    }

    public function addPayment(Request $request, int $id)
    {
        $folio = Folio::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,card,qris,transfer,voucher,deposit,company_charge'],
            'reference_no' => ['nullable', 'string'],
        ]);
        $data['cashier_id'] = $request->user()?->id;
        $this->svc->postPayment($folio, $data);
        return back();
    }

    public function addDiscount(Request $request, int $id)
    {
        $folio = Folio::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate(['amount' => 'required|numeric|min:0', 'reason' => 'required|string']);
        $this->svc->applyDiscount($folio, (float) $data['amount'], $data['reason'], $request->user()?->id);
        return back();
    }

    public function transfer(Request $request, int $id)
    {
        $from = Folio::where('property_id', app('current_property')->id)->findOrFail($id);
        $to = Folio::where('property_id', app('current_property')->id)->findOrFail((int) $request->input('to_folio_id'));
        $this->svc->transfer($from, $to, (float) $request->input('amount'), $request->input('description', 'Transfer'));
        return back();
    }

    public function settle(Request $request, int $id)
    {
        $folio = Folio::where('property_id', app('current_property')->id)->findOrFail($id);
        if ((float) $folio->balance > 0) {
            return back()->withErrors(['balance' => 'Folio masih punya outstanding balance.']);
        }
        $folio->update(['status' => 'closed', 'closed_at' => now()]);
        return back();
    }

    public function invoice(int $id, Request $request)
    {
        $folio = Folio::where('property_id', app('current_property')->id)->with(['charges', 'payments', 'reservation.primaryGuest', 'property'])->findOrFail($id);
        if ($request->query('format') === 'pdf') {
            $pdf = app(\App\Services\Pdf\InvoicePdfGenerator::class)->generate($folio);
            return response($pdf, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="invoice-'.$folio->folio_no.'.pdf"']);
        }
        return view('panel.fo.folios.invoice', compact('folio'));
    }
}
