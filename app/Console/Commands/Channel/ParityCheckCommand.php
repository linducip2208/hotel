<?php

namespace App\Console\Commands\Channel;

use App\Models\Property;
use App\Services\Channel\ParityMonitorService;
use Illuminate\Console\Command;

class ParityCheckCommand extends Command
{
    protected $signature   = 'parity:check {--property= : Specific property ID}';
    protected $description = 'Compare live OTA rates against direct rates and create parity alerts';

    public function handle(ParityMonitorService $monitor): int
    {
        $query = Property::where('is_active', true);
        if ($id = $this->option('property')) {
            $query->where('id', $id);
        }

        $properties = $query->get();
        $totalAlerts = 0;

        foreach ($properties as $property) {
            $count = $monitor->checkAndAlert($property);
            $totalAlerts += $count;
            if ($count > 0) {
                $this->warn("  {$property->name}: {$count} parity breach(es) detected");
            } else {
                $this->line("  {$property->name}: OK");
            }
        }

        $this->info("Parity check complete — {$totalAlerts} alert(s) created.");
        return self::SUCCESS;
    }
}
