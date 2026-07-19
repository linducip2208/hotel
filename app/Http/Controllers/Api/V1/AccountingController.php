<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\NightAudit;
use App\Models\Property;
use App\Services\Accounting\JournalPoster;
use Illuminate\Http\Request;

class AccountingController extends Controller
{
    private function property(): Property
    {
        return app('current_property') ?? Property::orderBy('id')->firstOrFail();
    }

    public function coa()
    {
        return response()->json(
            ChartOfAccount::where('property_id', $this->property()->id)
                ->orderBy('code')
                ->get()
        );
    }

    public function journals()
    {
        return response()->json(
            JournalEntry::where('property_id', $this->property()->id)
                ->with('lines')
                ->latest()
                ->paginate(50)
        );
    }

    public function storeJournal(Request $request, JournalPoster $poster)
    {
        $data = $request->validate([
            'description' => 'required|string|max:500',
            'lines'       => 'required|array|min:2',
            'lines.*.account_id' => 'required|integer|exists:chart_of_accounts,id',
            'lines.*.debit'      => 'nullable|numeric|min:0',
            'lines.*.credit'     => 'nullable|numeric|min:0',
        ]);

        $entry = $poster->post(
            $this->property()->id,
            $data['description'],
            $data['lines'],
            'api'
        );

        return response()->json($entry, 201);
    }

    public function dailyRevenue(Request $request)
    {
        $audit = NightAudit::where('property_id', $this->property()->id)
            ->whereDate('audit_date', $request->query('date', now()->subDay()->toDateString()))
            ->first();

        return response()->json($audit);
    }

    public function trialBalance()
    {
        return response()->json(['stub' => true]);
    }

    public function profitLoss()
    {
        return response()->json(['stub' => true]);
    }
}
