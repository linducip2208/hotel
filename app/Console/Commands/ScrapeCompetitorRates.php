<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\RateScraperTarget;
use App\Services\Rms\RateScraperService;
use Illuminate\Console\Command;

class ScrapeCompetitorRates extends Command
{
    protected $signature = 'hotel:scrape-rates {--property=} {--target=}';

    protected $description = 'Scrape competitor rates and generate alerts';

    public function handle(RateScraperService $service): int
    {
        $propertyId = $this->option('property');
        $targetId = $this->option('target');

        if ($targetId) {
            $target = RateScraperTarget::findOrFail($targetId);
            $results = $service->scrapeTarget($target);
            $this->info("Scraped {$target->name}: ".count($results).' dates');
        } elseif ($propertyId) {
            $results = $service->scrapeAll((int) $propertyId);
            $this->info("Scraped property {$propertyId}: ".count($results).' targets');
        } else {
            $properties = Property::where('is_active', true)->pluck('id');
            foreach ($properties as $pid) {
                $results = $service->scrapeAll($pid);
                $this->info("Property {$pid}: ".count($results).' targets scraped.');
            }
        }

        return 0;
    }
}
