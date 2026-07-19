<?php

namespace App\Services\Approvals;

use App\Models\ApprovalRequest;
use App\Services\Audit\AuditLogger;

class ApprovalService
{
    public function __construct(protected AuditLogger $audit) {}

    public function request(string $actionType, array $payload, ?int $requesterId = null, ?int $expiresInHours = 48): ApprovalRequest
    {
        $req = ApprovalRequest::create([
            'property_id' => app('current_property')->id,
            'requester_id' => $requesterId ?? auth()->id() ?? 1,
            'action_type' => $actionType,
            'payload' => $payload,
            'status' => 'pending',
            'expires_at' => now()->addHours($expiresInHours),
        ]);
        $this->audit->record('approval.requested', $req, ['action_type' => $actionType]);
        return $req;
    }

    public function approve(ApprovalRequest $r, int $approverId, ?string $notes = null): void
    {
        $r->update([
            'status' => 'approved',
            'approver_id' => $approverId,
            'approved_at' => now(),
            'approver_notes' => $notes,
        ]);
        $this->audit->record('approval.approved', $r);
    }

    public function reject(ApprovalRequest $r, int $approverId, ?string $notes = null): void
    {
        $r->update([
            'status' => 'rejected',
            'approver_id' => $approverId,
            'approved_at' => now(),
            'approver_notes' => $notes,
        ]);
        $this->audit->record('approval.rejected', $r);
    }

    /** Threshold check helper. Returns true kalau approve diperlukan. */
    public function needsApproval(string $actionType, float $amount = 0, array $config = []): bool
    {
        $thresholds = array_merge($this->getDefaultThresholds(), $config);

        return match ($actionType) {
            'discount' => $amount > $thresholds['discount_pct'],
            'refund' => $amount > $thresholds['refund_amount'],
            'comp_room', 'period_unlock' => true,
            'ap_payment' => $amount > $thresholds['ap_payment'],
            default => false,
        };
    }

    protected function getDefaultThresholds(): array
    {
        $property = app()->bound('current_property') ? app('current_property') : null;
        $overrides = [];

        if ($property && method_exists($property, 'getSetting')) {
            $overrides['discount_pct'] = (int) ($property->getSetting('approval.discount_pct', 20));
            $overrides['refund_amount'] = (int) ($property->getSetting('approval.refund_amount', 5000000));
            $overrides['ap_payment'] = (int) ($property->getSetting('approval.ap_payment', 50000000));
        }

        return array_merge([
            'discount_pct' => 20,
            'refund_amount' => 5000000,
            'comp_room' => true,
            'period_unlock' => true,
            'ap_payment' => 50000000,
        ], $overrides);
    }
}
