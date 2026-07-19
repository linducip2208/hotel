<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ArAccount;
use App\Models\ArInvoice;
use App\Models\ApBill;
use App\Models\ApSupplier;
use Illuminate\Http\Request;

class ArApController extends Controller
{
    // ── AR ─────────────────────────────────────────────────────────────────

    public function arAccounts(Request $request)
    {
        $property = $request->user()->property;
        $accounts = ArAccount::where('property_id', $property->id)
            ->with(['company', 'travelAgent', 'channel', 'guest'])
            ->where('is_active', true)
            ->get();
        return response()->json($accounts);
    }

    public function arInvoices(Request $request)
    {
        $property = $request->user()->property;
        $query = ArInvoice::where('property_id', $property->id)
            ->with(['arAccount', 'lines', 'payments'])
            ->orderByDesc('issued_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(20));
    }

    public function showArInvoice(Request $request, int $id)
    {
        $property = $request->user()->property;
        $invoice = ArInvoice::where('property_id', $property->id)
            ->with(['arAccount', 'lines', 'payments'])
            ->findOrFail($id);
        return response()->json($invoice);
    }

    public function payArInvoice(Request $request, int $id)
    {
        $property = $request->user()->property;
        $invoice = ArInvoice::where('property_id', $property->id)->findOrFail($id);

        $data = $request->validate([
            'amount'       => 'required|numeric|min:0.01',
            'method'       => 'required|string',
            'reference_no' => 'nullable|string',
        ]);

        $payment = $invoice->payments()->create([
            ...$data,
            'paid_at' => now()->toDateString(),
        ]);

        $invoice->increment('paid_total', $data['amount']);
        $invoice->decrement('balance', $data['amount']);
        if ($invoice->fresh()->balance <= 0) {
            $invoice->update(['status' => 'paid']);
        }

        return response()->json($payment, 201);
    }

    // ── AP ─────────────────────────────────────────────────────────────────

    public function suppliers(Request $request)
    {
        $property = $request->user()->property;
        $suppliers = ApSupplier::where('property_id', $property->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        return response()->json($suppliers);
    }

    public function apBills(Request $request)
    {
        $property = $request->user()->property;
        $query = ApBill::where('property_id', $property->id)
            ->with(['supplier', 'lines', 'payments'])
            ->orderByDesc('issued_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(20));
    }

    public function payApBill(Request $request, int $id)
    {
        $property = $request->user()->property;
        $bill = ApBill::where('property_id', $property->id)->findOrFail($id);

        $data = $request->validate([
            'amount'       => 'required|numeric|min:0.01',
            'method'       => 'required|string',
            'reference_no' => 'nullable|string',
        ]);

        $payment = $bill->payments()->create([
            ...$data,
            'paid_at' => now()->toDateString(),
        ]);

        $bill->increment('paid_total', $data['amount']);
        $bill->decrement('balance', $data['amount']);
        if ($bill->fresh()->balance <= 0) {
            $bill->update(['status' => 'paid']);
        }

        return response()->json($payment, 201);
    }
}
