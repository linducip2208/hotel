<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenant;

use App\Models\Tenant;
use App\Services\Tenancy\TenantDatabaseManager;
use Illuminate\Console\Command;

class DestroyCommand extends Command
{
    protected $signature = 'tenant:destroy {tenant_id : Tenant UUID or slug} {--force : Skip confirmation}';
    protected $description = 'Destroy a tenant database with confirmation';

    public function handle(TenantDatabaseManager $manager): int
    {
        $tenantId = $this->argument('tenant_id');

        $tenant = Tenant::where('id', $tenantId)
            ->orWhere('slug', $tenantId)
            ->first();

        if (! $tenant) {
            $this->error("Tenant not found: {$tenantId}");
            return self::FAILURE;
        }

        if (! $tenant->database_name) {
            $this->warn("Tenant {$tenant->slug} has no database provisioned.");
            $tenant->delete();
            $this->info("Tenant record deleted.");
            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->warn("╔════════════════════════════════════════════════════╗");
            $this->warn("║  WARNING: This will DROP the tenant database!      ║");
            $this->warn("║  Database: {$tenant->database_name}");
            $this->warn("║  Tenant:   {$tenant->slug}");
            $this->warn("╚════════════════════════════════════════════════════╝");

            if (! $this->confirm("Type 'yes' to confirm permanent destruction of tenant '{$tenant->slug}'")) {
                $this->info('Cancelled.');
                return self::SUCCESS;
            }
        }

        $this->info("Destroying tenant: {$tenant->slug}");

        try {
            $manager->destroy($tenant);
            $tenant->delete();
            $this->info("  OK — tenant and database destroyed.");
        } catch (\Throwable $e) {
            $this->error("  FAILED: {$e->getMessage()}");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
