<?php

namespace App\Console\Commands\Audit;

use App\Models\AuditLog;
use App\Models\AuditLogCheckpoint;
use Illuminate\Console\Command;

class CheckpointCommand extends Command
{
    protected $signature = 'audit:checkpoint {--date=}';
    protected $description = 'Create daily checkpoint of audit log cumulative hash for external archival';

    public function handle(): int
    {
        $date = $this->option('date') ?: now()->subDay()->toDateString();
        $entries = AuditLog::whereDate('created_at', $date)->orderBy('id');

        $first = (clone $entries)->first();
        $last = (clone $entries)->latest('id')->first();
        $count = (clone $entries)->count();
        if (! $first || ! $last) {
            $this->info("No entries for {$date}, skipping.");
            return self::SUCCESS;
        }

        AuditLogCheckpoint::updateOrCreate(
            ['checkpoint_date' => $date],
            [
                'first_entry_id' => $first->id,
                'last_entry_id' => $last->id,
                'entries_count' => $count,
                'cumulative_hash' => hash('sha256', $first->entry_hash.$last->entry_hash.$count),
            ]
        );

        $this->info("Checkpoint {$date}: {$count} entries.");
        return self::SUCCESS;
    }
}
