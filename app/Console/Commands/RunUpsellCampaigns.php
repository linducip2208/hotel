<?php

namespace App\Console\Commands;

use App\Services\Revenue\UpsellPreArrivalService;
use Illuminate\Console\Command;

class RunUpsellCampaigns extends Command
{
    protected $signature = 'hotel:upsell-campaigns {--campaign=}';
    protected $description = 'Run active upsell pre-arrival campaigns';

    public function handle(UpsellPreArrivalService $service): int
    {
        $campaignId = $this->option('campaign');
        if ($campaignId) {
            $result = $service->runCampaign((int) $campaignId);
            $this->info(json_encode($result));
        } else {
            $results = $service->runAllActiveCampaigns();
            foreach ($results as $r) {
                $this->info(json_encode($r));
            }
        }

        return 0;
    }
}
