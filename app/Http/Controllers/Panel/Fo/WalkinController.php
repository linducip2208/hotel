<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Folio;
use App\Models\FolioCharge;
use App\Models\FolioPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WalkinController extends Controller
{
    public function index()
    {
        $propertyId = app('current_property')->id;

        $availableRooms = Room::with('roomType')
            ->where('property_id', $propertyId)
            ->where('is_active', true)
            ->where('fo_status', 'vacant')
            ->whereIn('hk_status', ['clean', 'inspected'])
            ->orderBy('floor')
            ->orderBy('number')
            ->get();

        $roomTypes = RoomType::where('property_id', $propertyId)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $occupiedRooms = Room::with(['roomType', 'reservationRooms' => function ($q) {
            $q->whereHas('reservation', fn ($q) => $q->whereIn('status', ['checked_in', 'confirmed']));
        }, 'reservationRooms.reservation.primaryGuest'])
            ->where('property_id', $propertyId)
            ->where('is_active', true)
            ->whereIn('fo_status', ['occupied', 'reserved'])
            ->orderBy('floor')
            ->orderBy('number')
            ->get();

        return view('panel.fo.walkin', compact('availableRooms', 'roomTypes', 'occupiedRooms'));
    }

    public function quickRegister(Request $request)
    {
        $request->validate([
            'room_ids' => 'required|array|min:1',
            'room_ids.*' => 'required|integer|exists:rooms,id',
            'guest_name' => 'required|string|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'guest_email' => 'nullable|email|max:255',
            'check_out' => 'required|date|after:today',
            'adults' => 'required|integer|min:1|max:10',
            'children' => 'nullable|integer|min:0|max:10',
            'payment_method' => 'required|string|in:cash,card,qris,transfer',
            'payment_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $propertyId = app('current_property')->id;
        $userId = $request->user()?->id;

        $nameParts = explode(' ', trim($request->guest_name), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $guest = Guest::firstOrCreate(
            [
                'email' => $request->guest_email ?? ('walkin-' . time() . '@walkin.local'),
                'property_id' => $propertyId,
            ],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $request->guest_phone,
                'property_id' => $propertyId,
            ]
        );

        $rooms = Room::with('roomType')
            ->where('property_id', $propertyId)
            ->whereIn('id', $request->room_ids)
            ->get();

        if ($rooms->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Kamar tidak ditemukan.'], 422);
        }

        $checkIn = Carbon::now();
        $checkOut = Carbon::parse($request->check_out);
        $nights = max(1, (int) $checkIn->diffInDays($checkOut));

        $totalRoom = 0;
        $roomRows = [];
        foreach ($rooms as $room) {
            $rate = $room->roomType->base_rate ?? 0;
            $subtotal = $rate * $nights;
            $totalRoom += $subtotal;
            $roomRows[] = ['room' => $room, 'rate' => $rate, 'subtotal' => $subtotal];
        }

        $adults = (int) $request->adults;
        $children = (int) ($request->children ?? 0);

        $reservation = Reservation::create([
            'property_id' => $propertyId,
            'ref' => $this->generateRef(),
            'primary_guest_id' => $guest->id,
            'source' => 'walk_in',
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkOut->toDateString(),
            'nights' => $nights,
            'adults' => $adults,
            'children' => $children,
            'status' => 'checked_in',
            'total_room' => $totalRoom,
            'grand_total' => $totalRoom,
            'balance' => $totalRoom,
            'special_requests' => $request->notes,
            'notes_internal' => 'Walk-in POS — ' . now()->format('d M Y H:i'),
            'checked_in_at' => now(),
            'created_by_user_id' => $userId,
        ]);

        foreach ($roomRows as $row) {
            ReservationRoom::create([
                'reservation_id' => $reservation->id,
                'room_type_id' => $row['room']->room_type_id,
                'rate_plan_id' => 1,
                'room_id' => $row['room']->id,
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'adults' => $adults,
                'children' => $children,
                'subtotal' => $row['subtotal'],
                'status' => 'occupied',
            ]);

            $row['room']->update(['fo_status' => 'occupied']);
        }

        $folio = Folio::create([
            'property_id' => $propertyId,
            'reservation_id' => $reservation->id,
            'guest_id' => $guest->id,
            'folio_no' => 'W-' . $reservation->ref,
            'type' => 'guest',
            'status' => 'open',
            'total_charges' => $totalRoom,
            'balance' => $totalRoom,
            'opened_at' => now(),
            'cashier_id' => $userId,
        ]);

        FolioCharge::create([
            'folio_id' => $folio->id,
            'property_id' => $propertyId,
            'charge_date' => now()->toDateString(),
            'description' => 'Room Charge — Walk-in #' . $reservation->ref,
            'category' => 'room',
            'qty' => count($roomRows),
            'unit_price' => $totalRoom / count($roomRows),
            'amount' => $totalRoom,
            'source_type' => 'reservation',
            'source_ref' => $reservation->ref,
            'posted_by_user_id' => $userId,
        ]);

        $paymentAmount = min((float) ($request->payment_amount ?? 0), $totalRoom);

        if ($paymentAmount > 0) {
            FolioPayment::create([
                'folio_id' => $folio->id,
                'property_id' => $propertyId,
                'payment_date' => now()->toDateString(),
                'amount' => $paymentAmount,
                'method' => $request->payment_method,
                'reference_no' => 'WALKIN-' . $reservation->ref,
                'cashier_id' => $userId,
            ]);

            $folio->update([
                'total_payments' => $paymentAmount,
                'balance' => $totalRoom - $paymentAmount,
            ]);

            $reservation->update([
                'balance' => $totalRoom - $paymentAmount,
            ]);
        }

        $roomNumbers = $rooms->pluck('number')->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Walk-in berhasil! ' . $guest->full_name . ' check-in di kamar ' . implode(', ', $roomNumbers),
            'reservation' => [
                'id' => $reservation->id,
                'ref' => $reservation->ref,
                'guest' => $guest->full_name,
                'rooms' => $roomNumbers,
                'total' => (int) $totalRoom,
                'nights' => $nights,
            ],
            'redirect' => route('panel.fo.reservations.show', $reservation->id),
        ]);
    }

    public function roomDetail(int $id)
    {
        $room = Room::with(['roomType', 'reservationRooms' => function ($q) {
            $q->whereHas('reservation', fn ($q) => $q->whereIn('status', ['checked_in', 'confirmed']));
        }, 'reservationRooms.reservation.primaryGuest'])->findOrFail($id);

        $activeRR = $room->reservationRooms->first();

        return response()->json([
            'id' => $room->id,
            'number' => $room->number,
            'floor' => $room->floor,
            'fo_status' => $room->fo_status,
            'hk_status' => $room->hk_status,
            'room_type' => [
                'name' => $room->roomType->name ?? 'N/A',
                'base_rate' => $room->roomType->base_rate ?? 0,
                'max_occupancy' => $room->roomType->max_occupancy ?? 2,
                'size_sqm' => $room->roomType->size_sqm ?? null,
            ],
            'current_guest' => $activeRR?->reservation?->primaryGuest?->full_name,
            'current_ref' => $activeRR?->reservation?->ref,
        ]);
    }

    protected function generateRef(): string
    {
        return 'HMS-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
    }
}
