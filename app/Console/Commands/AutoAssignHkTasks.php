<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Services\Hk\TaskAutoAssignmentService;
use Illuminate\Console\Command;

class AutoAssignHkTasks extends Command
{
    protected $signature = 'hk:auto-assign';
    protected $description = 'Auto-create checkout cleaning tasks and assign to attendants';

    public function handle(TaskAutoAssignmentService $service): void
    {
        $properties = Property::where('is_active', true)->get();

        foreach ($properties as $property) {
            $created = $service->createCheckoutTasks($property);
            $assigned = $service->autoAssign($property);

            $this->info("{$property->name}: {$created} tasks created, {$assigned['assigned']}/{$assigned['total']} assigned");
        }
    }
}
