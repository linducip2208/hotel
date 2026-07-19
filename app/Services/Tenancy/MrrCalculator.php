<?php

namespace App\Services\Tenancy;

use App\Models\Tenant;
use App\Models\TenantSubscription;

class MrrCalculator
{
    public function totalMrr(): float
    {
        return (float) TenantSubscription::where('status', 'active')
            ->where('billing_cycle', 'monthly')
            ->sum('price_paid_idr');
    }

    public function arr(): float
    {
        return $this->totalMrr() * 12;
    }

    public function activeTenantsCount(): int
    {
        return Tenant::where('status', 'active')->count();
    }

    public function trialTenantsCount(): int
    {
        return Tenant::where('status', 'trial')->count();
    }

    public function churnRatePct(int $daysWindow = 30): float
    {
        $churned = Tenant::whereNotNull('churned_at')
            ->where('churned_at', '>=', now()->subDays($daysWindow))->count();
        $total = Tenant::whereIn('status', ['active', 'churned', 'suspended'])->count();
        return $total > 0 ? round(($churned / $total) * 100, 2) : 0;
    }
}
