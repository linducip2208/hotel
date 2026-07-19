<?php

namespace App\Services\Fo;

use App\Jobs\BuildGuestProfileJob;
use App\Jobs\SendBookingConfirmationJob;
use App\Jobs\SendPostStayFollowupJob;
use App\Models\Folio;
use App\Models\Guest;
use App\Models\Inventory;
use App\Models\Property;
use App\Models\Rate;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Services\Accounting\Pb1Calculator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservationService
{
    public function __construct(
        protected Pb1Calculator $pb1,
        protected RoomAssignmentService $roomAssignment,
    ) {}

    /**
     * Create reservation with rooms + auto-create primary folio.
     * Atomic — wraps in transaction.
     */
    public function create(array $payload): Reservation
    {
        return DB::transaction(function () use ($payload) {
            $property = Property::findOrFail($payload['property_id']);
            $guest = $this->resolveGuest($payload['primary_guest'], $property->id);

            $checkIn = Carbon::parse($payload['check_in']);
            $checkOut = Carbon::parse($payload['check_out']);
            $nights = $checkIn->diffInDays($checkOut);

            $totalRoom = 0; $rooms = [];
            foreach ($payload['rooms'] as $r) {
                $subtotal = $this->priceRoom($property->id, $r['room_type_id'], $r['rate_plan_id'], $checkIn, $checkOut);
                $totalRoom += $subtotal;
                $rooms[] = $r + ['subtotal' => $subtotal];
                $this->checkAvailability($property->id, $r['room_type_id'], $checkIn, $checkOut);
            }

            $totalAddons = collect($payload['addons'] ?? [])->sum('subtotal');
            $serviceCharge = round(($totalRoom + $totalAddons) * 0.10, 2);
            $taxableBase = $totalRoom + $totalAddons + $serviceCharge;
            $pb1 = $this->pb1->calculate($property, $taxableBase);

            $reservation = Reservation::create([
                'property_id' => $property->id,
                'ref' => $this->generateRef($property),
                'primary_guest_id' => $guest->id,
                'company_id' => $payload['company_id'] ?? null,
                'travel_agent_id' => $payload['travel_agent_id'] ?? null,
                'source' => $payload['source'] ?? 'direct',
                'source_ref' => $payload['source_ref'] ?? null,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'nights' => $nights,
                'adults' => collect($payload['rooms'])->sum('adults'),
                'children' => collect($payload['rooms'])->sum('children'),
                'status' => 'confirmed',
                'total_room' => $totalRoom,
                'total_addons' => $totalAddons,
                'service_charge' => $serviceCharge,
                'tax_total' => $pb1,
                'grand_total' => $taxableBase + $pb1,
                'balance' => $taxableBase + $pb1,
                'currency' => 'IDR',
                'special_requests' => $payload['special_requests'] ?? null,
                'created_by_user_id' => $payload['created_by_user_id'] ?? null,
            ]);

            foreach ($rooms as $r) {
                ReservationRoom::create([
                    'reservation_id' => $reservation->id,
                    'room_type_id' => $r['room_type_id'],
                    'rate_plan_id' => $r['rate_plan_id'],
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'adults' => $r['adults'] ?? 1,
                    'children' => $r['children'] ?? 0,
                    'subtotal' => $r['subtotal'],
                ]);
            }

            foreach (($payload['addons'] ?? []) as $a) {
                $reservation->addons()->create($a);
            }

            $this->reserveInventory($property->id, $rooms, $checkIn, $checkOut);

            // Auto-open primary folio
            Folio::create([
                'property_id' => $property->id,
                'reservation_id' => $reservation->id,
                'guest_id' => $guest->id,
                'folio_no' => 'F-'.$reservation->ref,
                'type' => 'guest',
                'status' => 'open',
                'currency' => 'IDR',
            ]);

            $fresh = $reservation->fresh(['rooms', 'addons', 'folios']);

            $this->roomAssignment->assign($fresh, $guest->preferences ?? []);

            SendBookingConfirmationJob::dispatch($reservation->id)->afterCommit();

            // Trigger drip campaign on booking confirmation
            if (class_exists(\App\Services\Marketing\DripCampaignService::class)) {
                app(\App\Services\Marketing\DripCampaignService::class)->triggerCampaign('booking_confirmed', $fresh);
            }

            return $fresh;
        });
    }

    public function checkIn(Reservation $r, ?int $userId = null): Reservation
    {
        $r->status = 'checked_in';
        $r->checked_in_at = now();
        $r->save();
        return $r;
    }

    public function checkOut(Reservation $r, ?int $userId = null): Reservation
    {
        $r->status = 'checked_out';
        $r->checked_out_at = now();
        $r->save();

        foreach ($r->folios as $folio) {
            if ($folio->status === 'open' && (float) $folio->balance === 0.0) {
                $folio->status = 'closed';
                $folio->closed_at = now();
                $folio->save();
            }
        }

        // Post-stay: rebuild guest intelligence + send review request (1h delay)
        BuildGuestProfileJob::dispatch($r->primary_guest_id);
        SendPostStayFollowupJob::dispatch($r->id)->delay(now()->addHour());

        // Auto-charge minibar consumption on checkout
        try {
            foreach ($r->rooms as $rr) {
                app(\App\Services\Hk\MinibarService::class)->autoChargeOnCheckout($r->id, $rr->room_id, $userId ?? auth()->id() ?? 1);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Minibar auto-charge failed on checkout', [
                'reservation_id' => $r->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $r;
    }

    public function cancel(Reservation $r, string $reason, float $penalty = 0): Reservation
    {
        $r->status = 'cancelled';
        $r->cancelled_at = now();
        $r->cancellation_reason = $reason;
        $r->cancellation_penalty = $penalty;
        $r->save();
        $this->releaseInventory($r);
        return $r;
    }

    public function moveRoom(Reservation $r, int $reservationRoomId, int $newRoomId): Reservation
    {
        $rr = $r->rooms()->findOrFail($reservationRoomId);
        $rr->room_id = $newRoomId;
        $rr->save();
        return $r->fresh();
    }

    protected function resolveGuest(array $g, int $propertyId): Guest
    {
        $email = $g['email'] ?? null;
        if ($email && $existing = Guest::where('email', $email)->first()) {
            $existing->update(array_filter($g, fn ($v) => $v !== null && $v !== ''));
            return $existing;
        }
        return Guest::create($g + ['property_id' => $propertyId]);
    }

    protected function priceRoom(int $propertyId, int $roomTypeId, int $ratePlanId, Carbon $in, Carbon $out): float
    {
        $rates = Rate::query()
            ->where('property_id', $propertyId)
            ->where('room_type_id', $roomTypeId)
            ->where('rate_plan_id', $ratePlanId)
            ->whereBetween('date', [$in->copy()->toDateString(), $out->copy()->subDay()->toDateString()])
            ->where('closed', false)
            ->get();

        if ($rates->isEmpty()) {
            return 0;
        }
        return (float) $rates->sum('amount');
    }

    protected function checkAvailability(int $propertyId, int $roomTypeId, Carbon $in, Carbon $out): void
    {
        $cursor = $in->copy();
        while ($cursor->lt($out)) {
            $inv = Inventory::firstOrCreate(
                ['property_id' => $propertyId, 'room_type_id' => $roomTypeId, 'date' => $cursor->toDateString()],
                ['total' => 0, 'sold' => 0, 'blocked' => 0, 'out_of_order' => 0]
            );
            if ($inv->available <= 0) {
                throw new \RuntimeException("No availability on {$cursor->toDateString()} for room type {$roomTypeId}");
            }
            $cursor->addDay();
        }
    }

    protected function reserveInventory(int $propertyId, array $rooms, Carbon $in, Carbon $out): void
    {
        foreach ($rooms as $r) {
            $cursor = $in->copy();
            while ($cursor->lt($out)) {
                Inventory::where([
                    'property_id' => $propertyId,
                    'room_type_id' => $r['room_type_id'],
                    'date' => $cursor->toDateString(),
                ])->increment('sold');
                $cursor->addDay();
            }
        }
    }

    protected function releaseInventory(Reservation $r): void
    {
        foreach ($r->rooms as $rr) {
            $cursor = $rr->check_in->copy();
            while ($cursor->lt($rr->check_out)) {
                Inventory::where([
                    'property_id' => $r->property_id,
                    'room_type_id' => $rr->room_type_id,
                    'date' => $cursor->toDateString(),
                ])->where('sold', '>', 0)->decrement('sold');
                $cursor->addDay();
            }
        }
    }

    protected function generateRef(Property $property): string
    {
        return 'HMS-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }
}
