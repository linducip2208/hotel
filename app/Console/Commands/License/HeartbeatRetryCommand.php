<?php

namespace App\Console\Commands\License;

use App\Models\LocalLicense;
use App\Services\License\LicenseManager;
use Illuminate\Console\Command;

class HeartbeatRetryCommand extends Command
{
    protected $signature = 'license:heartbeat-retry';
    protected $description = 'Retry heartbeat if last success > 24h ago';

    public function handle(LicenseManager $manager): int
    {
        $local = LocalLicense::current();
        if (! $local || ! $local->isPaired()) {
            return self::SUCCESS;
        }

        if ($local->last_heartbeat_success_at && $local->last_heartbeat_success_at->isAfter(now()->subDay())) {
            return self::SUCCESS;
        }

        $result = $manager->heartbeat();
        $this->line($result['ok'] ? 'Heartbeat retry OK.' : 'Heartbeat retry failed.');
        return self::SUCCESS;
    }
}
