<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenant;

use App\Models\Tenant;
use App\Services\Tenancy\TenantDatabaseManager;
use Illuminate\Console\Command;

class MigrateCommand extends Command
{
    protected $signature = 'tenant:migrate {tenant_id? : Tenant UUID or slug} {--all : Run on all tenants}';
    protected $description = 'Run migrations on tenant databases';

    public function handle(TenantDatabaseManager $manager): int
    {
        if ($this->option('all')) {
            return $this->migrateAll($manager);
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

        if (! $tenant->database_name) {
            $this->error("Tenant {$tenant->slug} has no database provisioned. Run tenant:provision first.");
            return self::FAILURE;
        }

        $this->migrateSingle($manager, $tenant);

        return self::SUCCESS;
    }

    protected function migrateAll(TenantDatabaseManager $manager): int
    {
        $tenants = Tenant::whereNotNull('database_name')->where('provisioned', true)->get();

        if ($tenants->isEmpty()) {
            $this->info('No provisioned tenants found.');
            return self::SUCCESS;
        }

        $this->info("Running migrations on {$tenants->count()} tenant(s)...");

        foreach ($tenants as $tenant) {
            $this->migrateSingle($manager, $tenant);
        }

        return self::SUCCESS;
    }

    protected function migrateSingle(TenantDatabaseManager $manager, Tenant $tenant): void
    {
        $this->info("Migrating tenant: {$tenant->slug} ({$tenant->database_name})");

        try {
            $manager->migrate($tenant);
            $this->info("  OK");
        } catch (\Throwable $e) {
            $this->error("  FAILED: {$e->getMessage()}");
        }
    }
}
