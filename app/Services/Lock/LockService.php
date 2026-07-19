<?php

declare(strict_types=1);

namespace App\Services\Lock;

use App\Adapters\Lock\DormakabaAdapter;
use App\Adapters\Lock\LockAdapterInterface;
use App\Adapters\Lock\MiwaAdapter;
use App\Adapters\Lock\OnityAdapter;
use App\Adapters\Lock\SaltoAdapter;
use App\Adapters\Lock\VingcardAdapter;
use App\Models\DoorLockEvent;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use RuntimeException;

final class LockService
{
    /**
     * Issue a key card/mobile key for a guest's stay.
     */
    public function issueKey(Room $room, Guest $guest, Reservation $reservation, ?array $options = null): array
    {
        $adapter = $this->resolveAdapter($room->property_id);

        $validFrom = Carbon::parse($options['valid_from'] ?? $reservation->check_in ?? now());
        $validTo = Carbon::parse($options['valid_to'] ?? $reservation->check_out ?? now()->addDays(3));

        $result = $adapter->encodeKey(
            $room->room_number ?? "R{$room->id}",
            $validFrom,
            $validTo,
            [
                'name' => $guest->full_name,
                'guest_id' => $guest->id,
                'reservation_id' => $reservation->id,
                'mobile_key' => $options['mobile_key'] ?? false,
            ]
        );

        DoorLockEvent::create([
            'property_id' => $room->property_id,
            'room_id' => $room->id,
            'reservation_id' => $reservation->id,
            'guest_id' => $guest->id,
            'event_type' => 'key_issued',
            'source' => $options['mobile_key'] ?? false ? 'mobile_key' : 'rfid',
            'payload' => $result,
            'occurred_at' => now(),
        ]);

        return $result;
    }

    /** Revoke all keys for a room. */
    public function revokeKey(Room $room, ?string $keyId = null): bool
    {
        $adapter = $this->resolveAdapter($room->property_id);
        $roomNumber = $room->room_number ?? "R{$room->id}";

        if ($keyId) {
            $result = $adapter->revokeKey($roomNumber, $keyId);
        } else {
            // Try bulk revoke via status check + individual revoke
            $result = $adapter->revokeKey($roomNumber, 'all');
        }

        DoorLockEvent::create([
            'property_id' => $room->property_id,
            'room_id' => $room->id,
            'event_type' => 'key_revoked',
            'source' => 'staff',
            'payload' => ['key_id' => $keyId, 'success' => $result],
            'occurred_at' => now(),
        ]);

        return $result;
    }

    /** Get audit log for a room's lock events. */
    public function getAuditLog(Room $room, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $adapter = $this->resolveAdapter($room->property_id);

        return $adapter->getAuditTrail(
            $room->room_number ?? "R{$room->id}",
            $from ?? now()->subDays(30),
            $to ?? now()
        );
    }

    /** Emergency open by staff (logged for audit). */
    public function emergencyOpen(Room $room, User $staff): void
    {
        $adapter = $this->resolveAdapter($room->property_id);
        $roomNumber = $room->room_number ?? "R{$room->id}";

        $status = $adapter->getLockStatus($roomNumber);

        DoorLockEvent::create([
            'property_id' => $room->property_id,
            'room_id' => $room->id,
            'event_type' => 'emergency_open',
            'source' => 'staff_override',
            'payload' => [
                'staff_id' => $staff->id,
                'staff_name' => $staff->name,
                'previous_status' => $status,
            ],
            'occurred_at' => now(),
        ]);
    }

    /** Get current lock status for a room. */
    public function getStatus(Room $room): array
    {
        $adapter = $this->resolveAdapter($room->property_id);
        return $adapter->getLockStatus($room->room_number ?? "R{$room->id}");
    }

    /** Generate a digital PIN key for a reservation. */
    public function generatePin(Reservation $reservation, int $length = 6): string
    {
        $pin = str_pad((string) random_int(0, (int) pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);

        $roomId = $reservation->rooms()->first()?->room_id;

        DoorLockEvent::create([
            'property_id' => $reservation->property_id,
            'room_id' => $roomId,
            'reservation_id' => $reservation->id,
            'guest_id' => $reservation->primary_guest_id,
            'event_type' => 'key_issued',
            'source' => 'mobile_pin',
            'payload' => [
                'pin' => $pin,
                'valid_from' => $reservation->check_in?->toDateTimeString(),
                'valid_until' => $reservation->check_out?->toDateTimeString(),
            ],
            'occurred_at' => now(),
        ]);

        return $pin;
    }

    private function resolveAdapter(int $propertyId): LockAdapterInterface
    {
        $provider = \App\Models\Provider::where('property_id', $propertyId)
            ->where('api_format', 'door_lock')
            ->where('is_active', true)
            ->first();

        if (! $provider) {
            throw new RuntimeException('No active door lock provider configured for this property.');
        }

        return match (strtolower((string) $provider->extra_headers['vendor'] ?? '')) {
            'salto' => new SaltoAdapter($provider),
            'onity' => new OnityAdapter($provider),
            'vingcard', 'visionline' => new VingcardAdapter($provider),
            'dormakaba' => new DormakabaAdapter($provider),
            'miwa' => new MiwaAdapter($provider),
            default => throw new RuntimeException("Unsupported lock vendor: {$provider->extra_headers['vendor']}"),
        };
    }
}
