<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\FleetDriver;
use App\Models\FleetTrip;
use App\Models\FleetVehicle;
use App\Models\Folio;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\ShuttleSchedule;
use App\Services\Fo\FleetService;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    public function __construct(protected FleetService $svc) {}

    public function dashboard()
    {
        $property = app('current_property');
        $data = $this->svc->getDashboard($property);
        return view('panel.fo.fleet-dashboard', $data);
    }

    public function vehicles()
    {
        $propertyId = app('current_property')->id;
        $vehicles = FleetVehicle::where('property_id', $propertyId)->orderBy('name')->get();
        return view('panel.fo.fleet-vehicles', compact('vehicles'));
    }

    public function storeVehicle(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'plate_number' => 'required|string|max:20',
            'type' => 'required|in:car,van,bus,motorcycle,golf_cart',
            'capacity' => 'required|integer|min:1',
            'fuel_type' => 'nullable|string|max:50',
            'last_maintenance_at' => 'nullable|date',
            'next_maintenance_due' => 'nullable|date',
        ]);

        FleetVehicle::create($data + [
            'property_id' => app('current_property')->id,
            'is_active' => true,
        ]);

        return back()->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function updateVehicle(Request $request, $id)
    {
        $vehicle = FleetVehicle::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'plate_number' => 'required|string|max:20',
            'type' => 'required|in:car,van,bus,motorcycle,golf_cart',
            'capacity' => 'required|integer|min:1',
            'fuel_type' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'last_maintenance_at' => 'nullable|date',
            'next_maintenance_due' => 'nullable|date',
        ]);

        $vehicle->update($data);
        return back()->with('success', 'Kendaraan berhasil diperbarui.');
    }

    public function drivers()
    {
        $propertyId = app('current_property')->id;
        $drivers = FleetDriver::where('property_id', $propertyId)->with('employee')->orderBy('id')->get();
        $employees = \App\Models\Employee::where('property_id', $propertyId)->orderBy('full_name')->get();
        return view('panel.fo.fleet-trips', compact('drivers', 'employees')); // drivers shown in same page
    }

    public function storeDriver(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'license_number' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
        ]);

        FleetDriver::create($data + [
            'property_id' => app('current_property')->id,
            'is_active' => true,
        ]);

        return back()->with('success', 'Driver berhasil ditambahkan.');
    }

    public function updateDriver(Request $request, $id)
    {
        $driver = FleetDriver::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'license_number' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $driver->update($data);
        return back()->with('success', 'Driver berhasil diperbarui.');
    }

    public function trips(Request $request)
    {
        $propertyId = app('current_property')->id;
        $trips = FleetTrip::where('property_id', $propertyId)
            ->with(['vehicle', 'driver.employee', 'guest', 'reservation.primaryGuest'])
            ->orderByDesc('scheduled_at')
            ->paginate(25);

        $vehicles = FleetVehicle::where('property_id', $propertyId)->where('is_active', true)->orderBy('name')->get();
        $drivers = FleetDriver::where('property_id', $propertyId)->where('is_active', true)->with('employee')->get();
        $guests = Guest::where('property_id', $propertyId)->orderBy('first_name')->limit(100)->get();
        $reservations = Reservation::where('property_id', $propertyId)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->with('primaryGuest')
            ->orderBy('check_in')
            ->limit(50)->get();

        return view('panel.fo.fleet-trips', compact('trips', 'vehicles', 'drivers', 'guests', 'reservations'));
    }

    public function storeTrip(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'nullable|exists:fleet_vehicles,id',
            'driver_id' => 'nullable|exists:fleet_drivers,id',
            'reservation_id' => 'nullable|exists:reservations,id',
            'guest_id' => 'nullable|exists:guests,id',
            'trip_type' => 'required|in:airport_pickup,airport_dropoff,city_tour,custom,shuttle',
            'pickup_location' => 'nullable|string|max:200',
            'dropoff_location' => 'nullable|string|max:200',
            'scheduled_at' => 'required|date',
            'passenger_count' => 'nullable|integer|min:1',
            'charge_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $this->svc->scheduleTrip(app('current_property'), $data);
        return back()->with('success', 'Trip berhasil dijadwalkan.');
    }

    public function startTrip($id)
    {
        $trip = FleetTrip::where('property_id', app('current_property')->id)->findOrFail($id);
        $this->svc->startTrip($trip->id);
        return back()->with('success', 'Trip dimulai.');
    }

    public function completeTrip($id)
    {
        $trip = FleetTrip::where('property_id', app('current_property')->id)->findOrFail($id);
        $this->svc->completeTrip($trip->id);
        return back()->with('success', 'Trip selesai.');
    }

    public function cancelTrip($id)
    {
        $trip = FleetTrip::where('property_id', app('current_property')->id)->findOrFail($id);
        $this->svc->cancelTrip($trip->id);
        return back()->with('success', 'Trip dibatalkan.');
    }

    public function chargeTrip($id, Request $request)
    {
        $trip = FleetTrip::where('property_id', app('current_property')->id)->findOrFail($id);
        $folioId = $request->validate(['folio_id' => 'required|exists:folios,id'])['folio_id'];
        $this->svc->chargeToFolio($trip, $folioId);
        return back()->with('success', 'Biaya transport dibebankan ke folio.');
    }

    public function shuttle()
    {
        $propertyId = app('current_property')->id;
        $schedules = ShuttleSchedule::where('property_id', $propertyId)->orderBy('departure_time')->get();
        return view('panel.fo.fleet-shuttle', compact('schedules'));
    }

    public function storeShuttle(Request $request)
    {
        $data = $request->validate([
            'route_name' => 'required|string|max:100',
            'from_location' => 'required|string|max:200',
            'to_location' => 'required|string|max:200',
            'departure_time' => 'required|date_format:H:i',
            'arrival_time' => 'required|date_format:H:i',
            'days_of_week' => 'nullable|array',
            'capacity' => 'nullable|integer|min:1',
        ]);

        ShuttleSchedule::create($data + [
            'property_id' => app('current_property')->id,
            'is_active' => true,
        ]);

        return back()->with('success', 'Jadwal shuttle berhasil ditambahkan.');
    }

    public function updateShuttle(Request $request, $id)
    {
        $schedule = ShuttleSchedule::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'route_name' => 'required|string|max:100',
            'from_location' => 'required|string|max:200',
            'to_location' => 'required|string|max:200',
            'departure_time' => 'required|date_format:H:i',
            'arrival_time' => 'required|date_format:H:i',
            'days_of_week' => 'nullable|array',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $schedule->update($data);
        return back()->with('success', 'Jadwal shuttle berhasil diperbarui.');
    }
}
