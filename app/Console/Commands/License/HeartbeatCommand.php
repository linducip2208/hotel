<?php

namespace App\Console\Commands\License;

use App\Services\License\LicenseManager;
use Illuminate\Console\Command;

class HeartbeatCommand extends Command
{
    protected $signature = 'license:heartbeat';
    protected $description = 'Send heartbeat to vendor license server';

    public function handle(LicenseManager $manager): int
    {
        $result = $manager->heartbeat();
        if ($result['ok']) {
            $this->info('Heartbeat OK.');
            return self::SUCCESS;
        }
        $this->error('Heartbeat failed: '.($result['reason'] ?? 'unknown'));
        return self::FAILURE;
    }
}
