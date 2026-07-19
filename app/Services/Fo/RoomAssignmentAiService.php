<?php

namespace App\Services\Fo;

use App\Models\Property;
use App\Models\Reservation;
use App\Models\Room;

class RoomAssignmentAiService
{
    public function assignOptimal(Reservation $reservation): ?Room
    {
        $available = Room::where('property_id', $reservation->property_id)
            ->where('room_type_id', $reservation->room_type_id)
            ->where('fo_status', 'vacant')
            ->where('hk_status', 'clean')
            ->where('is_active', true)
            ->get();

        if ($available->isEmpty()) {
            return null;
        }

        $profile = $reservation->primaryGuest?->profile;
        $scores = [];

        foreach ($available as $room) {
            $score = 0;

            if ($profile) {
                if ($room->floor == $profile->preferred_floor) {
                    $score += 20;
                }
                if ($room->roomType?->bed_config == $profile->preferred_bed_type) {
                    $score += 20;
                }
            }

            if ($room->distance_to_elevator <= 20) {
                $score += 15;
            } elseif ($room->distance_to_elevator <= 50) {
                $score += 10;
            }

            switch ($room->view) {
                case 'ocean': $score += 15; break;
                case 'pool':  $score += 10; break;
                case 'garden': $score += 8; break;
                default: $score += 5;
            }

            if ($room->near_elevator && ($profile?->isHighValue() ?? false)) {
                $score -= 10;
            }

            $lastCheckout = Reservation::where('room_id', $room->id)
                ->where('status', 'checked_out')
                ->latest('check_out')
                ->first();
            if ($lastCheckout && $lastCheckout->check_out && $lastCheckout->check_out->diffInDays(now()) > 3) {
                $score += 10;
            }

            if ($reservation->group_block_id) {
                $groupRooms = \App\Models\GroupBlockRoom::where('group_block_id', $reservation->group_block_id)
                    ->where('room_type_id', $reservation->room_type_id)
                    ->pluck('room_id');
                $nearGroupRoom = Room::whereIn('id', $groupRooms)
                    ->where('floor', $room->floor)
                    ->exists();
                if ($nearGroupRoom) {
                    $score += 20;
                }
            }

            $scores[$room->id] = $score;
        }

        arsort($scores);
        $bestRoomId = array_key_first($scores);

        return $available->find($bestRoomId);
    }

    public function batchAssign(Property $property, string $date): array
    {
        $reservations = Reservation::where('property_id', $property->id)
            ->whereDate('check_in', $date)
            ->whereIn('status', ['confirmed', 'tentative'])
            ->whereNull('room_id')
            ->with('primaryGuest.profile')
            ->orderByDesc('created_at')
            ->get();

        $assigned = [];
        $scores = [];

        foreach ($reservations as $res) {
            $room = $this->assignOptimal($res);
            if ($room) {
                $res->update(['room_id' => $room->id]);
                $room->update(['fo_status' => 'occupied']);
                $assigned[$res->id] = $scores[$room->id] ?? 0;
            }
        }

        return $assigned;
    }

    public function getAssignmentScore(Room $room, Reservation $reservation): int
    {
        $profile = $reservation->primaryGuest?->profile;
        $score = 0;

        if ($profile) {
            if ($room->floor == $profile->preferred_floor) $score += 20;
            if ($room->roomType?->bed_config == $profile->preferred_bed_type) $score += 20;
        }

        if ($room->distance_to_elevator <= 20) $score += 15;
        elseif ($room->distance_to_elevator <= 50) $score += 10;

        switch ($room->view) {
            case 'ocean': $score += 15; break;
            case 'pool':  $score += 10; break;
            case 'garden': $score += 8; break;
            default: $score += 5;
        }

        if ($room->near_elevator && ($profile?->isHighValue() ?? false)) $score -= 10;

        $lastCheckout = Reservation::where('room_id', $room->id)
            ->where('status', 'checked_out')
            ->latest('check_out')
            ->first();
        if ($lastCheckout && $lastCheckout->check_out && $lastCheckout->check_out->diffInDays(now()) > 3) $score += 10;

        return max(0, $score);
    }
}
