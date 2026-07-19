<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Services\Accounting\NightAuditService;
use Illuminate\Console\Command;

class NightAuditCloseCommand extends Command
{
    protected $signature = 'night-audit:close {--property=}';
    protected $description = 'Run night audit for all properties (or specified one)';

    public function handle(NightAuditService $svc): int
    {
        $properties = $this->option('property')
            ? Property::where('id', $this->option('property'))->get()
            : Property::all();

        foreach ($properties as $p) {
            $this->info("Running night audit for {$p->name}...");
            try {
                $audit = $svc->run($p);
                $this->line("  ✓ status={$audit->status} ".json_encode($audit->summary));
            } catch (\Throwable $e) {
                $this->error("  ✗ ".$e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
