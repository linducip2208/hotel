<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Services\Approvals\ApprovalService;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function __construct(private ApprovalService $svc) {}

    public function index(Request $request)
    {
        $property = $request->user()->property;
        $query = ApprovalRequest::where('property_id', $property->id)
            ->with(['requester', 'approver'])
            ->orderByDesc('created_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(25));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'    => 'required|string',
            'payload' => 'required|array',
            'amount'  => 'nullable|numeric|min:0',
        ]);

        $approval = $this->svc->request(
            property: $request->user()->property,
            requester: $request->user(),
            type: $data['type'],
            payload: $data['payload'],
            amount: $data['amount'] ?? null,
        );

        return response()->json($approval, 201);
    }

    public function approve(Request $request, int $id)
    {
        $property = $request->user()->property;
        $approval = ApprovalRequest::where('property_id', $property->id)->findOrFail($id);
        $result = $this->svc->approve($approval, $request->user(), $request->notes);
        return response()->json($result);
    }

    public function reject(Request $request, int $id)
    {
        $property = $request->user()->property;
        $approval = ApprovalRequest::where('property_id', $property->id)->findOrFail($id);
        $result = $this->svc->reject($approval, $request->user(), $request->notes);
        return response()->json($result);
    }
}
