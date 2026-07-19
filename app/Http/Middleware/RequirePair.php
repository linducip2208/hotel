<?php

namespace App\Http\Middleware;

use App\Services\LicenseClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block all routes until the app is paired to a whitelabel.co.id license.
 * Only `/__pair*`, the health endpoint, and the dev-allowlist are accessible
 * without a valid .license.lock for the current host.
 */
class RequirePair
{
    public function __construct(private LicenseClient $client) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        $domain = strtolower($request->getHost());
        $data   = $this->client->verify($domain);

        if ($data) {
            $request->attributes->set('license', $data);
            return $next($request);
        }

        return redirect()->to('/__pair');
    }

    private function shouldBypass(Request $request): bool
    {
        $path = '/' . ltrim($request->path(), '/');

        if (str_starts_with($path, '/__pair')) return true;
        if (str_starts_with($path, '/setup')) return true;

        if ($path === '/health' || $path === '/up') return true;
        if (str_starts_with($path, '/_debugbar')) return true;

        if ($path === '/sitemap.xml' || str_starts_with($path, '/sitemap-')) return true;
        if ($path === '/robots.txt') return true;

        if ($this->isLocalDev($request->getHost())) {
            return true;
        }

        return false;
    }

    private function isLocalDev(string $host): bool
    {
        return $host === 'localhost'
            || $host === '127.0.0.1'
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.localhost');
    }
}
