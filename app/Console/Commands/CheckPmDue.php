<?php

namespace App\Console\Commands;

use App\Models\PmSchedule;
use App\Models\Property;
use App\Services\Asset\PmService;
use Illuminate\Console\Command;

class CheckPmDue extends Command
{
    protected $signature = 'pm:check-due';
    protected $description = 'Periksa jadwal preventive maintenance yang overdue dan kirim notifikasi';

    public function handle(PmService $svc): int
    {
        $properties = Property::where('is_active', true)->get();

        foreach ($properties as $property) {
            $overdue = PmSchedule::where('property_id', $property->id)
                ->where('is_active', true)
                ->where('next_due_at', '<', now())
                ->with('asset')
                ->get();

            if ($overdue->isNotEmpty()) {
                $this->warn("Properti: {$property->name} — {$overdue->count()} PM task overdue:");

                foreach ($overdue as $task) {
                    $daysOverdue = now()->diffInDays($task->next_due_at);
                    $this->line("  - {$task->task_name} ({$task->asset?->name}) — overdue {$daysOverdue} hari sejak {$task->next_due_at->format('d M Y')}");
                }
            }

            $dueThisWeek = PmSchedule::where('property_id', $property->id)
                ->where('is_active', true)
                ->whereBetween('next_due_at', [now()->toDateString(), now()->addDays(7)->toDateString()])
                ->count();

            if ($dueThisWeek > 0) {
                $this->info("  {$dueThisWeek} task jatuh tempo dalam 7 hari ke depan.");
            }
        }

        return self::SUCCESS;
    }
}
