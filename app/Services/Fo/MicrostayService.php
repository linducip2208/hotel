<?php

namespace App\Services;

use App\Models\MicrostayRate;
use App\Models\Reservation;
use Carbon\Carbon;

class MicrostayService
{
    public function getRates(int $propertyId, int $roomTypeId): array
    {
        return MicrostayRate::where('property_id', $propertyId)
            ->where('room_type_id', $roomTypeId)
            ->where('is_active', true)
            ->orderBy('hours')
            ->get()
            ->toArray();
    }

    public function calculatePrice(int $propertyId, int $roomTypeId, int $hours): ?float
    {
        $rate = MicrostayRate::where('property_id', $propertyId)
            ->where('room_type_id', $roomTypeId)
            ->where('hours', $hours)
            ->where('is_active', true)
            ->first();

        if (!$rate) {
            // Pro-rata from base 24hr rate if no microstay rate defined
            $roomType = \App\Models\RoomType::find($roomTypeId);
            if (!$roomType) return null;
            return round(($roomType->base_rate / 24) * $hours, 2);
        }

        return (float) $rate->price;
    }

    public function createMicrostayReservation(array $data): Reservation
    {
        $checkIn = Carbon::parse($data['check_in']);
        $hours = (int) $data['microstay_hours'];
        $checkOutHour = $checkIn->copy()->addHours($hours);

        $reservation = Reservation::create([
            'property_id' => $data['property_id'],
            'ref' => 'MS-' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'check_in' => $checkIn,
            'check_out' => $checkOutHour,
            'nights' => 0,
            'source' => 'walkin',
            'status' => 'confirmed',
            'is_microstay' => true,
            'microstay_hours' => $hours,
            'check_out_hour' => $checkOutHour,
            'total_room' => $data['total_room'] ?? 0,
            'grand_total' => $data['total_room'] ?? 0,
            'balance' => $data['total_room'] ?? 0,
            'adults' => $data['adults'] ?? 1,
        ]);

        return $reservation;
    }

    public function expireOverdueMicrostays(int $propertyId): int
    {
        $count = 0;
        $reservations = Reservation::where('property_id', $propertyId)
            ->where('is_microstay', true)
            ->where('status', 'checked_in')
            ->get();

        foreach ($reservations as $r) {
            if ($r->check_out_hour && Carbon::parse($r->check_out_hour)->isPast()) {
                $r->update(['status' => 'checked_out', 'checked_out_at' => now()]);
                $count++;
            }
        }

        return $count;
    }
}
