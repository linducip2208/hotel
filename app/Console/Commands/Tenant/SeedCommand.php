<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenant;

use App\Models\Tenant;
use App\Services\Tenancy\TenantDatabaseManager;
use Illuminate\Console\Command;

class SeedCommand extends Command
{
    protected $signature = 'tenant:seed {tenant_id? : Tenant UUID or slug} {--class= : Seeder class name} {--all : Run on all tenants}';
    protected $description = 'Seed tenant databases';

    public function handle(TenantDatabaseManager $manager): int
    {
        $seederClass = $this->option('class') ?: 'Database\\Seeders\\TenantSeeder';

        if ($this->option('all')) {
            return $this->seedAll($manager, $seederClass);
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
            $this->error("Tenant {$tenant->slug} has no database provisioned.");
            return self::FAILURE;
        }

        $this->seedSingle($manager, $tenant, $seederClass);

        return self::SUCCESS;
    }

    protected function seedAll(TenantDatabaseManager $manager, string $seederClass): int
    {
        $tenants = Tenant::whereNotNull('database_name')->where('provisioned', true)->get();

        if ($tenants->isEmpty()) {
            $this->info('No provisioned tenants found.');
            return self::SUCCESS;
        }

        $this->info("Seeding {$tenants->count()} tenant(s)...");

        foreach ($tenants as $tenant) {
            $this->seedSingle($manager, $tenant, $seederClass);
        }

        return self::SUCCESS;
    }

    protected function seedSingle(TenantDatabaseManager $manager, Tenant $tenant, string $seederClass): void
    {
        $this->info("Seeding tenant: {$tenant->slug}");

        try {
            $manager->seed($tenant);
            $this->info("  OK");
        } catch (\Throwable $e) {
            $this->error("  FAILED: {$e->getMessage()}");
        }
    }
}
