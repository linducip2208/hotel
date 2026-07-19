<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BudgetPeriod;
use App\Models\FxRate;
use App\Models\OwnerStatement;
use App\Models\Property;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    private function property(): Property
    {
        return app('current_property') ?? Property::orderBy('id')->firstOrFail();
    }

    public function bankAccounts()
    {
        return response()->json(
            BankAccount::where('property_id', $this->property()->id)->paginate(50)
        );
    }

    public function ownerStatements()
    {
        return response()->json(
            OwnerStatement::where('property_id', $this->property()->id)->latest()->paginate(50)
        );
    }

    public function fxRates()
    {
        return response()->json(FxRate::orderByDesc('rate_date')->paginate(100));
    }

    public function fxLookup(Request $request)
    {
        $request->validate([
            'base'  => 'required|string|size:3',
            'quote' => 'required|string|size:3',
        ]);

        $rate = FxRate::lookup(
            strtoupper($request->query('base')),
            strtoupper($request->query('quote'))
        );

        return response()->json(['rate' => $rate]);
    }

    public function budgets()
    {
        return response()->json(
            BudgetPeriod::where('property_id', $this->property()->id)
                ->with('lines')
                ->paginate(50)
        );
    }
}
