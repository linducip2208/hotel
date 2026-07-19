<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\NightAudit;
use App\Services\Accounting\NightAuditService;
use Illuminate\Http\Request;

class NightAuditController extends Controller
{
    public function index()
    {
        $property = app('current_property');
        $audits = NightAudit::where('property_id', $property->id)
            ->orderByDesc('audit_date')->paginate(30);
        return view('panel.fo.night-audit', compact('audits'));
    }

    public function run(Request $request, NightAuditService $svc)
    {
        $audit = $svc->run(app('current_property'), null, $request->user()?->id);
        return back()->with('status', "Night audit {$audit->audit_date} {$audit->status}");
    }
}
