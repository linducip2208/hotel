<?php

namespace App\Services\Maintenance;

use App\Models\WorkOrder;
use Illuminate\Support\Str;

class WorkOrderService
{
    public function create(array $data): WorkOrder
    {
        return WorkOrder::create($data + [
            'wo_no' => 'WO-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
            'reported_at' => now(),
            'status' => 'open',
        ]);
    }

    public function start(WorkOrder $wo, ?int $userId = null): void
    {
        $wo->update(['status' => 'in_progress', 'started_at' => now(), 'assignee_id' => $userId ?? $wo->assignee_id]);
    }

    public function complete(WorkOrder $wo, ?string $resolution = null): void
    {
        $wo->update(['status' => 'done', 'completed_at' => now(), 'resolution' => $resolution]);
    }

    public function verify(WorkOrder $wo): void
    {
        $wo->update(['status' => 'verified', 'verified_at' => now()]);
    }
}
