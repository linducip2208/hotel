<?php

namespace App\Http\Controllers\Panel\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ApBill;

class ApController extends Controller
{
    public function index()
    {
        $bills = ApBill::where('property_id', app('current_property')->id)
            ->orderByDesc('issued_at')->paginate(50);
        return view('panel.accounting.ap.index', compact('bills'));
    }

    public function show(int $id)
    {
        $bill = ApBill::with('lines', 'payments')->findOrFail($id);
        return view('panel.accounting.ap.show', compact('bill'));
    }
}
