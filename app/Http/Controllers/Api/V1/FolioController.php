<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Folio;
use App\Models\Property;
use App\Services\Fo\FolioService;
use Illuminate\Http\Request;

class FolioController extends Controller
{
    public function __construct(protected FolioService $svc) {}

    private function property(): Property
    {
        return app('current_property') ?? Property::orderBy('id')->firstOrFail();
    }

    public function show(int $id)
    {
        $folio = Folio::where('property_id', $this->property()->id)
            ->with('charges', 'payments')
            ->findOrFail($id);

        return response()->json($folio);
    }

    public function addCharge(Request $request, int $id)
    {
        $validated = $request->validate([
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric',
            'charge_code'  => 'nullable|string|max:50',
            'quantity'     => 'nullable|numeric|min:0',
            'unit_price'   => 'nullable|numeric|min:0',
            'tax_amount'   => 'nullable|numeric|min:0',
            'reference_no' => 'nullable|string|max:100',
        ]);

        $folio = Folio::where('property_id', $this->property()->id)->findOrFail($id);

        return response()->json($this->svc->postCharge($folio, $validated));
    }

    public function addPayment(Request $request, int $id)
    {
        $validated = $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'method'         => 'required|string|max:50',
            'reference_no'   => 'nullable|string|max:100',
            'currency'       => 'nullable|string|size:3',
            'exchange_rate'  => 'nullable|numeric|min:0',
        ]);

        $folio = Folio::where('property_id', $this->property()->id)->findOrFail($id);

        return response()->json($this->svc->postPayment($folio, $validated));
    }

    public function transfer(Request $request, int $id)
    {
        $validated = $request->validate([
            'to_folio_id' => 'required|integer|exists:folios,id',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        $propertyId = $this->property()->id;
        $from = Folio::where('property_id', $propertyId)->findOrFail($id);
        $to   = Folio::where('property_id', $propertyId)->findOrFail($validated['to_folio_id']);

        $this->svc->transfer($from, $to, (float) $validated['amount'], $validated['description'] ?? 'Transfer');

        return response()->json(['ok' => true]);
    }

    public function addDiscount(Request $request, int $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
        ]);

        $folio = Folio::where('property_id', $this->property()->id)->findOrFail($id);

        return response()->json($this->svc->applyDiscount($folio, (float) $validated['amount'], $validated['reason'] ?? '-'));
    }

    public function invoicePdf(int $id)
    {
        Folio::where('property_id', $this->property()->id)->findOrFail($id);

        return response()->json(['ok' => true, 'message' => 'PDF generation stub']);
    }
}
