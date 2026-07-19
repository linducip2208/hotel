<?php

namespace App\Console\Commands;

use App\Services\Fo\MicrostayService;
use Illuminate\Console\Command;

class ExpireMicrostays extends Command
{
    protected $signature = 'hotel:expire-microstays {--property=}';
    protected $description = 'Auto-checkout expired microstay reservations';

    public function handle(MicrostayService $service): int
    {
        $propertyId = $this->option('property');
        if (!$propertyId) {
            $properties = \App\Models\Property::where('is_active', true)->pluck('id');
            foreach ($properties as $pid) {
                $count = $service->expireOverdueMicrostays($pid);
                if ($count > 0) {
                    $this->info("Property {$pid}: {$count} expired microstays auto-checked out.");
                }
            }
        } else {
            $count = $service->expireOverdueMicrostays((int) $propertyId);
            $this->info("{$count} expired microstays auto-checked out.");
        }

        return 0;
    }
}
