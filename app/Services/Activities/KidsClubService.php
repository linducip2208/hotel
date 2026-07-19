<?php

namespace App\Services\Activities;

use App\Models\KidsActivity;
use App\Models\KidsBooking;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KidsClubService
{
    public function getActivities(int $propertyId): Collection
    {
        return KidsActivity::where('property_id', $propertyId)
            ->where('is_active', true)
            ->withCount(['bookings' => fn ($q) =>
                $q->where('booking_date', today()->toDateString())
                  ->where('status', '!=', 'cancelled')
            ])
            ->orderBy('name')
            ->get();
    }

    public function checkAvailability(KidsActivity $activity, string $date): array
    {
        $booked = $activity->bookings()
            ->where('booking_date', $date)
            ->where('status', '!=', 'cancelled')
            ->count();

        return [
            'available' => $booked < $activity->capacity,
            'booked' => $booked,
            'remaining' => max(0, $activity->capacity - $booked),
        ];
    }

    public function book(array $data): KidsBooking
    {
        return DB::transaction(function () use ($data) {
            $activity = KidsActivity::findOrFail($data['kids_activity_id']);
            $avail = $this->checkAvailability($activity, $data['booking_date']);

            if (!$avail['available']) {
                throw new \RuntimeException('Aktivitas sudah penuh untuk tanggal tersebut.');
            }

            return KidsBooking::create([
                'property_id' => $activity->property_id,
                'reservation_id' => $data['reservation_id'] ?? null,
                'guest_id' => $data['guest_id'] ?? null,
                'kids_activity_id' => $data['kids_activity_id'],
                'child_name' => $data['child_name'],
                'child_age' => $data['child_age'],
                'booking_date' => $data['booking_date'],
                'start_time' => $data['start_time'],
                'status' => 'booked',
                'special_requests' => $data['special_requests'] ?? null,
            ]);
        });
    }

    public function cancel(int $bookingId): KidsBooking
    {
        $booking = KidsBooking::findOrFail($bookingId);
        $booking->update(['status' => 'cancelled']);
        return $booking;
    }

    public function getSchedule(int $propertyId, ?string $date = null): array
    {
        $date = $date ?? today()->toDateString();
        $activities = $this->getActivities($propertyId);

        return $activities->map(fn ($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'age_range' => $a->age_range,
            'price' => $a->price,
            'duration' => $a->duration_minutes,
            'capacity' => $a->capacity,
            'booked' => $a->bookings_count,
            'available' => max(0, $a->capacity - $a->bookings_count),
        ])->toArray();
    }
}
