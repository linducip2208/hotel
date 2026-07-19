<?php

namespace App\Services\License;

use App\Models\LicenseEvent;
use App\Models\LocalLicense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LicenseManager
{
    public function __construct(
        protected TokenVerifier $verifier,
        protected FingerprintGenerator $fingerprint,
        protected LicenseClient $client,
    ) {}

    public function status(): array
    {
        $local = LocalLicense::current();
        if (! $local || ! $local->isPaired()) {
            return ['valid' => false, 'reason' => 'not_paired'];
        }

        if ($local->status === 'revoked') {
            return ['valid' => false, 'reason' => 'revoked'];
        }

        $token = $local->token_encrypted;
        if (! $token) {
            return ['valid' => false, 'reason' => 'missing_token'];
        }

        $claims = Cache::remember('license:claims:'.md5($token), config('license.cache_ttl_seconds'), function () use ($token) {
            return $this->verifier->verify($token);
        });

        if (! $claims) {
            return ['valid' => false, 'reason' => 'invalid_token'];
        }

        if (isset($claims->fingerprint) && $local->fingerprint && ! hash_equals($claims->fingerprint, $local->fingerprint)) {
            return ['valid' => false, 'reason' => 'fingerprint_mismatch'];
        }

        if (isset($claims->exp) && $claims->exp < time()) {
            return $this->checkGrace($local);
        }

        return [
            'valid' => true,
            'plan' => $claims->license->plan ?? null,
            'features' => isset($claims->license->features) ? (array) $claims->license->features : [],
            'valid_until' => $claims->license->valid_until ?? null,
            'last_heartbeat' => optional($local->last_heartbeat_success_at)->toIso8601String(),
        ];
    }

    public function feature(string $key, $default = false)
    {
        $status = $this->status();
        return data_get($status, "features.$key", $default);
    }

    public function pair(string $licenseKey): array
    {
        $local = LocalLicense::firstOrCreate(['id' => 1], ['status' => 'unpaired']);
        $installId = $local->install_id ?? $this->fingerprint->newInstallId();
        $fingerprint = $this->fingerprint->generate($installId);

        $machineInfo = [
            'hostname' => gethostname(),
            'os' => PHP_OS_FAMILY,
            'os_release' => php_uname('r'),
            'php_version' => PHP_VERSION,
            'app_version' => config('app.version', '1.0.0'),
        ];

        $response = $this->client->pair($licenseKey, $fingerprint, $installId, $machineInfo);
        $this->logEvent('pairing.attempt', $response);

        if (! $response['ok'] || empty($response['data']['token'])) {
            return [
                'ok' => false,
                'message' => $response['data']['error'] ?? ($response['error'] ?? 'Pairing failed.'),
            ];
        }

        $local->fill([
            'license_key_hash' => hash('sha256', $licenseKey),
            'token_encrypted' => $response['data']['token'],
            'fingerprint' => $fingerprint,
            'install_id' => $installId,
            'paired_at' => now(),
            'status' => 'paired',
            'last_heartbeat_success_at' => now(),
            'valid_until' => isset($response['data']['valid_until'])
                ? Carbon::parse($response['data']['valid_until'])
                : null,
            'plan' => $response['data']['plan'] ?? null,
            'features' => $response['data']['features'] ?? [],
            'max_rooms' => $response['data']['max_rooms'] ?? null,
            'max_users' => $response['data']['max_users'] ?? null,
        ])->save();

        $this->logEvent('pairing.success', ['license_key_hash' => substr($local->license_key_hash, 0, 12)]);
        return ['ok' => true, 'license' => $local->refresh()];
    }

    public function heartbeat(): array
    {
        $local = LocalLicense::current();
        if (! $local || ! $local->isPaired()) {
            return ['ok' => false, 'reason' => 'not_paired'];
        }

        $local->last_heartbeat_attempt_at = now();
        $local->save();

        $telemetry = [
            'rooms_count' => \App\Models\Room::count(),
            'active_bookings' => \App\Models\Reservation::whereIn('status', ['confirmed', 'checked_in'])->count(),
            'users' => \App\Models\User::count(),
            'app_version' => config('app.version', '1.0.0'),
        ];

        $response = $this->client->heartbeat($local->token_encrypted, $local->fingerprint, $telemetry);

        if (! $response['ok']) {
            $this->logEvent('heartbeat.failed', ['error' => $response['error'] ?? null, 'status' => $response['status']]);
            return ['ok' => false, 'reason' => 'network', 'detail' => $response];
        }

        $data = $response['data'] ?? [];

        if (! ($data['valid'] ?? false)) {
            $reason = $data['reason'] ?? 'unknown';
            $local->status = $reason === 'revoked' ? 'revoked' : 'degraded';
            $local->degrade_reason = $reason;
            $local->save();
            $this->logEvent('heartbeat.invalid', ['reason' => $reason]);
            return ['ok' => false, 'reason' => $reason];
        }

        if (! empty($data['renewed_token'])) {
            $local->token_encrypted = $data['renewed_token'];
        }
        $local->status = 'paired';
        $local->degrade_reason = null;
        $local->last_heartbeat_success_at = now();
        $local->grace_until = now()->addDays(config('license.grace_days'));
        if (! empty($data['valid_until'])) {
            $local->valid_until = Carbon::parse($data['valid_until']);
        }
        $local->save();

        $this->logEvent('heartbeat.success', ['next' => $local->grace_until?->toIso8601String()]);
        return ['ok' => true];
    }

    protected function checkGrace(LocalLicense $local): array
    {
        if ($local->grace_until && $local->grace_until->isFuture()) {
            return ['valid' => true, 'mode' => 'grace', 'grace_until' => $local->grace_until->toIso8601String()];
        }
        $local->status = 'degraded';
        $local->degrade_reason = 'grace_expired';
        $local->save();
        return ['valid' => false, 'reason' => 'grace_expired'];
    }

    protected function logEvent(string $event, array $payload = []): void
    {
        try {
            LicenseEvent::create([
                'event' => $event,
                'payload' => $payload,
                'source_ip' => request()?->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::channel('license')->error('Failed to log license event: '.$e->getMessage());
        }
    }
}
