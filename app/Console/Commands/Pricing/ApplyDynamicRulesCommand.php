<?php

namespace App\Console\Commands\Pricing;

use App\Models\Property;
use App\Services\Pricing\DynamicPricingService;
use Illuminate\Console\Command;

class ApplyDynamicRulesCommand extends Command
{
    protected $signature   = 'pricing:apply-dynamic-rules {--property= : Specific property ID}';
    protected $description = 'Evaluate dynamic pricing rules and upsert rate overrides for all properties';

    public function handle(DynamicPricingService $service): int
    {
        $query = Property::where('is_active', true);
        if ($id = $this->option('property')) {
            $query->where('id', $id);
        }

        $properties = $query->get();
        $total = 0;

        foreach ($properties as $property) {
            $applied = $service->applyRules($property);
            $total += $applied;
            $this->line("  {$property->name}: {$applied} rules applied");
        }

        $this->info("Dynamic pricing complete — {$total} overrides upserted across {$properties->count()} properties.");
        return self::SUCCESS;
    }
}
