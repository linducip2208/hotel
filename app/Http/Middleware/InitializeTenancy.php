<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\TenantDomain;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancy
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('hotel.mode') !== 'saas') {
            return $next($request);
        }

        if ($request->is('admin*') || $request->is('api/admin*')) {
            return $next($request);
        }

        $tenant = $this->resolveTenant($request);

        if (! $tenant) {
            abort(404, __('Tenant not found.'));
        }

        if ($tenant->isSuspended()) {
            abort(423, __('This account is currently suspended. Please contact support.'));
        }

        $tenant->update(['last_active_at' => now()]);

        app()->instance('current_tenant', $tenant);
        app()->instance(Tenant::class, $tenant);

        $this->configureTenantDatabase($tenant);

        return $next($request);
    }

    protected function resolveTenant(Request $request): ?Tenant
    {
        if ($tenantId = $request->header('X-Tenant-ID')) {
            return Tenant::find($tenantId);
        }

        if ($tenantId = session('current_tenant_id')) {
            return Tenant::find($tenantId);
        }

        $host = $request->getHost();

        $domain = TenantDomain::where('domain', $host)
            ->orWhere('domain', 'www.' . $host)
            ->first();

        if ($domain) {
            session(['current_tenant_id' => $domain->tenant_id]);
            return $domain->tenant;
        }

        return null;
    }

    protected function configureTenantDatabase(Tenant $tenant): void
    {
        $databaseName = $tenant->database_name;

        if (! $databaseName) {
            return;
        }

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

        Config::set('database.default', 'tenant');
        DB::purge('tenant');
    }
}
