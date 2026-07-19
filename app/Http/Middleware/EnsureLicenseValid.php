<?php

namespace App\Http\Middleware;

use App\Models\LocalLicense;
use App\Services\License\LicenseManager;
use App\Services\LicenseClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLicenseValid
{
    public function __construct(
        protected LicenseManager $manager,
        protected LicenseClient $marketplace,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isExempt($request)) {
            return $next($request);
        }

        // Marketplace pairing (whitelabel.co.id, kit v3) is the canonical license
        // gate. RequirePair middleware already enforced that before reaching here,
        // so we accept its state and skip the legacy LocalLicense gate.
        if ($this->marketplaceLicenseActive($request)) {
            return $next($request);
        }

        $local = LocalLicense::current();
        if (! $local || $local->status === 'unpaired') {
            return redirect('/setup/wizard');
        }

        $status = $this->manager->status();

        if (! $status['valid']) {
            $reason = $status['reason'] ?? 'invalid';

            if (in_array($reason, ['fingerprint_mismatch', 'revoked', 'grace_expired'], true)) {
                return response()->view('errors.license-locked', [
                    'reason' => $reason,
                    'license' => $local,
                ], 423);
            }

            return response()->view('errors.license-invalid', [
                'reason' => $reason,
                'license' => $local,
            ], 451);
        }

        if (($status['mode'] ?? null) === 'grace') {
            $request->attributes->set('license.grace', true);
            $request->attributes->set('license.grace_until', $status['grace_until']);
        }

        $request->attributes->set('license.features', $status['features'] ?? []);
        $request->attributes->set('license.plan', $status['plan'] ?? null);

        return $next($request);
    }

    protected function isExempt(Request $request): bool
    {
        return $request->is(
            'setup/*',
            'admin/login',
            'health',
            'api/license/*',
            'storage/*',
            'build/*',
            'favicon.ico',
            'robots.txt',
        );
    }

    protected function marketplaceLicenseActive(Request $request): bool
    {
        $domain = strtolower($request->getHost());

        if ($this->marketplace->verify($domain)) {
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
