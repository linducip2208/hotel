<?php

namespace App\Services\Fo;

use App\Models\Property;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Preference-scored room assignment.
 *
 * Works on the REAL data model: assignment targets are `reservation_rooms`
 * rows (a reservation can have many rooms) and room attributes actually
 * present in the schema (floor, view, roomType.bed_config, hk_status).
 */
class RoomAssignmentAiService
{
    public function __construct(protected RoomAssignmentService $assignmentService) {}

    /**
     * Pick the best free room for one reservation-room row.
     */
    public function assignOptimal(ReservationRoom $rr): ?Room
    {
        $reservation = $rr->reservation;
        if (! $reservation) {
            return null;
        }

        $candidates = $this->assignmentService->getAvailableRooms(
            $reservation->property_id,
            $rr->room_type_id,
            Carbon::parse($rr->check_in),
            Carbon::parse($rr->check_out),
        );

        if ($candidates->isEmpty()) {
            return null;
        }

        $scores = [];
        foreach ($candidates as $room) {
            $scores[$room->id] = $this->getAssignmentScore($room, $rr);
        }

        arsort($scores);

        return $candidates->firstWhere('id', array_key_first($scores));
    }

    /**
     * Batch auto-assign every unassigned reservation room arriving on a date.
     *
     * @return array<int, int> reservation_room_id => room_id
     */
    public function batchAssign(Property $property, string $date): array
    {
        $unassigned = ReservationRoom::query()
            ->whereHas('reservation', fn ($q) => $q
                ->where('property_id', $property->id)
                ->whereIn('status', ['confirmed', 'tentative'])
                ->whereDate('check_in', '<=', $date))
            ->whereNull('room_id')
            ->with('reservation.primaryGuest.profile')
            ->orderBy('check_in')
            ->get();

        $assigned = [];

        DB::transaction(function () use ($unassigned, &$assigned) {
            foreach ($unassigned as $rr) {
                $room = $this->assignOptimal($rr);
                if (! $room) {
                    continue;
                }

                $rr->update(['room_id' => $room->id]);

                // A future arrival holds the room, it is not occupied yet.
                $status = $rr->reservation->status === 'checked_in' ? 'occupied' : 'reserved';
                $room->update(['fo_status' => $status]);

                $assigned[$rr->id] = $room->id;
            }
        });

        return $assigned;
    }

    /**
     * Preference score for a room against a reservation-room row.
     * Only uses columns that actually exist in the schema.
     */
    public function getAssignmentScore(Room $room, ReservationRoom $rr): int
    {
        $reservation = $rr->reservation;
        $profile = $reservation?->primaryGuest?->profile;
        $score = 0;

        if ($profile) {
            if ($profile->preferred_floor !== null && $room->floor == $profile->preferred_floor) {
                $score += 20;
            }
            if ($profile->preferred_bed_type && $room->roomType?->bed_config === $profile->preferred_bed_type) {
                $score += 20;
            }
        }

        // View preference via guest preferences JSON if set.
        $preferredView = $reservation?->primaryGuest?->preferences['preferred_view'] ?? null;
        if ($preferredView && $room->view === $preferredView) {
            $score += 15;
        }

        // Cleanliness readiness.
        $score += match ($room->hk_status) {
            'inspected' => 10,
            'clean' => 8,
            'dirty' => -20,
            default => 0,
        };

        // Slightly prefer lower room numbers for deterministic ordering.
        $score += max(0, 50 - (int) $room->number) * 0.1;

        return (int) round(max(0, $score));
    }
}
