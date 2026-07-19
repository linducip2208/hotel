<?php

namespace App\Services\Fo;

use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RoomAssignmentService
{
    /**
     * Auto-assign the best room for a reservation's room type and date range.
     * Returns the assigned Room or null if no room is available.
     */
    public function assign(Reservation $reservation, ?array $preferences = []): ?Room
    {
        $rules = config('hotel.room_assignment_rules', []);
        $autoAssign = config('hotel.auto_assign_room', true);

        if (! $autoAssign) {
            return null;
        }

        $propertyId = $reservation->property_id;
        $checkIn = Carbon::parse($reservation->check_in);
        $checkOut = Carbon::parse($reservation->check_out);

        foreach ($reservation->rooms as $rr) {
            if ($rr->room_id) {
                continue; // already assigned
            }

            $candidates = $this->getAvailableRooms($propertyId, $rr->room_type_id, $checkIn, $checkOut);

            if ($candidates->isEmpty()) {
                continue;
            }

            $scores = [];
            foreach ($candidates as $room) {
                $score = 0;

                // 1. guest_preference — prefer rooms matching guest floor/view preference
                if (! empty($rules['guest_preference']) && $preferences) {
                    if (! empty($preferences['preferred_floor']) && $room->floor === (int) $preferences['preferred_floor']) {
                        $score += 100;
                    }
                    if (! empty($preferences['preferred_view']) && $room->view === $preferences['preferred_view']) {
                        $score += 80;
                    }
                }

                // 2. floor_balance — prefer floor with fewest occupied rooms
                if (! empty($rules['floor_balance'])) {
                    $occupiedOnFloor = ReservationRoom::whereHas('reservation', function ($q) use ($propertyId) {
                        $q->where('property_id', $propertyId)->whereIn('status', ['confirmed', 'checked_in']);
                    })->whereHas('room', function ($q) use ($room) {
                        $q->where('floor', $room->floor);
                    })->whereNotNull('room_id')->count();

                    // Lower occupancy = higher score (inverted)
                    $score += max(0, 50 - ($occupiedOnFloor * 5));
                }

                // 3. room_proximity — if group booking, cluster rooms on same floor
                if (! empty($rules['room_proximity']) && $reservation->group_block_id) {
                    $groupRoomsOnFloor = ReservationRoom::whereHas('reservation', function ($q) use ($reservation) {
                        $q->where('group_block_id', $reservation->group_block_id);
                    })->whereHas('room', function ($q) use ($room) {
                        $q->where('floor', $room->floor);
                    })->whereNotNull('room_id')->count();

                    $score += $groupRoomsOnFloor * 30;
                }

                // 4. previous_room — if repeat guest, try same room as last stay
                if (! empty($rules['previous_room'])) {
                    $lastStayRoom = $this->getLastStayRoomId($reservation->primary_guest_id, $propertyId);
                    if ($lastStayRoom && $room->id === $lastStayRoom) {
                        $score += 120;
                    }
                }

                // 5. clean_first — prefer rooms already clean
                if (! empty($rules['clean_first'])) {
                    if ($room->hk_status === 'clean') {
                        $score += 60;
                    } elseif ($room->hk_status === 'inspected') {
                        $score += 70;
                    } elseif ($room->hk_status === 'dirty') {
                        $score -= 30;
                    }
                }

                // 6. first_available — fallback: lowest room number
                if (! empty($rules['first_available'])) {
                    $score += (9999 - (int) $room->number) * 0.01;
                }

                $scores[$room->id] = $score;
            }

            // Pick highest-scoring room
            arsort($scores);
            $bestRoomId = array_key_first($scores);

            $bestRoom = $candidates->firstWhere('id', $bestRoomId);
            if ($bestRoom) {
                $rr->update(['room_id' => $bestRoom->id]);
                $bestRoom->update(['fo_status' => 'assigned']);

                return $bestRoom;
            }
        }

        return null;
    }

    /**
     * Assign rooms for multiple reservations (e.g. group booking).
     */
    public function bulkAssign(array $reservationIds): array
    {
        $results = [];

        foreach ($reservationIds as $id) {
            $reservation = Reservation::with(['rooms', 'primaryGuest'])->find($id);
            if ($reservation) {
                $results[$id] = $this->assign($reservation);
            }
        }

        return $results;
    }

    /**
     * Reassign a reservation to a new room.
     */
    public function reassign(Reservation $reservation, int $newRoomId): Room
    {
        $newRoom = Room::where('property_id', $reservation->property_id)
            ->where('is_active', true)
            ->findOrFail($newRoomId);

        DB::transaction(function () use ($reservation, $newRoomId) {
            // Release current room assignment
            foreach ($reservation->rooms as $rr) {
                if ($rr->room_id) {
                    $oldRoom = Room::find($rr->room_id);
                    if ($oldRoom) {
                        $oldRoom->update(['fo_status' => 'vacant']);
                    }
                }
                $rr->update(['room_id' => $newRoomId]);
            }

            $newRoom = Room::find($newRoomId);
            if ($newRoom) {
                $newRoom->update(['fo_status' => 'assigned']);
            }
        });

        return $newRoom->fresh();
    }

    /**
     * Get available rooms for a reservation with calculated scores.
     */
    public function getAvailableOptions(Reservation $reservation): Collection
    {
        $propertyId = $reservation->property_id;
        $checkIn = Carbon::parse($reservation->check_in);
        $checkOut = Carbon::parse($reservation->check_out);
        $preferences = $reservation->primaryGuest?->preferences ?? [];

        $allOptions = collect();
        foreach ($reservation->rooms as $rr) {
            if ($rr->room_id) {
                continue;
            }

            $candidates = $this->getAvailableRooms($propertyId, $rr->room_type_id, $checkIn, $checkOut);

            foreach ($candidates as $room) {
                $score = 0;

                if (! empty($preferences['preferred_floor']) && $room->floor === (int) $preferences['preferred_floor']) {
                    $score += 100;
                }
                if (! empty($preferences['preferred_view']) && $room->view === $preferences['preferred_view']) {
                    $score += 80;
                }

                if ($room->hk_status === 'clean') {
                    $score += 60;
                } elseif ($room->hk_status === 'inspected') {
                    $score += 70;
                } elseif ($room->hk_status === 'dirty') {
                    $score -= 30;
                }

                $score += (9999 - (int) $room->number) * 0.01;

                $room->score = round($score, 2);
                $allOptions->push($room);
            }
        }

        return $allOptions->sortByDesc('score')->values();
    }

    /**
     * Get rooms that are available (not assigned to another reservation) for a given date range and room type.
     */
    protected function getAvailableRooms(int $propertyId, int $roomTypeId, Carbon $checkIn, Carbon $checkOut): Collection
    {
        // Get all active rooms of the given type
        $rooms = Room::where('property_id', $propertyId)
            ->where('room_type_id', $roomTypeId)
            ->where('is_active', true)
            ->where('fo_status', '!=', 'out_of_order')
            ->orderBy('number')
            ->get();

        if ($rooms->isEmpty()) {
            return new Collection();
        }

        // Find rooms already assigned during the date range
        $occupiedRoomIds = ReservationRoom::whereHas('reservation', function ($q) use ($propertyId) {
            $q->where('property_id', $propertyId)
                ->whereIn('status', ['confirmed', 'checked_in']);
        })
            ->where('room_type_id', $roomTypeId)
            ->whereNotNull('room_id')
            ->where(function ($q) use ($checkIn, $checkOut) {
                // Overlapping date ranges
                $q->whereBetween('check_in', [$checkIn->toDateString(), $checkOut->copy()->subDay()->toDateString()]);
                $q->orWhereBetween('check_out', [$checkIn->copy()->addDay()->toDateString(), $checkOut->toDateString()]);
                $q->orWhere(function ($inner) use ($checkIn, $checkOut) {
                    $inner->where('check_in', '<=', $checkIn->toDateString())
                        ->where('check_out', '>=', $checkOut->toDateString());
                });
            })
            ->pluck('room_id')
            ->unique();

        return $rooms->reject(fn ($room) => $occupiedRoomIds->contains($room->id))->values();
    }

    /**
     * Get room ID from guest's last stay.
     */
    protected function getLastStayRoomId(?int $guestId, int $propertyId): ?int
    {
        if (! $guestId) {
            return null;
        }

        return ReservationRoom::whereHas('reservation', function ($q) use ($guestId, $propertyId) {
            $q->where('property_id', $propertyId)
                ->where('primary_guest_id', $guestId)
                ->where('status', 'checked_out');
        })
            ->whereNotNull('room_id')
            ->orderByDesc('check_out')
            ->value('room_id');
    }
}
