<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProvisionTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $tenantId) {}

    public function handle(TenantProvisioner $svc): void
    {
        $tenant = Tenant::findOrFail($this->tenantId);
        $svc->provision($tenant);
    }
}
