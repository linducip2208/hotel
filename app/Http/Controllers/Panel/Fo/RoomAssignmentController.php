<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\Audit\AuditLogger;
use App\Services\Fo\RoomAssignmentAiService;
use App\Services\Fo\RoomAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Room assignment board.
 *
 * Works on the REAL data model: assignments live on `reservation_rooms.room_id`
 * (per room of a multi-room reservation), not on `reservations.room_id`.
 */
class RoomAssignmentController extends Controller
{
    public function __construct(
        protected RoomAssignmentAiService $aiService,
        protected RoomAssignmentService $assignmentService,
        protected AuditLogger $audit,
    ) {}

    public function index(Request $request)
    {
        $property = app('current_property');
        $date = $request->date ?? now()->toDateString();

        // Rooms of arriving/active reservations without a physical room yet.
        $unassigned = ReservationRoom::query()
            ->whereHas('reservation', fn ($q) => $q
                ->where('property_id', $property->id)
                ->whereIn('status', ['confirmed', 'tentative'])
                ->whereDate('check_in', '<=', $date))
            ->whereNull('room_id')
            ->with(['reservation.primaryGuest.profile', 'reservation.primaryGuest', 'roomType'])
            ->orderBy('check_in')
            ->get();

        $roomTypes = RoomType::where('property_id', $property->id)->orderBy('name')->get();

        $availableRooms = Room::where('property_id', $property->id)
            ->where('is_active', true)
            ->with('roomType')
            ->orderBy('floor')
            ->orderBy('number')
            ->get();

        // Rooms already booked by an active reservation overlapping the date —
        // these must not be offered to another reservation.
        $bookedRoomIds = ReservationRoom::whereNotNull('room_id')
            ->whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>', $date)
            ->whereHas('reservation', fn ($q) => $q
                ->where('property_id', $property->id)
                ->whereIn('status', ['confirmed', 'checked_in', 'tentative']))
            ->pluck('room_id')
            ->unique();
        $recentAssignments = ReservationRoom::whereNotNull('room_id')
            ->whereHas('reservation', fn ($q) => $q
                ->where('property_id', $property->id)
                ->whereDate('check_in', '>=', now()->subDays(7)->toDateString())
                ->whereDate('check_in', '<=', now()->toDateString()))
            ->with(['reservation.primaryGuest', 'room'])
            ->orderByDesc('check_in')
            ->limit(30)
            ->get();

        return view('panel.fo.room-assignment', compact(
            'unassigned', 'roomTypes', 'availableRooms', 'recentAssignments', 'date', 'property', 'bookedRoomIds'
        ));
    }

    /** Assign a physical room to one reservation-room row. */
    public function assign(Request $request)
    {
        $propertyId = app('current_property')->id;

        $validated = $request->validate([
            'reservation_room_id' => ['required', 'integer', Rule::exists('reservation_rooms', 'id')],
            'room_id' => ['required', 'integer', Rule::exists('rooms', 'id')->where('property_id', $propertyId)],
        ]);

        $rr = ReservationRoom::whereHas('reservation', fn ($q) => $q->where('property_id', $propertyId))
            ->findOrFail($validated['reservation_room_id']);
        $reservation = $rr->reservation()->with('rooms')->firstOrFail();

        if (in_array($reservation->status, ['cancelled', 'no_show', 'checked_out'], true)) {
            return back()->withErrors(['assign' => "Reservasi {$reservation->ref} berstatus {$reservation->status} — tidak dapat ditempatkan."]);
        }

        $room = Room::where('property_id', $propertyId)->findOrFail($validated['room_id']);

        // The room must be free for this stay (ignore this row itself).
        $conflict = ReservationRoom::where('room_id', $room->id)
            ->where('id', '!=', $rr->id)
            ->whereDate('check_in', '<=', $rr->check_out->copy()->subDay())
            ->whereDate('check_out', '>', $rr->check_in)
            ->whereHas('reservation', fn ($q) => $q
                ->where('property_id', $propertyId)
                ->whereIn('status', ['confirmed', 'checked_in', 'tentative']))
            ->exists();

        if ($conflict || $room->fo_status === 'out_of_order') {
            return back()->withErrors(['assign' => "Kamar {$room->number} tidak tersedia untuk tanggal menginap ini."]);
        }

        DB::transaction(function () use ($rr, $room, $reservation) {
            $rr->update(['room_id' => $room->id]);
            // Not yet checked in → reserved, never occupied.
            if ($reservation->status !== 'checked_in') {
                $room->update(['fo_status' => 'reserved']);
            } else {
                $room->update(['fo_status' => 'occupied']);
            }

            $this->audit->record('room.assigned', $room, [
                'reservation_ref' => $reservation->ref,
                'reservation_room_id' => $rr->id,
                'room_id' => $room->id,
            ]);
        });

        return back()->with('success', "Reservasi {$reservation->ref} ditempatkan ke Kamar {$room->number}.");
    }

    /** Batch auto-assign for a date via the AI scoring service. */
    public function autoAssign(Request $request)
    {
        $property = app('current_property');
        $date = $request->date ?? now()->toDateString();

        $assigned = $this->aiService->batchAssign($property, $date);

        return back()->with('success', count($assigned).' reservasi berhasil ditempatkan otomatis untuk tanggal '.$date.'.');
    }

    /** Swap physical rooms between two reservation-room rows. */
    public function swap(Request $request)
    {
        $propertyId = app('current_property')->id;

        $validated = $request->validate([
            'reservation_room_a' => ['required', 'integer'],
            'reservation_room_b' => ['required', 'integer', 'different:reservation_room_a'],
        ]);

        $rrA = ReservationRoom::whereHas('reservation', fn ($q) => $q->where('property_id', $propertyId))
            ->findOrFail($validated['reservation_room_a']);
        $rrB = ReservationRoom::whereHas('reservation', fn ($q) => $q->where('property_id', $propertyId))
            ->findOrFail($validated['reservation_room_b']);

        if (! $rrA->room_id || ! $rrB->room_id) {
            return back()->withErrors(['swap' => 'Kedua reservasi harus sudah memiliki kamar sebelum ditukar.']);
        }

        $roomIdA = $rrA->room_id;
        $roomIdB = $rrB->room_id;

        DB::transaction(function () use ($rrA, $rrB, $roomIdA, $roomIdB) {
            $rrA->update(['room_id' => $roomIdB]);
            $rrB->update(['room_id' => $roomIdA]);

            $this->audit->record('room.swapped', $rrA, [
                'reservation_room_a' => $rrA->id,
                'reservation_room_b' => $rrB->id,
                'from_room' => $roomIdA,
                'to_room' => $roomIdB,
            ]);
        });

        return back()->with('success', 'Kamar berhasil ditukar antara dua reservasi.');
    }
}
