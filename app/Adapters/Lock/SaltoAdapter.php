<?php

declare(strict_types=1);

namespace App\Adapters\Lock;

use App\Adapters\BaseAdapter;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;

final class SaltoAdapter extends BaseAdapter implements LockAdapterInterface
{
    protected function defaultHeaders(): array
    {
        return array_merge(parent::defaultHeaders(), [
            'Authorization' => 'Bearer ' . $this->apiKey(),
            'X-Property-Id' => (string) ($this->provider->extra_headers['property_id'] ?? ''),
        ]);
    }

    public function encodeKey(string $roomNumber, Carbon $validFrom, Carbon $validTo, array $guestInfo): array
    {
        $payload = [
            'room_number' => $roomNumber,
            'valid_from' => $validFrom->toIso8601String(),
            'valid_to' => $validTo->toIso8601String(),
            'guest_name' => $guestInfo['name'] ?? 'Guest',
            'guest_id' => $guestInfo['guest_id'] ?? null,
            'reservation_id' => $guestInfo['reservation_id'] ?? null,
            'key_type' => 'guest',
            'access_level' => 'standard',
        ];

        $response = $this->http->post('api/v1/cards/encode', [
            RequestOptions::JSON => $payload,
        ]);

        $data = json_decode((string) $response->getBody(), true);

        return [
            'key_id' => $data['card_id'] ?? $data['id'] ?? null,
            'key_data' => $data['encoded_data'] ?? $data['card_data'] ?? null,
            'room_number' => $roomNumber,
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
            'provider' => 'salto',
        ];
    }

    public function revokeKey(string $roomNumber, string $keyId): bool
    {
        $response = $this->http->post('api/v1/cards/revoke', [
            RequestOptions::JSON => [
                'room_number' => $roomNumber,
                'card_id' => $keyId,
            ],
        ]);

        return $response->getStatusCode() === 200;
    }

    public function getLockStatus(string $roomNumber): array
    {
        $response = $this->http->get("api/v1/doors/{$roomNumber}/status");
        $data = json_decode((string) $response->getBody(), true);

        return [
            'room_number' => $roomNumber,
            'locked' => $data['locked'] ?? true,
            'battery_level' => $data['battery'] ?? null,
            'last_access' => isset($data['lastEvent']['timestamp']) ? Carbon::parse($data['lastEvent']['timestamp']) : null,
            'online' => $data['online'] ?? false,
            'alarms' => $data['alarms'] ?? [],
            'raw' => $data,
        ];
    }

    public function getAuditTrail(string $roomNumber, Carbon $from, Carbon $to): array
    {
        $response = $this->http->get("api/v1/doors/{$roomNumber}/audit", [
            RequestOptions::QUERY => [
                'from' => $from->toIso8601String(),
                'to' => $to->toIso8601String(),
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true);
        $events = $data['events'] ?? $data ?? [];

        return array_map(function ($e) {
            return [
                'timestamp' => Carbon::parse($e['timestamp'] ?? $e['date']),
                'event' => $e['type'] ?? $e['action'] ?? 'unknown',
                'user' => $e['user'] ?? $e['guest_name'] ?? null,
                'details' => $e['details'] ?? $e,
            ];
        }, $events);
    }
}
