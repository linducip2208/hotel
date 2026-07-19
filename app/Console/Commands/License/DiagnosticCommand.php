<?php

namespace App\Console\Commands\License;

use App\Models\LocalLicense;
use App\Services\License\FingerprintGenerator;
use App\Services\License\TokenVerifier;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class DiagnosticCommand extends Command
{
    protected $signature = 'license:diagnostic';
    protected $description = 'Run all license-related health checks';

    public function handle(TokenVerifier $verifier, FingerprintGenerator $gen): int
    {
        $this->info('Running license diagnostic...');

        $checks = [];
        $checks[] = $this->check('Vendor server reachable', function () {
            $client = new Client(['base_uri' => config('license.vendor_base_url'), 'timeout' => 5, 'http_errors' => false]);
            try { $r = $client->get('/health'); return $r->getStatusCode() < 500; }
            catch (\Throwable $e) { return false; }
        });

        $checks[] = $this->check('Public key file present', function () use ($verifier) {
            return $verifier->publicKeyHashOk();
        });

        $local = LocalLicense::current();
        $checks[] = $this->check('LocalLicense row exists', fn () => $local !== null);
        $checks[] = $this->check('Fingerprint set', fn () => $local && (bool) $local->fingerprint);
        $checks[] = $this->check('Install ID set', fn () => $local && (bool) $local->install_id);

        if ($local && $local->fingerprint) {
            $current = $gen->generate($local->install_id ?? null);
            $checks[] = $this->check('Fingerprint matches device', fn () => hash_equals($current, $local->fingerprint));
        }

        if ($local && $local->token_encrypted) {
            $claims = $verifier->verify($local->token_encrypted);
            $checks[] = $this->check('Token verifies', fn () => $claims !== null);
            if ($claims && isset($claims->exp)) {
                $checks[] = $this->check('Token not expired', fn () => $claims->exp > time());
            }
        }

        $failed = collect($checks)->filter(fn ($v) => $v === false)->count();
        $this->line('');
        $this->line($failed === 0 ? '✓ All checks passed.' : "✗ {$failed} checks failed.");
        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    protected function check(string $label, \Closure $fn): bool
    {
        try {
            $ok = (bool) $fn();
        } catch (\Throwable $e) {
            $ok = false;
        }
        $this->line(sprintf('  [%s] %s', $ok ? '✓' : '✗', $label));
        return $ok;
    }
}
