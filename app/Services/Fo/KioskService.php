<?php

namespace App\Services;

use App\Models\KioskSession;
use App\Models\Reservation;
use App\Models\Guest;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Str;

class KioskService
{
    public function startSession(int $propertyId, string $ref): ?KioskSession
    {
        $reservation = Reservation::where('property_id', $propertyId)
            ->where('ref', $ref)
            ->where('status', 'confirmed')
            ->with('primaryGuest')
            ->first();

        if (!$reservation) return null;

        $existing = KioskSession::where('reservation_id', $reservation->id)
            ->where('status', '!=', 'cancelled')
            ->first();
        if ($existing) return $existing;

        return KioskSession::create([
            'property_id' => $propertyId,
            'reservation_id' => $reservation->id,
            'guest_id' => $reservation->primary_guest_id,
            'session_code' => strtoupper(Str::random(6)),
            'status' => 'started',
            'expires_at' => now()->addHours(2),
        ]);
    }

    public function verifyIdentity(int $sessionId, string $idType, string $idNumber, ?string $ocrData = null): KioskSession
    {
        $session = KioskSession::findOrFail($sessionId);
        $session->update([
            'id_type' => $idType,
            'id_number' => $idNumber,
            'id_ocr_data' => $ocrData,
            'status' => 'verified',
        ]);

        if ($session->guest) {
            $session->guest->update([
                'id_type' => $idType,
                'id_number' => $idNumber,
            ]);
        }

        return $session;
    }

    public function signAndComplete(int $sessionId, string $signatureData, ?string $photoPath = null): KioskSession
    {
        $session = KioskSession::with('reservation.primaryGuest')->findOrFail($sessionId);
        $session->update([
            'signature_data' => $signatureData,
            'photo_path' => $photoPath,
            'terms_accepted' => json_encode(['accepted_at' => now()->toIso8601String()]),
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Auto-assign room if available
        $room = $this->autoAssignRoom($session->property_id, $session->reservation);
        if ($room) {
            $session->update(['room_assigned' => $room->room_number]);
            $session->reservation->reservationRooms()->first()?->update(['room_id' => $room->id]);
        }

        $session->reservation->update([
            'pre_checkin_complete' => true,
            'checked_in_at' => now(),
            'status' => 'checked_in',
        ]);

        return $session;
    }

    protected function autoAssignRoom(int $propertyId, Reservation $reservation): ?Room
    {
        $rooms = $reservation->reservationRooms()->with('roomType')->get();
        if ($rooms->isEmpty()) return null;

        $roomTypeId = $rooms->first()->room_type_id;
        return Room::where('property_id', $propertyId)
            ->where('room_type_id', $roomTypeId)
            ->where('is_active', true)
            ->whereDoesntHave('reservationRooms', function ($q) {
                $q->whereIn('status', ['confirmed', 'checked_in']);
            })
            ->orderBy('floor')
            ->first();
    }

    public function getActiveSessions(int $propertyId): array
    {
        return KioskSession::where('property_id', $propertyId)
            ->whereIn('status', ['started', 'verified', 'signed'])
            ->where('expires_at', '>', now())
            ->with('reservation.primaryGuest')
            ->orderBy('created_at')
            ->get()
            ->toArray();
    }
}
