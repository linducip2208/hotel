<?php

namespace App\Http\Controllers\Panel\Sales;

use App\Http\Controllers\Controller;
use App\Models\EventBooking;
use App\Models\EventType;
use App\Models\Guest;
use App\Models\Room;
use App\Services\Sales\EventPlanningService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $propertyId = app('current_property')->id;
        $service = app(EventPlanningService::class);
        $month = $request->get('month', now()->format('Y-m'));

        $calendar = $service->getCalendar(app('current_property'), $month);
        $eventTypes = EventType::where('property_id', $propertyId)->where('is_active', true)->orderBy('name')->get();

        $upcoming = EventBooking::where('property_id', $propertyId)
            ->where('event_date', '>=', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->with(['eventType', 'guest', 'venue'])
            ->orderBy('event_date')
            ->paginate(15);

        $view = $request->get('view', 'calendar');

        return view('panel.sales.events-index', compact('calendar', 'eventTypes', 'upcoming', 'month', 'view'));
    }

    public function create()
    {
        $propertyId = app('current_property')->id;
        $eventTypes = EventType::where('property_id', $propertyId)->where('is_active', true)->orderBy('name')->get();
        $guests = Guest::where('property_id', $propertyId)->orderBy('first_name')->limit(200)->get();
        $rooms = Room::where('property_id', $propertyId)->where('is_active', true)->with('roomType')->orderBy('room_number')->get();

        return view('panel.sales.events-create', compact('eventTypes', 'guests', 'rooms'));
    }

    public function store(Request $request, EventPlanningService $service)
    {
        $data = $request->validate([
            'event_name' => 'required|string|max:255',
            'event_type_id' => 'required|exists:event_types,id',
            'guest_id' => 'required|exists:guests,id',
            'event_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'expected_guests' => 'required|integer|min:1',
            'venue_id' => 'nullable|exists:rooms,id',
            'total_quoted' => 'nullable|numeric|min:0',
            'deposit_paid' => 'nullable|numeric|min:0',
            'setup_requirements' => 'nullable|array',
            'catering_requirements' => 'nullable|array',
            'special_requests' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'assigned_to_user_id' => 'nullable|exists:users,id',
        ]);

        $data['total_quoted'] = $data['total_quoted'] ?? 0;
        $data['deposit_paid'] = $data['deposit_paid'] ?? 0;

        $booking = $service->createBooking(app('current_property'), $data);

        return redirect()->route('panel.sales.events.show', $booking->id)
            ->with('success', 'Event booking berhasil dibuat.');
    }

    public function show($id)
    {
        $booking = EventBooking::where('property_id', app('current_property')->id)
            ->with(['eventType', 'guest', 'venue.roomType', 'assignedUser', 'folio', 'services'])
            ->findOrFail($id);

        $service = app(EventPlanningService::class);
        $totals = $service->calculateTotal($booking);

        return view('panel.sales.events-show', compact('booking', 'totals'));
    }

    public function update(Request $request, $id)
    {
        $booking = EventBooking::where('property_id', app('current_property')->id)->findOrFail($id);

        $data = $request->validate([
            'event_name' => 'required|string|max:255',
            'event_type_id' => 'required|exists:event_types,id',
            'guest_id' => 'required|exists:guests,id',
            'event_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'expected_guests' => 'required|integer|min:1',
            'venue_id' => 'nullable|exists:rooms,id',
            'total_quoted' => 'nullable|numeric|min:0',
            'deposit_paid' => 'nullable|numeric|min:0',
            'setup_requirements' => 'nullable|array',
            'catering_requirements' => 'nullable|array',
            'special_requests' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'assigned_to_user_id' => 'nullable|exists:users,id',
        ]);

        $booking->update($data);

        return back()->with('success', 'Event booking berhasil diperbarui.');
    }

    public function addService(Request $request, EventPlanningService $service)
    {
        $request->validate([
            'event_booking_id' => 'required|exists:event_bookings,id',
            'service_name' => 'required|string|max:255',
            'vendor_name' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
            'sell_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $booking = EventBooking::where('property_id', app('current_property')->id)
            ->findOrFail($request->event_booking_id);

        $service->addService($booking, $request->only([
            'service_name', 'vendor_name', 'cost', 'sell_price', 'notes',
        ]));

        return back()->with('success', 'Layanan berhasil ditambahkan ke event.');
    }

    public function updateStatus(Request $request, $id)
    {
        $booking = EventBooking::where('property_id', app('current_property')->id)->findOrFail($id);

        $request->validate([
            'status' => 'required|in:inquiry,tentative,confirmed,cancelled,completed',
        ]);

        app(EventPlanningService::class)->updateStatus($booking, $request->status);

        return back()->with('success', 'Status event berhasil diperbarui.');
    }

    public function types()
    {
        $types = EventType::where('property_id', app('current_property')->id)
            ->orderBy('name')
            ->paginate(20);

        return view('panel.sales.events-types', compact('types'));
    }

    public function storeType(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'min_guests' => 'nullable|integer|min:1',
            'max_guests' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        EventType::create($data + ['property_id' => app('current_property')->id]);

        return back()->with('success', 'Tipe event berhasil ditambahkan.');
    }

    public function updateType(Request $request, $id)
    {
        $type = EventType::where('property_id', app('current_property')->id)->findOrFail($id);

        $type->update($request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'min_guests' => 'nullable|integer|min:1',
            'max_guests' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]));

        return back()->with('success', 'Tipe event berhasil diperbarui.');
    }

    public function destroyType($id)
    {
        $type = EventType::where('property_id', app('current_property')->id)->findOrFail($id);
        $type->delete();

        return back()->with('success', 'Tipe event berhasil dihapus.');
    }
}
