<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\Fo\RoomAssignmentAiService;
use Illuminate\Http\Request;

class RoomAssignmentController extends Controller
{
    public function __construct(protected RoomAssignmentAiService $aiService) {}

    public function index(Request $request)
    {
        $property = app('current_property');
        $date = $request->date ?? now()->toDateString();

        $unassigned = Reservation::where('property_id', $property->id)
            ->whereDate('check_in', $date)
            ->whereIn('status', ['confirmed', 'tentative'])
            ->whereNull('room_id')
            ->with(['primaryGuest.profile', 'roomType'])
            ->orderByDesc('created_at')
            ->get();

        $roomTypes = RoomType::where('property_id', $property->id)->orderBy('name')->get();

        $availableRooms = Room::where('property_id', $property->id)
            ->where('is_active', true)
            ->with('roomType')
            ->orderBy('floor')
            ->orderBy('number')
            ->get();

        $recentAssignments = Reservation::where('property_id', $property->id)
            ->whereNotNull('room_id')
            ->whereDate('check_in', '>=', now()->subDays(7)->toDateString())
            ->whereDate('check_in', '<=', now()->toDateString())
            ->with(['primaryGuest', 'room'])
            ->orderByDesc('check_in')
            ->limit(30)
            ->get();

        return view('panel.fo.room-assignment', compact(
            'unassigned', 'roomTypes', 'availableRooms', 'recentAssignments', 'date', 'property'
        ));
    }

    public function assign(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'room_id'        => 'required|exists:rooms,id',
        ]);

        $reservation = Reservation::findOrFail($request->reservation_id);
        $room = Room::findOrFail($request->room_id);

        $reservation->update(['room_id' => $room->id]);
        $room->update(['fo_status' => 'occupied']);

        return back()->with('success', "Reservasi #{$reservation->ref} ditempatkan ke Kamar {$room->number}.");
    }

    public function autoAssign(Request $request)
    {
        $property = app('current_property');
        $date = $request->date ?? now()->toDateString();

        $assigned = $this->aiService->batchAssign($property, $date);

        $count = count($assigned);
        return back()->with('success', "{$count} reservasi berhasil ditempatkan otomatis untuk tanggal {$date}.");
    }

    public function swap(Request $request)
    {
        $request->validate([
            'reservation_a' => 'required|exists:reservations,id',
            'reservation_b' => 'required|exists:reservations,id|different:reservation_a',
        ]);

        $resA = Reservation::findOrFail($request->reservation_a);
        $resB = Reservation::findOrFail($request->reservation_b);

        $roomA = $resA->room_id;
        $roomB = $resB->room_id;

        $resA->update(['room_id' => $roomB]);
        $resB->update(['room_id' => $roomA]);

        return back()->with('success', 'Kamar berhasil ditukar antara dua reservasi.');
    }
}
