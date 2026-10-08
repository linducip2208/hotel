<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\Fo\FolioService;
use App\Services\Fo\ReservationService;
use App\Services\Fo\ReservationValidationException;
use App\Services\Fo\RoomSoldOutException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WalkinController extends Controller
{
    public function __construct(protected ReservationService $reservationService) {}

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
        $propertyId = app('current_property')->id;

        $request->validate([
            'room_ids' => 'required|array|min:1',
            'room_ids.*' => ['required', 'integer', Rule::exists('rooms', 'id')->where('property_id', $propertyId)],
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

        $userId = $request->user()?->id;

        $nameParts = explode(' ', trim($request->guest_name), 2);
        $guestData = [
            'first_name' => $nameParts[0],
            'last_name' => $nameParts[1] ?? '',
            'email' => $request->guest_email ?? ('walkin-'.time().'-'.random_int(100, 999).'@walkin.local'),
            'phone' => $request->guest_phone,
            'adults' => (int) $request->adults,
            'children' => (int) ($request->children ?? 0),
        ];

        try {
            $rooms = Room::with('roomType')
                ->where('property_id', $propertyId)
                ->where('is_active', true)
                ->where('fo_status', 'vacant')
                ->whereIn('id', $request->room_ids)
                ->lockForUpdate()
                ->get();

            if ($rooms->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Kamar tidak tersedia (sudah ditempati atau tidak ada).'], 422);
            }

            // ALL selected rooms go through createWalkIn so pricing, totals,
            // folio charges and occupancy cover every room (no undercharge).
            $reservation = $this->reservationService->createWalkIn(
                app('current_property'),
                $rooms->all(),
                $guestData,
                Carbon::parse($request->check_out)->endOfDay(),
                $userId,
            );

            $reservation = $reservation->fresh(['rooms.room']);

            // Advance payment taken at the desk (money already received → paid),
            // routed through FolioService so the journal entry is posted like
            // every other payment.
            $folio = $reservation->folios->first();
            if ($folio) {
                $paymentAmount = min((float) ($request->payment_amount ?? 0), (float) $reservation->grand_total);
                if ($paymentAmount > 0) {
                    app(FolioService::class)->postPayment($folio, [
                        'amount' => $paymentAmount,
                        'method' => $request->payment_method,
                        'reference_no' => 'WALKIN-'.$reservation->ref,
                        'cashier_id' => $userId,
                    ]);
                }

                $reservation->update(['balance' => $folio->fresh()->balance]);
            }
        } catch (RoomSoldOutException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (ReservationValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $reservation->refresh();
        $roomNumbers = $reservation->rooms->map(fn ($rr) => $rr->room?->number)->filter()->values()->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Walk-in berhasil! '.$reservation->primaryGuest->full_name.' check-in di kamar '.implode(', ', $roomNumbers),
            'reservation' => [
                'id' => $reservation->id,
                'ref' => $reservation->ref,
                'guest' => $reservation->primaryGuest->full_name,
                'rooms' => $roomNumbers,
                'total' => (int) $reservation->grand_total,
                'nights' => $reservation->nights,
            ],
            'redirect' => route('panel.fo.reservations.show', $reservation->id),
        ]);
    }

    public function roomDetail(int $id)
    {
        $room = Room::where('property_id', app('current_property')->id)
            ->with(['roomType', 'reservationRooms' => function ($q) {
                $q->whereHas('reservation', fn ($q) => $q->whereIn('status', ['checked_in', 'confirmed']));
            }, 'reservationRooms.reservation.primaryGuest'])
            ->findOrFail($id);

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
}
