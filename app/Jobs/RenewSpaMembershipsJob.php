<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Spa\MembershipBillingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class RenewSpaMembershipsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(MembershipBillingService $svc): void
    {
        $expiring = $svc->renewExpiring();
        $renewed = $svc->processRenewals();
    }
}
