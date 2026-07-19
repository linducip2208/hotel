<?php

namespace App\Http\Middleware;

use App\Models\LocalLicense;
use App\Services\LicenseClient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class InjectLicenseStatus
{
    public function __construct(private LicenseClient $marketplace) {}

    public function handle(Request $request, Closure $next): Response
    {
        $local = LocalLicense::current();
        $paired = $this->isPaired($request, $local);

        View::share('licenseStatus', [
            'paired'      => $paired,
            'mode'        => $request->attributes->get('license.grace') ? 'grace' : 'normal',
            'grace_until' => $request->attributes->get('license.grace_until'),
            'features'    => $request->attributes->get('license.features', []),
            'plan'        => $request->attributes->get('license.plan'),
        ]);
        return $next($request);
    }

    private function isPaired(Request $request, ?LocalLicense $local): bool
    {
        if ($local?->isPaired()) {
            return true;
        }

        $domain = strtolower($request->getHost());
        if ($this->marketplace->isPaired($domain)) {
            return true;
        }

        if ($this->isLocalDev($domain)) {
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
