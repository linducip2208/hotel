<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenant;

use App\Models\Tenant;
use App\Services\Tenancy\TenantDatabaseManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProvisionCommand extends Command
{
    protected $signature = 'tenant:provision {tenant_id? : Tenant UUID or slug} {--all : Provision all pending tenants}';
    protected $description = 'Provision tenant databases and run migrations';

    public function handle(TenantDatabaseManager $manager): int
    {
        if ($this->option('all')) {
            return $this->provisionAll($manager);
        }

        $tenantId = $this->argument('tenant_id');

        if (! $tenantId) {
            $this->error('Provide a tenant_id or use --all.');
            return self::FAILURE;
        }

        $tenant = Tenant::where('id', $tenantId)
            ->orWhere('slug', $tenantId)
            ->first();

        if (! $tenant) {
            $this->error("Tenant not found: {$tenantId}");
            return self::FAILURE;
        }

        $this->provisionSingle($manager, $tenant);

        return self::SUCCESS;
    }

    protected function provisionAll(TenantDatabaseManager $manager): int
    {
        $tenants = Tenant::where('provisioned', false)
            ->whereIn('status', ['trial', 'active'])
            ->get();

        if ($tenants->isEmpty()) {
            $this->info('No pending tenants to provision.');
            return self::SUCCESS;
        }

        $this->info("Provisioning {$tenants->count()} tenant(s)...");

        foreach ($tenants as $tenant) {
            $this->provisionSingle($manager, $tenant);
        }

        return self::SUCCESS;
    }

    protected function provisionSingle(TenantDatabaseManager $manager, Tenant $tenant): void
    {
        $this->info("Provisioning tenant: {$tenant->slug} ({$tenant->id})");

        try {
            $manager->provision($tenant);
            $this->info("  OK: database_name={$tenant->database_name}");
        } catch (\Throwable $e) {
            $this->error("  FAILED: {$e->getMessage()}");
            Log::error("Tenant provision failed: {$tenant->slug}", ['exception' => $e]);
        }
    }
}
