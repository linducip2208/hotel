<?php

namespace App\Services\Maintenance;

use App\Models\PreventiveMaintenanceSchedule;
use Carbon\Carbon;

class PpmScheduler
{
    /** Generate WOs for upcoming PPM schedules within window. */
    public function generateUpcoming(int $propertyId, int $daysAhead = 7): int
    {
        $count = 0;
        PreventiveMaintenanceSchedule::where('property_id', $propertyId)
            ->where('is_active', true)
            ->whereDate('next_due_at', '<=', now()->addDays($daysAhead)->toDateString())
            ->each(function (PreventiveMaintenanceSchedule $s) use (&$count) {
                $svc = new WorkOrderService();
                $svc->create([
                    'property_id' => $s->property_id,
                    'asset_id' => $s->asset_id,
                    'type' => 'preventive',
                    'priority' => 'normal',
                    'description' => 'PPM '.$s->frequency.' — '.($s->checklist ?? 'standard'),
                ]);
                $s->update([
                    'last_done_at' => now()->toDateString(),
                    'next_due_at' => $this->advance($s->next_due_at, $s->frequency)->toDateString(),
                ]);
                $count++;
            });
        return $count;
    }

    protected function advance(Carbon $from, string $frequency): Carbon
    {
        return match ($frequency) {
            'daily' => $from->copy()->addDay(),
            'weekly' => $from->copy()->addWeek(),
            'monthly' => $from->copy()->addMonth(),
            'quarterly' => $from->copy()->addMonths(3),
            'yearly' => $from->copy()->addYear(),
            default => $from->copy()->addMonth(),
        };
    }
}
