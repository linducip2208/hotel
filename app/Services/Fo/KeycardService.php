<?php

namespace App\Services\Fo;

use App\Models\KeycardInventory;
use App\Models\KeycardType;
use App\Models\Reservation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KeycardService
{
    public function getInventoryOverview(int $propertyId): array
    {
        $cards = KeycardInventory::where('property_id', $propertyId)->get();

        return [
            'total' => $cards->count(),
            'available' => $cards->where('status', 'available')->count(),
            'assigned' => $cards->where('status', 'assigned')->count(),
            'lost' => $cards->where('status', 'lost')->count(),
            'damaged' => $cards->where('status', 'damaged')->count(),
        ];
    }

    public function getAvailableCards(int $propertyId): Collection
    {
        return KeycardInventory::where('property_id', $propertyId)
            ->where('status', 'available')
            ->with('keycardType')
            ->orderBy('card_number')
            ->get();
    }

    public function issue(int $cardId, int $reservationId, ?int $roomId = null, ?int $guestId = null): KeycardInventory
    {
        return DB::transaction(function () use ($cardId, $reservationId, $roomId, $guestId) {
            $card = KeycardInventory::findOrFail($cardId);

            if ($card->status !== 'available') {
                throw new \RuntimeException('Kartu tidak tersedia — status: ' . $card->status);
            }

            $card->update([
                'status' => 'assigned',
                'assigned_to_reservation_id' => $reservationId,
                'assigned_to_room_id' => $roomId,
                'current_guest_id' => $guestId,
                'issued_at' => now(),
                'returned_at' => null,
            ]);

            return $card;
        });
    }

    public function returnCard(int $cardId): KeycardInventory
    {
        return DB::transaction(function () use ($cardId) {
            $card = KeycardInventory::findOrFail($cardId);

            if ($card->status !== 'assigned') {
                throw new \RuntimeException('Kartu tidak dalam status assigned.');
            }

            $card->update([
                'status' => 'available',
                'assigned_to_reservation_id' => null,
                'assigned_to_room_id' => null,
                'current_guest_id' => null,
                'returned_at' => now(),
                'times_reused' => ($card->times_reused ?? 0) + 1,
            ]);

            return $card;
        });
    }

    public function markLost(int $cardId): KeycardInventory
    {
        $card = KeycardInventory::findOrFail($cardId);
        $card->update([
            'status' => 'lost',
            'returned_at' => now(),
        ]);
        return $card;
    }

    public function markDamaged(int $cardId): KeycardInventory
    {
        $card = KeycardInventory::findOrFail($cardId);
        $card->update([
            'status' => 'damaged',
            'returned_at' => now(),
        ]);
        return $card;
    }

    public function getActiveAssignments(int $propertyId): Collection
    {
        return KeycardInventory::where('property_id', $propertyId)
            ->where('status', 'assigned')
            ->with(['keycardType', 'assignedRoom', 'assignedReservation', 'currentGuest'])
            ->orderBy('issued_at', 'desc')
            ->get();
    }

    public function getTypes(int $propertyId): Collection
    {
        return KeycardType::where('property_id', $propertyId)
            ->where('is_active', true)
            ->withCount('inventory')
            ->orderBy('name')
            ->get();
    }
}
