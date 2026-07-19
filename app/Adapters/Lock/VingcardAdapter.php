<?php

declare(strict_types=1);

namespace App\Adapters\Lock;

use App\Adapters\BaseAdapter;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;

final class VingcardAdapter extends BaseAdapter implements LockAdapterInterface
{
    protected function defaultHeaders(): array
    {
        return array_merge(parent::defaultHeaders(), [
            'Authorization' => 'Bearer ' . $this->apiKey(),
            'X-Visionline-Hotel' => (string) ($this->provider->extra_headers['hotel_id'] ?? ''),
        ]);
    }

    public function encodeKey(string $roomNumber, Carbon $validFrom, Carbon $validTo, array $guestInfo): array
    {
        $payload = [
            'roomNumber' => $roomNumber,
            'activationDate' => $validFrom->format('c'),
            'expirationDate' => $validTo->format('c'),
            'guestFirstName' => explode(' ', $guestInfo['name'] ?? 'Guest')[0],
            'guestLastName' => explode(' ', $guestInfo['name'] ?? 'Guest')[1] ?? '',
            'reservationNumber' => (string) ($guestInfo['reservation_id'] ?? ''),
            'keyType' => $guestInfo['mobile_key'] ?? false ? 'MOBILE' : 'RFID',
            'numberOfKeys' => $guestInfo['mobile_key'] ?? false ? 1 : 2,
        ];

        $response = $this->http->post('Visionline/ws/rest/KeyService/keys', [
            RequestOptions::JSON => $payload,
        ]);

        $data = json_decode((string) $response->getBody(), true);

        return [
            'key_id' => $data['keyId'] ?? $data['id'] ?? null,
            'key_data' => $data['encodedKey'] ?? $data['keyData'] ?? null,
            'mobile_invite_url' => $data['inviteUrl'] ?? $data['mobileInviteUrl'] ?? null,
            'room_number' => $roomNumber,
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
            'provider' => 'vingcard',
        ];
    }

    public function revokeKey(string $roomNumber, string $keyId): bool
    {
        $response = $this->http->delete("Visionline/ws/rest/KeyService/keys/{$keyId}", [
            RequestOptions::QUERY => ['roomNumber' => $roomNumber],
        ]);

        return $response->getStatusCode() === 200;
    }

    public function getLockStatus(string $roomNumber): array
    {
        $response = $this->http->get("Visionline/ws/rest/DoorService/doors/{$roomNumber}");
        $data = json_decode((string) $response->getBody(), true);

        return [
            'room_number' => $roomNumber,
            'locked' => $data['locked'] ?? true,
            'battery_level' => $data['batteryLevel'] ?? null,
            'last_access' => isset($data['lastEvent']['timestamp']) ? Carbon::parse($data['lastEvent']['timestamp']) : null,
            'online' => $data['online'] ?? false,
            'alarms' => $data['alarms'] ?? [],
            'raw' => $data,
        ];
    }

    public function getAuditTrail(string $roomNumber, Carbon $from, Carbon $to): array
    {
        $response = $this->http->get("Visionline/ws/rest/DoorService/doors/{$roomNumber}/events", [
            RequestOptions::QUERY => [
                'startDateTime' => $from->toIso8601String(),
                'endDateTime' => $to->toIso8601String(),
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true);
        $events = $data['doorEvents'] ?? $data ?? [];

        return array_map(function ($e) {
            return [
                'timestamp' => Carbon::parse($e['eventTime'] ?? $e['timestamp']),
                'event' => $e['eventType'] ?? $e['type'] ?? 'unknown',
                'user' => $e['userName'] ?? $e['guest'] ?? null,
                'details' => $e,
            ];
        }, $events);
    }
}
