<?php

declare(strict_types=1);

namespace App\Adapters\Lock;

use App\Adapters\BaseAdapter;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;

final class DormakabaAdapter extends BaseAdapter implements LockAdapterInterface
{
    protected function defaultHeaders(): array
    {
        return array_merge(parent::defaultHeaders(), [
            'X-API-Key' => $this->apiKey(),
            'X-Property-Key' => (string) ($this->provider->extra_headers['property_key'] ?? ''),
        ]);
    }

    public function encodeKey(string $roomNumber, Carbon $validFrom, Carbon $validTo, array $guestInfo): array
    {
        $payload = [
            'doorReference' => $roomNumber,
            'credentialType' => 'GUEST',
            'validityStart' => $validFrom->format('Y-m-d\TH:i:s\Z'),
            'validityEnd' => $validTo->format('Y-m-d\TH:i:s\Z'),
            'guestInformation' => [
                'name' => $guestInfo['name'] ?? 'Guest',
                'reservationReference' => (string) ($guestInfo['reservation_id'] ?? ''),
            ],
            'credentialFormat' => 'MOBILE_FIRST',
        ];

        $response = $this->http->post('ambiance/api/v1/credentials', [
            RequestOptions::JSON => $payload,
        ]);

        $data = json_decode((string) $response->getBody(), true);

        return [
            'key_id' => $data['credentialId'] ?? $data['id'] ?? null,
            'key_data' => $data['credentialData'] ?? $data['encodedData'] ?? null,
            'mobile_qr' => $data['qrCode'] ?? null,
            'room_number' => $roomNumber,
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
            'provider' => 'dormakaba',
        ];
    }

    public function revokeKey(string $roomNumber, string $keyId): bool
    {
        $response = $this->http->delete("ambiance/api/v1/credentials/{$keyId}", [
            RequestOptions::QUERY => ['doorReference' => $roomNumber],
        ]);

        return $response->getStatusCode() === 200;
    }

    public function getLockStatus(string $roomNumber): array
    {
        $response = $this->http->get("ambiance/api/v1/doors/{$roomNumber}/status");
        $data = json_decode((string) $response->getBody(), true);

        return [
            'room_number' => $roomNumber,
            'locked' => $data['locked'] ?? true,
            'battery_level' => $data['batteryLevel'] ?? null,
            'last_access' => isset($data['lastActivity']['timestamp']) ? Carbon::parse($data['lastActivity']['timestamp']) : null,
            'online' => $data['online'] ?? false,
            'alarms' => $data['alarms'] ?? [],
            'raw' => $data,
        ];
    }

    public function getAuditTrail(string $roomNumber, Carbon $from, Carbon $to): array
    {
        $response = $this->http->get("ambiance/api/v1/doors/{$roomNumber}/audit", [
            RequestOptions::QUERY => [
                'from' => $from->toIso8601String(),
                'to' => $to->toIso8601String(),
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true);
        $events = $data['auditRecords'] ?? $data ?? [];

        return array_map(function ($e) {
            return [
                'timestamp' => Carbon::parse($e['createdAt'] ?? $e['timestamp']),
                'event' => $e['action'] ?? $e['type'] ?? 'unknown',
                'user' => $e['user'] ?? $e['guestName'] ?? null,
                'details' => $e,
            ];
        }, $events);
    }
}
