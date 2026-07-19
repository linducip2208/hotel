<?php

namespace App\Http\Controllers\Panel\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountsController extends Controller
{
    public function index()
    {
        $accounts = ChartOfAccount::where('property_id', app('current_property')->id)->orderBy('code')->get();
        return view('panel.accounting.coa', compact('accounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:16',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense,header',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id' => 'nullable|integer',
        ]);
        ChartOfAccount::create($data + ['property_id' => app('current_property')->id]);
        return back();
    }

    public function update(Request $request, int $id)
    {
        $coa = ChartOfAccount::where('property_id', app('current_property')->id)->findOrFail($id);
        if ($coa->is_system) abort(403, 'System account cannot be edited.');
        $coa->update($request->only(['name', 'description', 'is_active']));
        return back();
    }
}
