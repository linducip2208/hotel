<?php

namespace App\Services\Fo;

use App\Models\FleetDriver;
use App\Models\FleetTrip;
use App\Models\FleetVehicle;
use App\Models\Folio;
use App\Models\FolioCharge;
use App\Models\Guest;
use App\Models\Property;
use App\Models\Reservation;
use Carbon\Carbon;

class FleetService
{
    public function getDashboard(Property $property): array
    {
        $today = Carbon::today();

        $todaysTrips = FleetTrip::where('property_id', $property->id)
            ->whereDate('scheduled_at', $today)
            ->with(['vehicle', 'driver.employee', 'guest', 'reservation.primaryGuest'])
            ->orderBy('scheduled_at')
            ->get();

        $vehicles = FleetVehicle::where('property_id', $property->id)
            ->with(['trips' => fn($q) => $q->whereDate('scheduled_at', $today)])
            ->get();

        $activeVehicles = FleetVehicle::where('property_id', $property->id)
            ->where('is_active', true)->count();
        $vehiclesOnTrip = FleetTrip::where('property_id', $property->id)
            ->where('status', 'in_progress')->count();
        $vehiclesAvailable = FleetVehicle::where('property_id', $property->id)
            ->where('is_active', true)->count() - $vehiclesOnTrip;

        $drivers = FleetDriver::where('property_id', $property->id)
            ->where('is_active', true)
            ->with('employee')
            ->get();

        $availableDrivers = $drivers->filter(function ($d) use ($today) {
            $activeTrip = FleetTrip::where('driver_id', $d->id)
                ->whereDate('scheduled_at', $today)
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->exists();
            return !$activeTrip;
        });

        return [
            'todaysTrips' => $todaysTrips,
            'vehicles' => $vehicles,
            'stats' => [
                'total_vehicles' => FleetVehicle::where('property_id', $property->id)->count(),
                'active_vehicles' => $activeVehicles,
                'on_trip' => $vehiclesOnTrip,
                'available_vehicles' => max(0, $vehiclesAvailable),
                'total_drivers' => $drivers->count(),
                'available_drivers' => $availableDrivers->count(),
            ],
            'drivers' => $drivers,
            'availableDrivers' => $availableDrivers,
        ];
    }

    public function scheduleTrip(Property $property, array $data): FleetTrip
    {
        $trip = FleetTrip::create([
            'property_id' => $property->id,
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'driver_id' => $data['driver_id'] ?? null,
            'reservation_id' => $data['reservation_id'] ?? null,
            'guest_id' => $data['guest_id'] ?? null,
            'trip_type' => $data['trip_type'],
            'pickup_location' => $data['pickup_location'] ?? null,
            'dropoff_location' => $data['dropoff_location'] ?? null,
            'scheduled_at' => $data['scheduled_at'],
            'status' => 'scheduled',
            'passenger_count' => $data['passenger_count'] ?? 1,
            'charge_amount' => $data['charge_amount'] ?? 0,
            'notes' => $data['notes'] ?? null,
        ]);

        return $trip;
    }

    public function startTrip(int $tripId): FleetTrip
    {
        $trip = FleetTrip::findOrFail($tripId);
        $trip->update([
            'status' => 'in_progress',
            'actual_departure' => now(),
        ]);
        return $trip;
    }

    public function completeTrip(int $tripId): FleetTrip
    {
        $trip = FleetTrip::findOrFail($tripId);
        $trip->update([
            'status' => 'completed',
            'actual_arrival' => now(),
        ]);
        return $trip;
    }

    public function cancelTrip(int $tripId): FleetTrip
    {
        $trip = FleetTrip::findOrFail($tripId);
        $trip->update(['status' => 'cancelled']);
        return $trip;
    }

    public function chargeToFolio(FleetTrip $trip, int $folioId): void
    {
        if ($trip->charge_amount <= 0) return;

        $folio = Folio::where('property_id', $trip->property_id)->findOrFail($folioId);

        $charge = FolioCharge::create([
            'folio_id' => $folio->id,
            'property_id' => $trip->property_id,
            'description' => 'Transport: ' . $trip->trip_type . ' - ' . ($trip->pickup_location ?? '') . ' ke ' . ($trip->dropoff_location ?? ''),
            'amount' => $trip->charge_amount,
            'tax_amount' => 0,
            'is_void' => false,
            'posted_at' => now(),
        ]);

        $trip->update(['folio_charge_id' => $charge->id]);
        $folio->recalculate();
    }
}
