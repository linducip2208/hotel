<?php

namespace App\Services\Pos;

use App\Models\Property;
use App\Models\RestaurantTable;
use App\Models\TableReservation;
use Carbon\Carbon;

class TableReservationService
{
    public function findAvailable(Property $property, string $date, string $startTime, int $partySize, ?int $outletId = null): array
    {
        $query = RestaurantTable::where('property_id', $property->id)
            ->where('is_active', true)
            ->where('capacity', '>=', $partySize);

        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        $tables = $query->get();

        $occupiedTableIds = TableReservation::where('property_id', $property->id)
            ->where('reservation_date', $date)
            ->whereIn('status', ['confirmed', 'seated'])
            ->where('start_time', '<', Carbon::parse("$date $startTime")->addMinutes(90))
            ->where('end_time', '>', Carbon::parse("$date $startTime"))
            ->pluck('restaurant_table_id')
            ->toArray();

        return $tables->reject(fn($t) => in_array($t->id, $occupiedTableIds))->values()->toArray();
    }

    public function reserve(Property $property, array $data): TableReservation
    {
        $endTime = Carbon::parse("{$data['reservation_date']} {$data['start_time']}")
            ->addMinutes($data['duration_minutes'] ?? 90)
            ->format('H:i:s');

        return TableReservation::create([
            'property_id' => $property->id,
            'restaurant_table_id' => $data['restaurant_table_id'],
            'reservation_id' => $data['reservation_id'] ?? null,
            'guest_id' => $data['guest_id'] ?? null,
            'guest_name' => $data['guest_name'],
            'guest_phone' => $data['guest_phone'] ?? null,
            'party_size' => $data['party_size'],
            'reservation_date' => $data['reservation_date'],
            'start_time' => $data['start_time'],
            'end_time' => $endTime,
            'duration_minutes' => $data['duration_minutes'] ?? 90,
            'status' => 'confirmed',
            'special_requests' => $data['special_requests'] ?? null,
            'notes' => $data['notes'] ?? null,
            'booked_by_user_id' => $data['booked_by_user_id'] ?? null,
        ]);
    }

    public function checkIn(int $id): TableReservation
    {
        $r = TableReservation::findOrFail($id);
        $r->update(['status' => 'seated']);
        return $r;
    }

    public function complete(int $id): TableReservation
    {
        $r = TableReservation::findOrFail($id);
        $r->update(['status' => 'completed']);
        return $r;
    }

    public function noShow(int $id): TableReservation
    {
        $r = TableReservation::findOrFail($id);
        $r->update(['status' => 'no_show']);
        return $r;
    }

    public function cancel(int $id): TableReservation
    {
        $r = TableReservation::findOrFail($id);
        $r->update(['status' => 'cancelled']);
        return $r;
    }

    public function getFloorPlan(Property $property, ?string $date = null, ?int $outletId = null): array
    {
        $date = $date ?? Carbon::today()->toDateString();
        $query = RestaurantTable::where('property_id', $property->id)->where('is_active', true);
        if ($outletId) $query->where('outlet_id', $outletId);

        $tables = $query->orderBy('section')->orderBy('table_number')->get();

        $activeReservations = TableReservation::where('property_id', $property->id)
            ->where('reservation_date', $date)
            ->whereIn('status', ['confirmed', 'seated'])
            ->with('guest')
            ->get()
            ->groupBy('restaurant_table_id');

        $result = [];
        foreach ($tables as $table) {
            $active = $activeReservations->get($table->id);
            $status = 'available';
            $currentReservation = null;
            if ($active && $active->isNotEmpty()) {
                $seated = $active->firstWhere('status', 'seated');
                if ($seated) {
                    $status = 'occupied';
                    $currentReservation = $seated;
                } else {
                    $status = 'reserved';
                    $currentReservation = $active->first();
                }
            }
            $result[] = [
                'table' => $table,
                'status' => $status,
                'current_reservation' => $currentReservation,
            ];
        }

        return $result;
    }
}
