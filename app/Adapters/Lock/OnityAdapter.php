<?php

declare(strict_types=1);

namespace App\Adapters\Lock;

use App\Adapters\BaseAdapter;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;

final class OnityAdapter extends BaseAdapter implements LockAdapterInterface
{
    protected function defaultHeaders(): array
    {
        return array_merge(parent::defaultHeaders(), [
            'X-API-Key' => $this->apiKey(),
            'X-Hotel-Code' => (string) ($this->provider->extra_headers['hotel_code'] ?? ''),
        ]);
    }

    public function encodeKey(string $roomNumber, Carbon $validFrom, Carbon $validTo, array $guestInfo): array
    {
        $payload = [
            'roomNumber' => $roomNumber,
            'startDate' => $validFrom->format('Y-m-d\TH:i:s'),
            'endDate' => $validTo->format('Y-m-d\TH:i:s'),
            'guestName' => $guestInfo['name'] ?? 'Guest',
            'guestId' => (string) ($guestInfo['guest_id'] ?? ''),
            'reservationNumber' => (string) ($guestInfo['reservation_id'] ?? ''),
            'numberOfKeys' => 1,
            'keyTechnology' => 'rfid',
        ];

        $response = $this->http->post('OnPortal/api/keys/issue', [
            RequestOptions::JSON => $payload,
        ]);

        $data = json_decode((string) $response->getBody(), true);

        return [
            'key_id' => $data['keyId'] ?? $data['id'] ?? null,
            'key_data' => $data['keyData'] ?? $data['encodedData'] ?? null,
            'room_number' => $roomNumber,
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
            'provider' => 'onity',
        ];
    }

    public function revokeKey(string $roomNumber, string $keyId): bool
    {
        $response = $this->http->post('OnPortal/api/keys/revoke', [
            RequestOptions::JSON => [
                'roomNumber' => $roomNumber,
                'keyId' => $keyId,
            ],
        ]);

        return $response->getStatusCode() === 200;
    }

    public function getLockStatus(string $roomNumber): array
    {
        $response = $this->http->get("OnPortal/api/locks/{$roomNumber}");
        $data = json_decode((string) $response->getBody(), true);

        return [
            'room_number' => $roomNumber,
            'locked' => $data['isLocked'] ?? true,
            'battery_level' => $data['batteryPercent'] ?? null,
            'last_access' => isset($data['lastAccess']['time']) ? Carbon::parse($data['lastAccess']['time']) : null,
            'online' => $data['online'] ?? false,
            'alarms' => $data['alarms'] ?? [],
            'raw' => $data,
        ];
    }

    public function getAuditTrail(string $roomNumber, Carbon $from, Carbon $to): array
    {
        $response = $this->http->get("OnPortal/api/locks/{$roomNumber}/events", [
            RequestOptions::QUERY => [
                'startTime' => $from->toIso8601String(),
                'endTime' => $to->toIso8601String(),
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true);
        $events = $data['events'] ?? $data ?? [];

        return array_map(function ($e) {
            return [
                'timestamp' => Carbon::parse($e['time'] ?? $e['timestamp']),
                'event' => $e['eventType'] ?? $e['type'] ?? 'unknown',
                'user' => $e['user'] ?? $e['guest'] ?? null,
                'details' => $e,
            ];
        }, $events);
    }
}
