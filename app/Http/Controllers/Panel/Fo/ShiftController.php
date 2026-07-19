<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Services\Fo\CashierShiftService;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function __construct(protected CashierShiftService $svc) {}

    public function index(Request $request)
    {
        $shifts = CashierShift::where('property_id', app('current_property')->id)
            ->with('cashier')->orderByDesc('opened_at')->paginate(50);
        $current = $request->user() ? $this->svc->currentForCashier($request->user()) : null;
        return view('panel.fo.shifts.index', compact('shifts', 'current'));
    }

    public function open(Request $request)
    {
        $data = $request->validate(['opening_float' => 'required|numeric|min:0']);
        $this->svc->open($request->user(), (float) $data['opening_float']);
        return back();
    }

    public function close(Request $request, int $id)
    {
        $shift = CashierShift::where('property_id', app('current_property')->id)->findOrFail($id);
        if ($shift->cashier_id !== $request->user()?->id) abort(403);
        $data = $request->validate(['actual_cash' => 'required|numeric|min:0', 'notes' => 'nullable|string']);
        $this->svc->close($shift, (float) $data['actual_cash'], $data['notes'] ?? null);
        return back();
    }

    public function show(int $id)
    {
        $shift = CashierShift::with('cashier', 'payments')->findOrFail($id);
        return view('panel.fo.shifts.show', compact('shift'));
    }
}
