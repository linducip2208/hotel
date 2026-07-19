<?php

declare(strict_types=1);

namespace App\Console\Commands\Spa;

use App\Services\Spa\MembershipBillingService;
use Illuminate\Console\Command;

final class RenewMembershipsCommand extends Command
{
    protected $signature = 'spa:renew-memberships';
    protected $description = 'Renew expiring spa memberships and check upcoming expirations';

    public function handle(MembershipBillingService $svc): int
    {
        $this->info('Checking expiring memberships...');
        $expiring = $svc->renewExpiring();
        $this->info("Found {$expiring} memberships expiring within 7 days.");

        $this->info('Processing auto-renewals...');
        $renewed = $svc->processRenewals();
        $this->info("Renewed {$renewed} memberships.");

        return 0;
    }
}
