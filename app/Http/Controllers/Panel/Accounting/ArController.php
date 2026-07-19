<?php

namespace App\Http\Controllers\Panel\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ArInvoice;

class ArController extends Controller
{
    public function index()
    {
        $invoices = ArInvoice::where('property_id', app('current_property')->id)
            ->orderByDesc('issued_at')->paginate(50);
        return view('panel.accounting.ar.index', compact('invoices'));
    }

    public function show(int $id)
    {
        $invoice = ArInvoice::with('lines', 'payments')->findOrFail($id);
        return view('panel.accounting.ar.show', compact('invoice'));
    }
}
