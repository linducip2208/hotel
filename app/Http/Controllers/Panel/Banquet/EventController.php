<?php

namespace App\Http\Controllers\Panel\Banquet;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\FunctionRoom;
use App\Services\Banquet\EventService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(protected EventService $svc) {}

    public function index(Request $request)
    {
        $query = Event::where('property_id', app('current_property')->id)
            ->with('functionRoom', 'company', 'primaryContact');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('event_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('event_date', [$request->from, $request->to]);
        }

        $events = $query->orderByDesc('event_date')->paginate(50)->appends($request->query());
        return view('panel.banquet.events.index', compact('events'));
    }

    public function calendar()
    {
        $rooms = FunctionRoom::where('property_id', app('current_property')->id)->where('is_active', true)->get();
        $events = Event::where('property_id', app('current_property')->id)
            ->whereBetween('event_date', [now()->subDays(7), now()->addMonths(2)])
            ->with('functionRoom')->get();
        return view('panel.banquet.calendar', compact('rooms', 'events'));
    }

    public function create()
    {
        $rooms = FunctionRoom::where('property_id', app('current_property')->id)->where('is_active', true)->get();
        return view('panel.banquet.events.create', compact('rooms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'event_type' => 'required|string',
            'function_room_id' => 'required|integer',
            'event_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'expected_attendees' => 'required|integer|min:1',
            'venue_rate' => 'required|numeric|min:0',
            'setup' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $data['property_id'] = app('current_property')->id;
        $event = $this->svc->create($data);
        return redirect()->route('panel.banquet.events.show', $event->id);
    }

    public function show(int $id)
    {
        $event = Event::with('functionRoom', 'company', 'primaryContact', 'menuItems')->findOrFail($id);
        return view('panel.banquet.events.show', compact('event'));
    }

    public function edit($id)
    {
        $event = Event::with('functionRoom')->findOrFail($id);
        $functionRooms = FunctionRoom::where('property_id', app('current_property')->id)->get();
        $companies = \App\Models\Company::where('property_id', app('current_property')->id)->get();
        $guests = \App\Models\Guest::where('property_id', app('current_property')->id)->orderBy('last_name')->get();
        return view('panel.banquet.events.edit', compact('event', 'functionRooms', 'companies', 'guests'));
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_type' => 'required|string',
            'function_room_id' => 'required|exists:function_rooms,id',
            'company_id' => 'nullable|exists:companies,id',
            'primary_contact_guest_id' => 'nullable|exists:guests,id',
            'event_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'setup' => 'nullable|string',
            'expected_attendees' => 'nullable|integer',
            'venue_rate' => 'nullable|numeric',
            'status' => 'required|in:inquiry,tentative,definite,completed,cancelled',
            'notes' => 'nullable|string',
        ]);
        $event->update($validated);
        return redirect()->route('panel.banquet.events.show', $event->id)->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();
        return redirect()->route('panel.banquet.index')->with('success', 'Event berhasil dihapus.');
    }

    public function updateStatus(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $request->validate(['status' => 'required|in:inquiry,tentative,definite,completed,cancelled']);
        $event->update(['status' => $request->status]);
        return back()->with('success', 'Status event diperbarui.');
    }

    public function addMenu(Request $request, int $id)
    {
        $event = Event::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
        ]);
        $this->svc->addMenuItem($event, $data);
        return back();
    }

    public function beo(int $id)
    {
        $event = Event::with('functionRoom', 'menuItems')->findOrFail($id);
        $beo = $this->svc->generateBeo($event);
        return view('panel.banquet.events.beo', compact('beo', 'event'));
    }

    public function functionRooms()
    {
        $rooms = FunctionRoom::where('property_id', app('current_property')->id)->paginate(50);
        return view('panel.banquet.function-rooms', compact('rooms'));
    }

    public function storeFunctionRoom(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'code' => 'required|string',
            'capacity_classroom' => 'nullable|integer',
            'capacity_theatre' => 'nullable|integer',
            'capacity_banquet' => 'nullable|integer',
            'half_day_rate' => 'nullable|numeric',
            'full_day_rate' => 'nullable|numeric',
        ]);
        FunctionRoom::create($data + ['property_id' => app('current_property')->id]);
        return back()->with('success', 'Function room berhasil ditambahkan.');
    }

    public function updateFunctionRoom(Request $request, $id)
    {
        $room = FunctionRoom::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string',
            'code' => 'required|string',
            'capacity_classroom' => 'nullable|integer',
            'capacity_theatre' => 'nullable|integer',
            'capacity_banquet' => 'nullable|integer',
            'half_day_rate' => 'nullable|numeric',
            'full_day_rate' => 'nullable|numeric',
        ]);
        $room->update($data);
        return back()->with('success', 'Function room berhasil diperbarui.');
    }

    public function destroyFunctionRoom($id)
    {
        $room = FunctionRoom::where('property_id', app('current_property')->id)->findOrFail($id);
        $room->delete();
        return back()->with('success', 'Function room berhasil dihapus.');
    }
}
