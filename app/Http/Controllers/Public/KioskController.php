<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\Request;

final class KioskController extends Controller
{
    /** Kiosk home screen. */
    public function index()
    {
        return view('public.kiosk');
    }

    /** Look up reservation by reference or ID scan. */
    public function lookup(Request $request)
    {
        $request->validate(['ref' => 'required|string']);

        $reservation = Reservation::with(['primaryGuest', 'rooms.roomType'])
            ->where('ref', $request->input('ref'))
            ->orWhereHas('primaryGuest', fn ($q) => $q->where('id_number', $request->input('ref')))
            ->first();

        if (! $reservation) {
            return response()->json(['found' => false, 'message' => 'Reservation not found.']);
        }

        return response()->json([
            'found' => true,
            'reservation' => [
                'id' => $reservation->id,
                'ref' => $reservation->ref,
                'guest_name' => $reservation->primaryGuest?->full_name,
                'check_in' => $reservation->check_in?->toDateString(),
                'check_out' => $reservation->check_out?->toDateString(),
                'status' => $reservation->status,
                'rooms' => $reservation->rooms->map(fn ($rr) => $rr->room?->room_number),
                'balance' => (float) $reservation->balance,
            ],
        ]);
    }

    /** Self check-in flow. */
    public function checkin(Request $request)
    {
        $request->validate(['reservation_id' => 'required|integer']);
        $pid = app('current_property')->id;

        $reservation = Reservation::where('property_id', $pid)->findOrFail($request->input('reservation_id'));

        if (! in_array($reservation->status, ['confirmed', 'arrival'])) {
            return response()->json(['ok' => false, 'message' => 'Reservation not eligible for check-in.']);
        }

        $reservation->update([
            'status' => 'in_house',
            'checked_in_at' => now(),
        ]);

        // Create folio if not exists
        if ($reservation->folios()->count() === 0) {
            $reservation->folios()->create([
                'property_id' => $pid,
                'folio_no' => 'FOL-'.now()->format('Ym').'-'.strtoupper(substr($reservation->ref, -6)),
                'guest_id' => $reservation->primary_guest_id,
                'balance' => 0,
            ]);
        }

        // Update room status
        foreach ($reservation->rooms as $rr) {
            Room::where('id', $rr->room_id)->update(['hk_status' => 'occupied']);
        }

        return response()->json(['ok' => true, 'message' => 'Check-in successful! Enjoy your stay.']);
    }

    /** Print registration receipt. */
    public function printReceipt(int $reservationId)
    {
        $pid = app('current_property')->id;
        $reservation = Reservation::where('property_id', $pid)->with('primaryGuest', 'rooms.roomType')->findOrFail($reservationId);

        return view('public.kiosk-receipt', compact('reservation'));
    }
}
