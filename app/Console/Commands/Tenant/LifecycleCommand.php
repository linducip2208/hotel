<?php

namespace App\Console\Commands\Tenant;

use App\Services\Tenancy\TenantLifecycleService;
use Illuminate\Console\Command;

class LifecycleCommand extends Command
{
    protected $signature = 'tenant:lifecycle';
    protected $description = 'Run tenant lifecycle: trial countdown, suspend past_due, churn after 90d';

    public function handle(TenantLifecycleService $svc): int
    {
        $stats = $svc->processAll();
        foreach ($stats as $k => $v) {
            $this->line("  $k: $v");
        }
        return self::SUCCESS;
    }
}
