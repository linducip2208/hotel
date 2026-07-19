<?php

declare(strict_types=1);

namespace App\Services\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class TenantDatabaseManager
{
    public function provision(Tenant $tenant): void
    {
        $databaseName = $this->getDatabaseName($tenant);

        $tenant->update(['database_name' => $databaseName]);

        $this->createDatabase($databaseName);

        $tenant->update([
            'provisioned' => true,
            'provisioned_at' => now(),
        ]);

        $this->migrate($tenant);
        $this->seed($tenant);

        $tenant->logEvent('provisioned', ['database_name' => $databaseName]);

        Log::info("Provisioned tenant {$tenant->slug} database: {$databaseName}");
    }

    public function destroy(Tenant $tenant): void
    {
        $databaseName = $tenant->database_name;

        if ($databaseName) {
            DB::statement("DROP DATABASE IF EXISTS `{$databaseName}`");
            Log::info("Dropped tenant {$tenant->slug} database: {$databaseName}");
        }

        $tenant->update([
            'provisioned' => false,
            'database_name' => null,
            'provisioned_at' => null,
        ]);
    }

    public function backup(Tenant $tenant): string
    {
        $databaseName = $tenant->database_name;
        $host = $tenant->db_host ?? env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $username = env('DB_USERNAME', 'forge');
        $password = env('DB_PASSWORD', '');
        $backupDir = storage_path("backups/tenants/{$tenant->slug}");

        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = $backupDir . '/' . now()->format('Y-m-d_His') . '.sql';

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($databaseName),
            escapeshellarg($filename)
        );

        Process::run($command);

        Log::info("Backed up tenant {$tenant->slug} to {$filename}");

        return $filename;
    }

    public function migrate(Tenant $tenant): void
    {
        $this->configureTenantConnection($tenant);

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations',
            '--force' => true,
        ]);

        Log::info("Ran migrations for tenant {$tenant->slug}");
    }

    public function seed(Tenant $tenant): void
    {
        $this->configureTenantConnection($tenant);

        Artisan::call('db:seed', [
            '--database' => 'tenant',
            '--class' => 'Database\\Seeders\\TenantSeeder',
            '--force' => true,
        ]);
    }

    protected function createDatabase(string $databaseName): void
    {
        $charset = 'utf8mb4';
        $collation = 'utf8mb4_unicode_ci';

        DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET {$charset} COLLATE {$collation}");

        Log::info("Created database: {$databaseName}");
    }

    protected function getDatabaseName(Tenant $tenant): string
    {
        return 'tenant_' . substr($tenant->id, 0, 8);
    }

    protected function configureTenantConnection(Tenant $tenant): void
    {
        $databaseName = $tenant->database_name;

        Config::set('database.connections.tenant', [
            'driver' => env('DB_CONNECTION', 'mysql'),
            'url' => env('DB_URL'),
            'host' => $tenant->db_host ?? env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB',
        ]);

        DB::purge('tenant');
    }
}
