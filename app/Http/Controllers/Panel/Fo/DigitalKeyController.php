<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\DoorLockEvent;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\Lock\LockService;
use Illuminate\Http\Request;

class DigitalKeyController extends Controller
{
    public function __construct(protected LockService $lock) {}

    public function index()
    {
        $propertyId = app('current_property')->id;

        $reservations = Reservation::where('property_id', $propertyId)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->whereNotNull('check_in')
            ->with(['primaryGuest', 'rooms.room', 'doorLockEvents' => function ($q) {
                $q->where('event_type', 'key_issued')->where('source', 'mobile_pin')->orderByDesc('occurred_at');
            }])
            ->orderBy('check_in')
            ->get();

        return view('panel.fo.digital-keys', compact('reservations'));
    }

    public function issue($reservationId, Request $request)
    {
        $propertyId = app('current_property')->id;
        $reservation = Reservation::where('property_id', $propertyId)->findOrFail($reservationId);

        $pin = $this->lock->generatePin($reservation, $request->integer('length', 6));

        return back()->with('success', "PIN digital berhasil dibuat: {$pin}. Simpan dan berikan kepada tamu.");
    }

    public function revoke($reservationId)
    {
        $propertyId = app('current_property')->id;
        $reservation = Reservation::where('property_id', $propertyId)->findOrFail($reservationId);

        DoorLockEvent::where('reservation_id', $reservationId)
            ->where('event_type', 'key_issued')
            ->where('source', 'mobile_pin')
            ->update(['payload->revoked' => true, 'payload->revoked_at' => now()->toDateTimeString()]);

        DoorLockEvent::create([
            'property_id' => $propertyId,
            'room_id' => $reservation->rooms()->first()?->room_id,
            'reservation_id' => $reservationId,
            'guest_id' => $reservation->primary_guest_id,
            'event_type' => 'key_revoked',
            'source' => 'mobile_pin',
            'payload' => ['revoked_at' => now()->toDateTimeString(), 'reason' => 'manual_revoke'],
            'occurred_at' => now(),
        ]);

        return back()->with('success', 'Semua kunci digital untuk reservasi ini telah dicabut.');
    }
}
