<?php

namespace App\Http\Controllers\Panel\Activities;

use App\Http\Controllers\Controller;
use App\Models\KidsActivity;
use App\Models\KidsBooking;
use App\Services\Activities\KidsClubService;
use Illuminate\Http\Request;

class KidsClubController extends Controller
{
    protected KidsClubService $service;

    public function __construct(KidsClubService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $propertyId = app('current_property')->id;
        $activities = $this->service->getActivities($propertyId);
        $schedule = $this->service->getSchedule($propertyId);

        return view('panel.activities.kids-club', compact('activities', 'schedule'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'age_min' => 'required|integer|min:0|max:17',
            'age_max' => 'required|integer|min:1|max:18|gte:age_min',
            'capacity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15',
            'schedule' => 'nullable|json',
            'is_active' => 'sometimes|boolean',
        ]);

        KidsActivity::create([
            'property_id' => app('current_property')->id,
            'name' => $data['name'],
            'age_min' => $data['age_min'],
            'age_max' => $data['age_max'],
            'capacity' => $data['capacity'],
            'price' => $data['price'],
            'duration_minutes' => $data['duration_minutes'],
            'schedule' => $data['schedule'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return back()->with('success', 'Aktivitas Kids Club berhasil ditambahkan.');
    }

    public function update(Request $request, int $id)
    {
        $propertyId = app('current_property')->id;
        $activity = KidsActivity::where('property_id', $propertyId)->findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'age_min' => 'sometimes|integer|min:0|max:17',
            'age_max' => 'sometimes|integer|min:1|max:18',
            'capacity' => 'sometimes|integer|min:1',
            'price' => 'sometimes|numeric|min:0',
            'duration_minutes' => 'sometimes|integer|min:15',
            'schedule' => 'nullable|json',
            'is_active' => 'sometimes|boolean',
        ]);

        $activity->update($data);

        return back()->with('success', 'Aktivitas berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $propertyId = app('current_property')->id;
        $activity = KidsActivity::where('property_id', $propertyId)->findOrFail($id);
        $activity->delete();

        return back()->with('success', 'Aktivitas berhasil dihapus.');
    }

    public function bookings()
    {
        $propertyId = app('current_property')->id;
        $bookings = KidsBooking::where('property_id', $propertyId)
            ->with(['activity', 'guest', 'reservation'])
            ->latest('booking_date')
            ->paginate(30);

        $activities = KidsActivity::where('property_id', $propertyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('panel.activities.kids-bookings', compact('bookings', 'activities'));
    }

    public function book(Request $request)
    {
        $data = $request->validate([
            'kids_activity_id' => 'required|exists:kids_activities,id',
            'child_name' => 'required|string|max:255',
            'child_age' => 'required|integer|min:0|max:18',
            'booking_date' => 'required|date',
            'start_time' => 'required',
            'reservation_id' => 'nullable|exists:reservations,id',
            'guest_id' => 'nullable|exists:guests,id',
            'special_requests' => 'nullable|string|max:500',
        ]);

        $data['property_id'] = app('current_property')->id;

        try {
            $booking = $this->service->book($data);
            return back()->with('success', "Booking untuk {$data['child_name']} berhasil dibuat.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function cancel(Request $request, int $id)
    {
        $propertyId = app('current_property')->id;
        $booking = KidsBooking::where('property_id', $propertyId)->findOrFail($id);
        $this->service->cancel($booking->id);

        return back()->with('success', 'Booking berhasil dibatalkan.');
    }
}
