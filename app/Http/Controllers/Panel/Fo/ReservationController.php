<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\Fo\ReservationService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(protected ReservationService $svc) {}

    public function index(Request $request)
    {
        $property = app('current_property');
        $reservations = Reservation::where('property_id', $property->id)
            ->with(['primaryGuest', 'rooms.roomType'])
            ->orderByDesc('check_in')
            ->paginate(25);
        return view('panel.fo.reservations.index', compact('reservations'));
    }

    public function create()
    {
        return view('panel.fo.reservations.create', [
            'property' => app('current_property'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'rooms' => ['required', 'array', 'min:1'],
            'rooms.*.room_type_id' => ['required', 'integer'],
            'rooms.*.rate_plan_id' => ['required', 'integer'],
            'rooms.*.adults' => ['required', 'integer', 'min:1'],
            'rooms.*.children' => ['nullable', 'integer', 'min:0'],
            'primary_guest.first_name' => ['required', 'string', 'max:100'],
            'primary_guest.last_name' => ['nullable', 'string', 'max:100'],
            'primary_guest.email' => ['nullable', 'email'],
            'primary_guest.phone' => ['nullable', 'string'],
            'special_requests' => ['nullable', 'string'],
            'source' => ['nullable', 'string'],
        ]);

        $data['property_id'] = app('current_property')->id;
        $data['created_by_user_id'] = $request->user()?->id;

        $reservation = $this->svc->create($data);
        return redirect()->route('panel.fo.reservations.show', $reservation->id);
    }

    public function show(int $id)
    {
        $reservation = Reservation::where('property_id', app('current_property')->id)->with(['primaryGuest', 'rooms.roomType', 'addons', 'folios.charges', 'folios.payments'])->findOrFail($id);
        return view('panel.fo.reservations.show', compact('reservation'));
    }

    public function update(Request $request, int $id)
    {
        $reservation = Reservation::where('property_id', app('current_property')->id)->findOrFail($id);
        $reservation->update($request->only(['special_requests', 'arrival_time', 'notes_internal']));
        return back();
    }

    public function cancel(Request $request, int $id)
    {
        $r = Reservation::where('property_id', app('current_property')->id)->findOrFail($id);
        $reason = $request->input('reason', 'No reason given');
        $penalty = (float) $request->input('penalty', 0);
        $this->svc->cancel($r, $reason, $penalty);
        return back();
    }

    public function checkIn(int $id)
    {
        $r = Reservation::where('property_id', app('current_property')->id)->findOrFail($id);
        $this->svc->checkIn($r);
        return back();
    }

    public function checkOut(int $id)
    {
        $r = Reservation::where('property_id', app('current_property')->id)->findOrFail($id);
        $this->svc->checkOut($r);
        return back();
    }

    public function moveRoom(Request $request, int $id)
    {
        $r = Reservation::where('property_id', app('current_property')->id)->findOrFail($id);
        $this->svc->moveRoom($r, (int) $request->input('reservation_room_id'), (int) $request->input('to_room_id'));
        return back();
    }

    public function arrivals()
    {
        $list = Reservation::where('property_id', app('current_property')->id)
            ->where('status', 'confirmed')
            ->whereDate('check_in', now())
            ->with('primaryGuest')->get();
        return view('panel.fo.arrivals', compact('list'));
    }

    public function departures()
    {
        $list = Reservation::where('property_id', app('current_property')->id)
            ->where('status', 'checked_in')
            ->whereDate('check_out', now())
            ->with('primaryGuest')->get();
        return view('panel.fo.departures', compact('list'));
    }

    public function inHouse()
    {
        $list = Reservation::where('property_id', app('current_property')->id)
            ->where('status', 'checked_in')
            ->with('primaryGuest', 'rooms.room')
            ->get();
        return view('panel.fo.in-house', compact('list'));
    }

    public function calendar(Request $request)
    {
        $from = \Carbon\Carbon::parse($request->query('from', now()->toDateString()));
        $to = \Carbon\Carbon::parse($request->query('to', now()->addDays(13)->toDateString()));
        $days = $from->diffInDays($to) + 1;
        $dates = collect();
        for ($d = 0; $d < $days; $d++) $dates->push($from->copy()->addDays($d));

        $property = app('current_property');
        $rooms = \App\Models\Room::where('property_id', $property->id)->where('is_active', true)
            ->with('roomType')->orderBy('floor')->orderBy('number')->get();

        $reservations = \App\Models\ReservationRoom::with('reservation.primaryGuest')
            ->whereHas('reservation', fn ($q) => $q->where('property_id', $property->id)
                ->whereIn('status', ['confirmed', 'checked_in', 'tentative', 'checked_out', 'cancelled', 'no_show']))
            ->whereBetween('check_in', [$from->copy()->subDays(30), $to])
            ->get()
            ->map(fn ($rr) => [
                'id' => $rr->reservation->id,
                'ref' => $rr->reservation->ref,
                'room_id' => $rr->room_id,
                'guest_name' => $rr->reservation->primaryGuest?->full_name ?? 'Guest',
                'status' => $rr->reservation->status,
                'check_in' => $rr->check_in->toDateString(),
                'check_out' => $rr->check_out->toDateString(),
            ])->toArray();

        return view('panel.fo.calendar', compact('rooms', 'dates', 'from', 'to', 'days', 'reservations'));
    }

    public function calendarData(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->addDays(30)->toDateString());

        $property = app('current_property');
        $rooms = \App\Models\Room::where('property_id', $property->id)
            ->where('is_active', true)->with('roomType')
            ->orderBy('floor')->orderBy('number')->get();

        $roomIds = $rooms->pluck('id');
        $reservations = \App\Models\ReservationRoom::with('reservation.primaryGuest')
            ->whereHas('reservation', fn ($q) => $q->where('property_id', $property->id)
                ->whereIn('status', ['confirmed', 'checked_in', 'tentative', 'checked_out', 'cancelled', 'no_show']))
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('check_in', [$from, $to])
                  ->orWhereBetween('check_out', [$from, $to])
                  ->orWhere(fn ($q) => $q->where('check_in', '<', $from)->where('check_out', '>', $to));
            })
            ->get()
            ->map(fn ($rr) => [
                'id' => $rr->reservation->id,
                'ref' => $rr->reservation->ref,
                'room_id' => $rr->room_id,
                'guest_name' => $rr->reservation->primaryGuest?->full_name ?? 'Guest',
                'status' => $rr->reservation->status,
                'check_in' => $rr->check_in->toDateString(),
                'check_out' => $rr->check_out->toDateString(),
            ]);

        return response()->json(compact('rooms', 'reservations', 'from', 'to'));
    }
}
