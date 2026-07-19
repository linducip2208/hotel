<?php

namespace App\Services\License;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class LicenseClient
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => rtrim(config('license.vendor_base_url'), '/').'/',
            'timeout' => 15,
            'connect_timeout' => 5,
            'http_errors' => false,
        ]);
    }

    public function pair(string $licenseKey, string $fingerprint, string $installId, array $machineInfo): array
    {
        return $this->call(config('license.pairing_endpoint'), [
            'license_key' => $licenseKey,
            'fingerprint' => $fingerprint,
            'install_id' => $installId,
            'machine_info' => $machineInfo,
        ]);
    }

    public function heartbeat(string $token, string $fingerprint, array $telemetry): array
    {
        return $this->call(config('license.heartbeat_endpoint'), [
            'token' => $token,
            'fingerprint' => $fingerprint,
            'telemetry' => $telemetry,
        ]);
    }

    public function migrate(string $licenseKey, string $oldFingerprint, string $newFingerprint, array $machineInfo, string $reason): array
    {
        return $this->call(config('license.migrate_endpoint'), [
            'license_key' => $licenseKey,
            'old_fingerprint' => $oldFingerprint,
            'new_fingerprint' => $newFingerprint,
            'machine_info' => $machineInfo,
            'reason' => $reason,
        ]);
    }

    protected function call(string $path, array $body): array
    {
        try {
            $response = $this->http->post(ltrim($path, '/'), [
                'json' => $body,
                'headers' => ['Accept' => 'application/json'],
            ]);
            $payload = json_decode((string) $response->getBody(), true) ?? [];
            return [
                'ok' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
                'status' => $response->getStatusCode(),
                'data' => $payload,
            ];
        } catch (GuzzleException $e) {
            return [
                'ok' => false,
                'status' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }
}
