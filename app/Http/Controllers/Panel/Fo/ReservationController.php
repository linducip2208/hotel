<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\RatePlan;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\Fo\OutstandingBalanceException;
use App\Services\Fo\ReservationService;
use App\Services\Fo\ReservationValidationException;
use App\Services\Fo\RoomAssignmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReservationController extends Controller
{
    public function __construct(protected ReservationService $svc) {}

    public function index(Request $request)
    {
        $property = app('current_property');

        $query = Reservation::where('property_id', $property->id)
            ->with(['primaryGuest', 'rooms.roomType']);

        // Search by ref or guest name/email.
        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('ref', 'like', "%{$search}%")
                    ->orWhereHas('primaryGuest', fn ($g) => $g
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        // Status filter.
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        // Date scope: arrivals/departures overlapping a date.
        if ($date = $request->query('date')) {
            $query->whereDate('check_in', '<=', $date)
                ->whereDate('check_out', '>', $date);
        }

        $reservations = $query->orderByDesc('check_in')->paginate(25)->withQueryString();

        return view('panel.fo.reservations.index', compact('reservations'));
    }

    public function create()
    {
        $property = app('current_property');

        return view('panel.fo.reservations.create', [
            'property' => $property,
            'roomTypes' => RoomType::where('property_id', $property->id)
                ->where('is_active', true)->orderBy('name')->get(),
            'ratePlans' => RatePlan::where('property_id', $property->id)
                ->where('is_active', true)->orderBy('name')->get(),
            'sources' => ['direct' => 'Direct', 'walk_in' => 'Walk-in', 'website' => 'Website', 'ota' => 'OTA', 'travel_agent' => 'Travel Agent', 'corporate' => 'Corporate', 'group' => 'Group', 'phone' => 'Telepon', 'whatsapp' => 'WhatsApp', 'other' => 'Lainnya'],
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

        try {
            $reservation = $this->svc->create($data);
        } catch (ReservationValidationException $e) {
            return back()->withInput()->withErrors(['check_in' => $e->getMessage()]);
        }

        return redirect()->route('panel.fo.reservations.show', $reservation->id)
            ->with('success', 'Reservasi berhasil dibuat.');
    }

    public function show(int $id)
    {
        $reservation = Reservation::where('property_id', app('current_property')->id)
            ->with(['primaryGuest', 'rooms.roomType', 'rooms.ratePlan', 'rooms.room', 'addons', 'folios.charges', 'folios.payments'])
            ->findOrFail($id);

        $readiness = $this->svc->checkInReadiness($reservation);
        $readinessBlocked = collect($readiness)->where('state', 'block')->where('key', '!=', 'duplicate')->isNotEmpty();

        // Free rooms per reservation-room row for the move-room action.
        $freeRooms = [];
        foreach ($reservation->rooms as $rr) {
            $freeRooms[$rr->id] = app(RoomAssignmentService::class)
                ->getAvailableRooms(
                    $reservation->property_id,
                    $rr->room_type_id,
                    Carbon::parse($rr->check_in),
                    Carbon::parse($rr->check_out),
                )
                ->reject(fn ($room) => $room->id === $rr->room_id)
                ->values();
        }

        // Cancellation policy preview (estimated penalty).
        $policyPreview = $this->svc->computeCancellationPenalty($reservation);

        return view('panel.fo.reservations.show', compact(
            'reservation', 'readiness', 'readinessBlocked', 'freeRooms', 'policyPreview'
        ));
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

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->svc->cancel($r, $data['reason'], null, $request->user()?->id);
        } catch (ReservationValidationException $e) {
            return back()->withErrors(['cancel' => $e->getMessage()]);
        }

        return back()->with('success', 'Reservasi dibatalkan. Inventory telah dirilis.');
    }

    public function checkIn(int $id)
    {
        $r = Reservation::where('property_id', app('current_property')->id)->findOrFail($id);

        try {
            $this->svc->checkIn($r, auth()->id());
        } catch (ReservationValidationException $e) {
            return back()->withErrors(['check_in' => $e->getMessage()]);
        }

        return back()->with('success', 'Tamu berhasil di-check-in.');
    }

    public function checkOut(Request $request, int $id)
    {
        $r = Reservation::where('property_id', app('current_property')->id)->findOrFail($id);

        $override = $request->boolean('force');
        $reason = $request->input('override_reason');

        if ($override) {
            if (! Gate::allows('fo.reservation.force_checkout')) {
                abort(403, 'Anda tidak memiliki izin override check-out.');
            }
            if (! $reason || trim($reason) === '') {
                return back()->withErrors(['checkout' => 'Alasan override check-out wajib diisi.']);
            }
        }

        try {
            $this->svc->checkOut($r, auth()->id(), $override, $reason);
        } catch (OutstandingBalanceException $e) {
            return back()->withErrors(['checkout' => $e->getMessage().' Gunakan override manager bila diperlukan.']);
        } catch (ReservationValidationException $e) {
            return back()->withErrors(['checkout' => $e->getMessage()]);
        }

        return back()->with('success', 'Check-out berhasil. Kamar ditandai dirty dan tugas housekeeping dibuat.');
    }

    public function readiness(int $id)
    {
        $r = Reservation::where('property_id', app('current_property')->id)->findOrFail($id);

        return response()->json(['checks' => $this->svc->checkInReadiness($r)]);
    }

    public function moveRoom(Request $request, int $id)
    {
        $r = Reservation::where('property_id', app('current_property')->id)->findOrFail($id);

        try {
            $this->svc->moveRoom($r, (int) $request->input('reservation_room_id'), (int) $request->input('to_room_id'));
        } catch (ReservationValidationException $e) {
            return back()->withErrors(['move_room' => $e->getMessage()]);
        }

        return back()->with('success', 'Kamar berhasil dipindahkan.');
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
        $from = Carbon::parse($request->query('from', now()->toDateString()));
        $to = Carbon::parse($request->query('to', now()->addDays(13)->toDateString()));
        $days = $from->diffInDays($to) + 1;
        $dates = collect();
        for ($d = 0; $d < $days; $d++) {
            $dates->push($from->copy()->addDays($d));
        }

        $property = app('current_property');
        $rooms = Room::where('property_id', $property->id)->where('is_active', true)
            ->with('roomType')->orderBy('floor')->orderBy('number')->get();

        $reservations = ReservationRoom::with('reservation.primaryGuest')
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
        $rooms = Room::where('property_id', $property->id)
            ->where('is_active', true)->with('roomType')
            ->orderBy('floor')->orderBy('number')->get();

        $roomIds = $rooms->pluck('id');
        $reservations = ReservationRoom::with('reservation.primaryGuest')
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
