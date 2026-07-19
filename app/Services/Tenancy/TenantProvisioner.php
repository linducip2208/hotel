<?php

declare(strict_types=1);

namespace App\Services\Tenancy;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantSubscription;
use Illuminate\Support\Facades\Log;

class TenantProvisioner
{
    public function __construct(
        private TenantDatabaseManager $dbManager
    ) {}

    public function provision(Tenant $tenant): Tenant
    {
        if ($tenant->provisioned && $tenant->database_name) {
            return $tenant;
        }

        Log::info("Provisioning tenant {$tenant->slug}");

        if (! $tenant->domains()->exists()) {
            TenantDomain::create([
                'tenant_id' => $tenant->id,
                'domain' => $tenant->slug . '.hotelhub.id',
                'is_primary' => true,
                'is_verified' => true,
                'ssl_status' => 'active',
            ]);
        }

        if ($tenant->plan_id) {
            $plan = Plan::find($tenant->plan_id);
            $isTrialing = (bool) $tenant->trial_ends_at && $tenant->trial_ends_at->isFuture();
            TenantSubscription::firstOrCreate(
                ['tenant_id' => $tenant->id, 'plan_id' => $tenant->plan_id, 'status' => $isTrialing ? 'trialing' : 'active'],
                [
                    'current_period_start' => now()->toDateString(),
                    'current_period_end' => $isTrialing ? $tenant->trial_ends_at->toDateString() : now()->addMonth()->toDateString(),
                    'trial_ends_at' => $tenant->trial_ends_at?->toDateString(),
                    'billing_cycle' => 'monthly',
                    'price_paid_idr' => $plan?->per_room_price_idr ?? $plan?->monthly_price_idr ?? 0,
                ]
            );
        }

        $this->dbManager->provision($tenant);

        return $tenant->fresh();
    }
}
