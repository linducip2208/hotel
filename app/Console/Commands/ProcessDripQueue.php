<?php

namespace App\Console\Commands;

use App\Services\Marketing\DripCampaignService;
use Illuminate\Console\Command;

class ProcessDripQueue extends Command
{
    protected $signature = 'drip:process';
    protected $description = 'Process pending drip campaign queue items that are due';

    public function handle(DripCampaignService $service): int
    {
        $result = $service->processQueue();

        $this->info("Drip queue processed: {$result['sent']} sent, {$result['failed']} failed");

        return self::SUCCESS;
    }
}
