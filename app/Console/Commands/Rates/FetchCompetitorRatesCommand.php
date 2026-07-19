<?php

declare(strict_types=1);

namespace App\Console\Commands\Rates;

use App\Services\Rms\RateShopperService;
use Carbon\Carbon;
use Illuminate\Console\Command;

final class FetchCompetitorRatesCommand extends Command
{
    protected $signature = 'rates:fetch-competitor {property_id?} {--date=today}';
    protected $description = 'Fetch competitor hotel rates for rate shopping comparison';

    public function handle(RateShopperService $svc): int
    {
        $propertyId = $this->argument('property_id') ? (int) $this->argument('property_id') : null;
        $date = Carbon::parse($this->option('date') ?? 'today');

        $this->info("Fetching competitor rates for {$date->toDateString()}...");

        if ($propertyId) {
            $result = $svc->fetchCompetitorRates($propertyId, $date);
            if (isset($result['cached'])) {
                $this->warn($result['message']);
            } else {
                $this->info("Snapshot saved. ARI: {$result['rate_index']}");
            }
        } else {
            // Fetch for all active properties
            $properties = \App\Models\Property::where('is_active', true)->get();
            foreach ($properties as $p) {
                try {
                    $result = $svc->fetchCompetitorRates($p->id, $date);
                    $this->line("  {$p->name}: " . ($result['rate_index'] ?? 'cached'));
                } catch (\Throwable $e) {
                    $this->error("  {$p->name}: {$e->getMessage()}");
                }
            }
        }

        return 0;
    }
}
