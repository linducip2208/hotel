<?php

declare(strict_types=1);

namespace App\Adapters\Lock;

use App\Adapters\BaseAdapter;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;

final class MiwaAdapter extends BaseAdapter implements LockAdapterInterface
{
    protected function defaultHeaders(): array
    {
        return array_merge(parent::defaultHeaders(), [
            'X-Auth-Token' => $this->apiKey(),
            'X-Facility-Code' => (string) ($this->provider->extra_headers['facility_code'] ?? ''),
        ]);
    }

    public function encodeKey(string $roomNumber, Carbon $validFrom, Carbon $validTo, array $guestInfo): array
    {
        $payload = [
            'facility_code' => $this->provider->extra_headers['facility_code'] ?? '',
            'room_no' => $roomNumber,
            'start_datetime' => $validFrom->format('Y-m-d H:i:s'),
            'end_datetime' => $validTo->format('Y-m-d H:i:s'),
            'guest_name' => $guestInfo['name'] ?? 'Guest',
            'reservation_no' => (string) ($guestInfo['reservation_id'] ?? ''),
            'key_type' => 'RFID',
            'card_count' => 2,
        ];

        $response = $this->http->post('alv2/api/cards/issue', [
            RequestOptions::JSON => $payload,
        ]);

        $data = json_decode((string) $response->getBody(), true);

        return [
            'key_id' => $data['card_id'] ?? $data['id'] ?? null,
            'key_data' => $data['encoded_card'] ?? $data['card_data'] ?? null,
            'room_number' => $roomNumber,
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
            'provider' => 'miwa',
        ];
    }

    public function revokeKey(string $roomNumber, string $keyId): bool
    {
        $response = $this->http->post('alv2/api/cards/revoke', [
            RequestOptions::JSON => [
                'room_no' => $roomNumber,
                'card_id' => $keyId,
            ],
        ]);

        return $response->getStatusCode() === 200;
    }

    public function getLockStatus(string $roomNumber): array
    {
        $response = $this->http->get("alv2/api/locks/{$roomNumber}");
        $data = json_decode((string) $response->getBody(), true);

        return [
            'room_number' => $roomNumber,
            'locked' => $data['locked'] ?? true,
            'battery_level' => $data['battery_pct'] ?? null,
            'last_access' => isset($data['last_event_time']) ? Carbon::parse($data['last_event_time']) : null,
            'online' => $data['online'] ?? false,
            'alarms' => $data['alarms'] ?? [],
            'raw' => $data,
        ];
    }

    public function getAuditTrail(string $roomNumber, Carbon $from, Carbon $to): array
    {
        $response = $this->http->get("alv2/api/locks/{$roomNumber}/logs", [
            RequestOptions::QUERY => [
                'from' => $from->format('Y-m-d H:i:s'),
                'to' => $to->format('Y-m-d H:i:s'),
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true);
        $events = $data['logs'] ?? $data ?? [];

        return array_map(function ($e) {
            return [
                'timestamp' => Carbon::parse($e['event_time'] ?? $e['timestamp']),
                'event' => $e['event_type'] ?? $e['action'] ?? 'unknown',
                'user' => $e['user_name'] ?? $e['guest'] ?? null,
                'details' => $e,
            ];
        }, $events);
    }
}
