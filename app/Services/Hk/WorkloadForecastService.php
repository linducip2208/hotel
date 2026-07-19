<?php

namespace App\Services\Hk;

use App\Models\HkTask;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WorkloadForecastService
{
    public function forecastForDate(Carbon $date, int $propertyId): array
    {
        $dateStr = $date->toDateString();

        // Checkout rooms — guests leaving today, need full clean
        $checkoutResIds = Reservation::where('property_id', $propertyId)
            ->where('check_out', $dateStr)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->pluck('id');

        $checkoutRoomIds = ReservationRoom::whereIn('reservation_id', $checkoutResIds)->pluck('room_id');

        // Stayover rooms — guests staying, light clean
        $stayoverResIds = Reservation::where('property_id', $propertyId)
            ->where('status', 'checked_in')
            ->where('check_in', '<', $dateStr)
            ->where('check_out', '>', $dateStr)
            ->pluck('id');

        $stayoverRoomIds = ReservationRoom::whereIn('reservation_id', $stayoverResIds)->pluck('room_id');

        // Vacant dirty rooms
        $vacantDirtyRoomIds = Room::where('property_id', $propertyId)
            ->where('hk_status', 'dirty')
            ->where('fo_status', 'vacant')
            ->pluck('id');

        // Exclude already assigned tasks today
        $alreadyAssigned = HkTask::where('property_id', $propertyId)
            ->whereDate('scheduled_date', $dateStr)
            ->whereIn('status', ['pending', 'in_progress'])
            ->pluck('room_id');

        $cleanTypes = [
            'checkout' => ['clean_type' => 'full', 'minutes' => 30, 'priority' => 'high', 'color' => 'red'],
            'stayover' => ['clean_type' => 'light', 'minutes' => 15, 'priority' => 'normal', 'color' => 'blue'],
            'vacant_dirty' => ['clean_type' => 'vacant', 'minutes' => 10, 'priority' => 'low', 'color' => 'gray'],
        ];

        $rooms = Room::where('property_id', $propertyId)
            ->whereIn('id', $checkoutRoomIds->merge($stayoverRoomIds)->merge($vacantDirtyRoomIds)->unique())
            ->whereNotIn('id', $alreadyAssigned)
            ->with('roomType')
            ->get();

        $workload = [];
        $totalMinutes = 0;

        foreach ($rooms as $room) {
            $types = $this->determineTypes($room->id, $checkoutRoomIds, $stayoverRoomIds, $vacantDirtyRoomIds);

            $minuteWeight = 0;
            $parts = [];
            foreach ($types as $t) {
                $cfg = $cleanTypes[$t];
                $minuteWeight += $cfg['minutes'];
                $parts[] = $t;
            }

            $totalMinutes += $minuteWeight;

            $primaryType = $types[0];
            $cfg = $cleanTypes[$primaryType];

            $workload[] = [
                'room_id' => $room->id,
                'room_number' => $room->number,
                'type' => $room->roomType?->name ?? 'Standard',
                'fo_status' => $room->fo_status,
                'clean_type' => $cfg['clean_type'],
                'estimated_minutes' => $minuteWeight,
                'priority' => $cfg['priority'],
                'color' => $cfg['color'],
                'floor' => $room->floor,
            ];
        }

        $attendantsNeeded = $totalMinutes > 0 ? (int) ceil($totalMinutes / 480) : 1;

        return [
            'date' => $date,
            'total_rooms' => count($workload),
            'total_minutes' => $totalMinutes,
            'attendants_needed' => $attendantsNeeded,
            'rooms' => $workload,
        ];
    }

    public function generateAssignment(int $propertyId, Carbon $date, int $attendantCount): array
    {
        $forecast = $this->forecastForDate($date, $propertyId);
        $rooms = collect($forecast['rooms']);

        // Sort by priority then floor
        $rooms = $rooms->sortByDesc('priority')->values();

        // Distribute fairly by estimated minutes
        $assignments = array_fill(0, max($attendantCount, 1), []);
        $loads = array_fill(0, max($attendantCount, 1), 0);

        foreach ($rooms as $room) {
            $minLoadIndex = array_search(min($loads), $loads);
            $assignments[$minLoadIndex][] = $room;
            $loads[$minLoadIndex] += $room['estimated_minutes'];
        }

        $result = [];
        for ($i = 0; $i < count($assignments); $i++) {
            $totalMin = array_sum(array_column($assignments[$i], 'estimated_minutes'));
            $result[] = [
                'attendant' => $i + 1,
                'rooms' => $assignments[$i],
                'room_count' => count($assignments[$i]),
                'total_minutes' => $totalMin,
            ];
        }

        return $result;
    }

    protected function determineTypes(int $roomId, Collection $checkoutRoomIds, Collection $stayoverRoomIds, Collection $vacantDirtyRoomIds): array
    {
        $types = [];
        if ($checkoutRoomIds->contains($roomId)) $types[] = 'checkout';
        elseif ($stayoverRoomIds->contains($roomId)) $types[] = 'stayover';
        elseif ($vacantDirtyRoomIds->contains($roomId)) $types[] = 'vacant_dirty';
        else $types[] = 'stayover'; // fallback

        return $types;
    }
}
