<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NightAudit;
use App\Services\Accounting\NightAuditService;
use DateTime;
use Illuminate\Http\Request;

class NightAuditController extends Controller
{
    public function __construct(private NightAuditService $svc) {}

    public function index(Request $request)
    {
        $property = $request->user()->property;
        $audits = NightAudit::where('property_id', $property->id)
            ->orderByDesc('audit_date')
            ->paginate(30);
        return response()->json($audits);
    }

    public function run(Request $request)
    {
        $request->validate(['date' => 'required|date_format:Y-m-d']);
        $property = $request->user()->property;
        $audit = $this->svc->run($property, new DateTime($request->date), $request->user()->id);
        return response()->json($audit, 201);
    }

    public function show(Request $request, int $id)
    {
        $property = $request->user()->property;
        $audit = NightAudit::where('property_id', $property->id)->findOrFail($id);
        return response()->json($audit);
    }
}
