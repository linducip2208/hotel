<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\NightAuditCompleted;
use App\Models\NightAudit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class GenerateNightAuditReport implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 2;
    public int $timeout = 300;

    public function handle(NightAuditCompleted $event): void
    {
        $nightAudit = $event->nightAudit;

        $reportData = [
            'audit_date'       => $nightAudit->audit_date?->toDateString(),
            'property_id'      => $nightAudit->property_id,
            'started_at'       => $nightAudit->started_at?->toDateTimeString(),
            'completed_at'     => $nightAudit->completed_at?->toDateTimeString(),
            'summary'          => $nightAudit->summary,
            'generated_at'     => now()->toDateTimeString(),
        ];

        $nightAudit->update([
            'summary' => array_merge((array) $nightAudit->summary, [
                'report_generated' => true,
                'report_data'      => $reportData,
            ]),
        ]);
    }
}
