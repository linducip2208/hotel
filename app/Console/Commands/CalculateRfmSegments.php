<?php

namespace App\Console\Commands;

use App\Services\Guest\RfmSegmentationService;
use Illuminate\Console\Command;

class CalculateRfmSegments extends Command
{
    protected $signature = 'hotel:calculate-rfm {--property=}';
    protected $description = 'Calculate RFM scores and segment all guests';

    public function handle(RfmSegmentationService $service): int
    {
        $propertyId = $this->option('property');
        if (!$propertyId) {
            $properties = \App\Models\Property::where('is_active', true)->pluck('id');
            foreach ($properties as $pid) {
                $count = $service->calculateAll($pid);
                $this->info("Property {$pid}: {$count} guests segmented.");
            }
        } else {
            $count = $service->calculateAll((int) $propertyId);
            $this->info("{$count} guests segmented.");
        }

        return 0;
    }
}
