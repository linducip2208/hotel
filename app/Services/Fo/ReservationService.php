<?php

namespace App\Services\Fo;

use App\Jobs\BuildGuestProfileJob;
use App\Jobs\SendBookingConfirmationJob;
use App\Jobs\SendPostStayFollowupJob;
use App\Models\CancellationPolicy;
use App\Models\Folio;
use App\Models\FolioPayment;
use App\Models\Guest;
use App\Models\HkTask;
use App\Models\Inventory;
use App\Models\Property;
use App\Models\RatePlan;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\Accounting\Pb1Calculator;
use App\Services\Audit\AuditLogger;
use App\Services\Hk\MinibarService;
use App\Services\Promo\PromoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReservationService
{
    public function __construct(
        protected Pb1Calculator $pb1,
        protected RoomAssignmentService $roomAssignment,
        protected PricingService $pricing,
        protected AuditLogger $audit,
    ) {}

    /**
     * Create reservation with rooms + auto-create primary folio.
     * Atomic — wraps in transaction. Availability is re-checked and reserved
     * under row locks to prevent overselling.
     */
    public function create(array $payload): Reservation
    {
        return DB::transaction(function () use ($payload) {
            $property = Property::findOrFail($payload['property_id']);
            $guest = $this->resolveGuest($payload['primary_guest'], $property->id);

            $checkIn = Carbon::parse($payload['check_in'])->startOfDay();
            $checkOut = Carbon::parse($payload['check_out'])->startOfDay();

            if ($checkOut->lte($checkIn)) {
                throw new ReservationValidationException('Tanggal check-out harus setelah tanggal check-in.');
            }

            $totalRoom = 0;
            $rooms = [];
            $promo = null;
            if (! empty($payload['promo_code'])) {
                $promo = app(PromoService::class)
                    ->lookup($payload['promo_code'], $property->id);
                if (! $promo) {
                    throw new ReservationValidationException('Kode promo tidak valid atau sudah kedaluwarsa.');
                }
            }

            foreach ($payload['rooms'] as $roomIndex => $r) {
                $this->assertRoomTypeAndPlanBelongToProperty($property->id, (int) $r['room_type_id'], (int) $r['rate_plan_id']);

                $quote = $this->pricing->quote($property, (int) $r['room_type_id'], (int) $r['rate_plan_id'], $checkIn, $checkOut, (int) ($r['adults'] ?? 1), (int) ($r['children'] ?? 0), $promo);
                if (! empty($quote['restrictions'])) {
                    throw new ReservationValidationException('Rate plan tidak dapat dijual untuk tanggal ini: '.implode('; ', $quote['restrictions']));
                }

                $totalRoom += $quote['room_total'];
                $rooms[] = $r + ['subtotal' => $quote['room_total'], 'per_night' => $quote['nightly'], 'quote' => $quote];

                // Concurrency-safe availability check (row lock, verified again
                // below). Rooms earlier in this same payload count against capacity.
                $this->checkAvailability($property->id, (int) $r['room_type_id'], $checkIn, $checkOut, $roomIndex);
            }

            $totalAddons = collect($payload['addons'] ?? [])->sum('subtotal');
            // Promo discount applies once to the room subtotal (not per room quote).
            $discount = $promo ? (float) ($rooms[0]['quote']['discount'] ?? 0) : 0.0;
            $discountedRoomTotal = max(0.0, $totalRoom - $discount);
            $serviceCharge = round(($discountedRoomTotal + $totalAddons) * $this->serviceChargePct($property) / 100, 2);
            $taxableBase = $discountedRoomTotal + $totalAddons + $serviceCharge;
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
                'nights' => $checkIn->diffInDays($checkOut),
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
                'promo_code' => $promo?->code,
                'discount_amount' => $discount,
                'special_requests' => $payload['special_requests'] ?? null,
                'created_by_user_id' => $payload['created_by_user_id'] ?? null,
            ]);

            foreach ($rooms as $r) {
                ReservationRoom::create([
                    'reservation_id' => $reservation->id,
                    'room_type_id' => $r['room_type_id'],
                    'rate_plan_id' => $r['rate_plan_id'],
                    'room_id' => $r['room_id'] ?? null,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'adults' => $r['adults'] ?? 1,
                    'children' => $r['children'] ?? 0,
                    'subtotal' => $r['subtotal'],
                    'per_night_rates' => $r['per_night'],
                ]);
            }

            foreach (($payload['addons'] ?? []) as $a) {
                $reservation->addons()->create($a);
            }

            // Reserve inventory atomically (locked rows, re-checked).
            $this->reserveInventory($property->id, $rooms, $checkIn, $checkOut);

            // Auto-open primary folio
            $folio = Folio::create([
                'property_id' => $property->id,
                'reservation_id' => $reservation->id,
                'guest_id' => $guest->id,
                'folio_no' => 'F-'.$reservation->ref,
                'type' => 'guest',
                'status' => 'open',
                'currency' => 'IDR',
            ]);

            // Post the full-stay charges up front (room + service charge + PB1).
            // Night audit skips reservations carrying a 'reservation_full' charge,
            // so in-house nightly posting never double-charges a pre-billed stay.
            $folio->charges()->create([
                'property_id' => $property->id,
                'charge_date' => $checkIn->toDateString(),
                'description' => "Room — {$rooms[0]['room_type_id']} × {$checkIn->diffInDays($checkOut)} night(s)",
                'category' => 'room',
                'qty' => count($rooms),
                'unit_price' => count($rooms) > 0 ? round($totalRoom / count($rooms), 2) : 0,
                'amount' => $totalRoom,
                'source_type' => 'reservation_full',
                'source_ref' => $reservation->ref,
                'posted_by_user_id' => $payload['created_by_user_id'] ?? null,
            ]);
            // Promo discount posted as a transparent negative line so the
            // folio/invoice shows room (full) − discount + service + tax =
            // grand_total exactly.
            if ($discount > 0) {
                $folio->charges()->create([
                    'property_id' => $property->id,
                    'charge_date' => $checkIn->toDateString(),
                    'description' => 'Discount — promo '.$promo?->code,
                    'category' => 'discount',
                    'qty' => 1,
                    'unit_price' => -$discount,
                    'amount' => -$discount,
                    'source_type' => 'reservation_full',
                    'source_ref' => $reservation->ref,
                    'posted_by_user_id' => $payload['created_by_user_id'] ?? null,
                ]);
            }
            if ($serviceCharge > 0) {
                $folio->charges()->create([
                    'property_id' => $property->id,
                    'charge_date' => $checkIn->toDateString(),
                    'description' => 'Service charge',
                    'category' => 'service_charge',
                    'qty' => 1,
                    'unit_price' => $serviceCharge,
                    'amount' => $serviceCharge,
                    'source_type' => 'reservation_full',
                    'source_ref' => $reservation->ref,
                    'posted_by_user_id' => $payload['created_by_user_id'] ?? null,
                ]);
            }
            if ($pb1 > 0) {
                $folio->charges()->create([
                    'property_id' => $property->id,
                    'charge_date' => $checkIn->toDateString(),
                    'description' => 'PB1',
                    'category' => 'pb1',
                    'qty' => 1,
                    'unit_price' => $pb1,
                    'amount' => $pb1,
                    'tax_code' => 'PB1',
                    'source_type' => 'reservation_full',
                    'source_ref' => $reservation->ref,
                    'posted_by_user_id' => $payload['created_by_user_id'] ?? null,
                ]);
            }
            $folio->recalculate();

            $fresh = $reservation->fresh(['rooms', 'addons', 'folios']);

            // Record promo usage after successful commit of the reservation row.
            if ($promo) {
                app(PromoService::class)->recordUsage($promo);
            }

            $this->audit->record('reservation.created', $fresh, [
                'source' => $fresh->source,
                'grand_total' => $fresh->grand_total,
                'check_in' => $fresh->check_in->toDateString(),
                'check_out' => $fresh->check_out->toDateString(),
            ]);

            $this->roomAssignment->assign($fresh, $guest->preferences ?? []);

            SendBookingConfirmationJob::dispatch($fresh->id)->afterCommit();

            return $fresh;
        });
    }

    /**
     * Check-in readiness checklist. Each check is either 'pass', 'warn' or 'block'.
     * UI renders this; checkIn() enforces blocks server-side.
     */
    public function checkInReadiness(Reservation $r): array
    {
        $checks = [];

        $statusOk = in_array($r->status, ['confirmed', 'tentative'], true);
        $checks[] = [
            'key' => 'status',
            'label' => 'Status reservasi valid',
            'state' => $statusOk ? 'pass' : 'block',
            'detail' => $statusOk ? 'Status: '.$r->status : 'Status '.$r->status.' tidak dapat di-check-in',
        ];

        if ($r->status === 'checked_in') {
            $checks[] = ['key' => 'duplicate', 'label' => 'Belum pernah check-in', 'state' => 'block', 'detail' => 'Reservasi sudah check-in pada '.$r->checked_in_at?->format('d M Y H:i')];
        }

        $rooms = $r->rooms()->with(['room.roomType', 'roomType'])->get();
        $hasAssignment = $rooms->contains(fn ($rr) => $rr->room_id);
        $checks[] = [
            'key' => 'room_assigned',
            'label' => 'Kamar sudah ditentukan',
            'state' => $hasAssignment ? 'pass' : 'block',
            'detail' => $hasAssignment
                ? $rooms->filter(fn ($rr) => $rr->room_id)->map(fn ($rr) => $rr->room->number)->implode(', ')
                : 'Belum ada kamar fisik yang di-assign',
        ];

        $guestReady = $r->primaryGuest && filled($r->primaryGuest->first_name);
        $checks[] = [
            'key' => 'guest',
            'label' => 'Identitas tamu lengkap',
            'state' => $guestReady ? 'pass' : 'block',
            'detail' => $guestReady ? ($r->primaryGuest->full_name ?? $r->primaryGuest->first_name) : 'Data tamu utama belum lengkap',
        ];

        foreach ($rooms as $rr) {
            if (! $rr->room_id || ! $rr->room) {
                continue;
            }

            $room = $rr->room;
            $ooo = $room->fo_status === 'out_of_order';
            $checks[] = [
                'key' => 'room_ooo_'.$room->id,
                'label' => "Kamar {$room->number} bukan Out of Order",
                'state' => $ooo ? 'block' : 'pass',
                'detail' => $ooo ? 'Kamar OOO — pindahkan tamu ke kamar lain' : 'OK',
            ];

            $hkOk = in_array($room->hk_status, ['clean', 'inspected'], true);
            $checks[] = [
                'key' => 'room_hk_'.$room->id,
                'label' => "Kamar {$room->number} sudah dibersihkan",
                'state' => $hkOk ? 'pass' : 'block',
                'detail' => 'Status HK: '.($room->hk_status ?? 'unknown'),
            ];

            $occupiedByOther = ReservationRoom::where('room_id', $room->id)
                ->where('id', '!=', $rr->id)
                ->whereHas('reservation', fn ($q) => $q->whereIn('status', ['checked_in', 'confirmed'])
                    ->where('property_id', $r->property_id))
                ->exists();
            $checks[] = [
                'key' => 'room_conflict_'.$room->id,
                'label' => "Kamar {$room->number} tidak ditempati reservasi lain",
                'state' => $occupiedByOther ? 'block' : 'pass',
                'detail' => $occupiedByOther ? 'Ada reservasi aktif lain di kamar ini' : 'OK',
            ];
        }

        $arrival = Carbon::parse($r->check_in);
        if ($arrival->isFuture() && $arrival->startOfDay()->gt(now()->startOfDay())) {
            $checks[] = [
                'key' => 'arrival_date',
                'label' => 'Tanggal kedatangan',
                'state' => 'warn',
                'detail' => 'Check-in dijadwalkan '.$arrival->format('d M Y').' (masih di masa depan)',
            ];
        }

        $hasPendingPayment = $r->folios()
            ->whereHas('payments', fn ($q) => $q->where('status', FolioPayment::STATUS_PENDING))
            ->exists();
        if ($hasPendingPayment) {
            $checks[] = [
                'key' => 'pending_payment',
                'label' => 'Pembayaran online belum terkonfirmasi',
                'state' => 'warn',
                'detail' => 'Ada transaksi pembayaran gateway yang masih pending',
            ];
        }

        return $checks;
    }

    /**
     * Check-in with readiness enforcement. Throws ReservationValidationException
     * when blocking conditions exist (use checkInReadiness() for UI detail).
     */
    public function checkIn(Reservation $r, ?int $userId = null): Reservation
    {
        return DB::transaction(function () use ($r) {
            $r = Reservation::whereKey($r->id)->lockForUpdate()->firstOrFail();

            if ($r->status === 'checked_in') {
                throw new ReservationValidationException('Reservasi ini sudah di-check-in.');
            }
            if (! in_array($r->status, ['confirmed', 'tentative'], true)) {
                throw new ReservationValidationException("Reservasi berstatus '{$r->status}' tidak dapat di-check-in.");
            }

            $readiness = collect($this->checkInReadiness($r));
            $blocks = $readiness->where('state', 'block')->where('key', '!=', 'duplicate')->values();
            if ($blocks->isNotEmpty()) {
                throw new ReservationValidationException('Check-in belum dapat dilakukan: '.$blocks->pluck('detail')->implode('; '));
            }

            $r->status = 'checked_in';
            $r->checked_in_at = now();
            $r->save();

            foreach ($r->rooms as $rr) {
                if ($rr->room_id) {
                    $rr->update(['status' => 'occupied']);
                    Room::whereKey($rr->room_id)->update(['fo_status' => 'occupied']);
                    // Snapshot minibar stock so checkout auto-charge bills each
                    // consumed item exactly once.
                    try {
                        app(MinibarService::class)->snapshotOnCheckIn($rr->room_id);
                    } catch (\Exception $e) {
                        Log::warning('Minibar snapshot failed on check-in', [
                            'reservation_id' => $r->id,
                            'room_id' => $rr->room_id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $this->audit->record('reservation.checked_in', $r, ['rooms' => $r->rooms->pluck('room_id')->filter()->values()->all()]);

            return $r->fresh(['rooms.room']);
        });
    }

    /**
     * Check-out. Blocks when folio outstanding unless an authorized override
     * (permission + reason) is supplied; both paths are audit-logged.
     */
    public function checkOut(Reservation $r, ?int $userId = null, bool $override = false, ?string $overrideReason = null): Reservation
    {
        return DB::transaction(function () use ($r, $userId, $override, $overrideReason) {
            $r = Reservation::whereKey($r->id)->lockForUpdate()->firstOrFail();

            if ($r->status !== 'checked_in') {
                throw new ReservationValidationException("Reservasi berstatus '{$r->status}' tidak dapat di-check-out.");
            }

            $r->load('folios.charges', 'folios.payments');

            // Post-stay automation — minibar consumption MUST be charged BEFORE
            // settlement so the outstanding balance includes it and the folio
            // is still open when the charge lands.
            foreach ($r->rooms as $rr) {
                if ($rr->room_id) {
                    try {
                        app(MinibarService::class)->autoChargeOnCheckout($r->id, $rr->room_id, $userId ?? auth()->id() ?? 1);
                    } catch (\Exception $e) {
                        Log::warning('Minibar auto-charge failed on checkout', [
                            'reservation_id' => $r->id,
                            'room_id' => $rr->room_id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Recalculate every open folio first so balances reflect posted charges/payments.
            foreach ($r->folios->where('status', 'open') as $folio) {
                $folio->recalculate();
            }

            // Only a POSITIVE balance blocks checkout (guest owes money).
            // A negative/zero balance means fully paid or advance deposit held.
            $outstanding = (float) $r->folios->where('status', 'open')->sum('balance');

            if ($outstanding > 0.009 && ! $override) {
                throw new OutstandingBalanceException($outstanding);
            }
            if ($override) {
                if (! $overrideReason) {
                    throw new ReservationValidationException('Alasan override check-out wajib diisi.');
                }
                $this->audit->record('reservation.force_checkout', $r, [
                    'outstanding_balance' => $outstanding,
                    'reason' => $overrideReason,
                    'override_by_user_id' => $userId,
                ]);
            }

            // Settle folios that are fully paid or hold an advance deposit;
            // leave debtor folios open (they remain receivable).
            foreach ($r->folios as $folio) {
                $folio->recalculate();
                if ($folio->status === 'open' && (float) $folio->balance <= 0.009) {
                    $folio->status = 'closed';
                    $folio->closed_at = now();
                    $folio->save();
                }
            }

            $r->status = 'checked_out';
            $r->checked_out_at = now();
            $r->balance = $outstanding;
            $r->save();

            // Room state machine: occupied → vacant/dirty + HK pickup task.
            $roomIds = [];
            foreach ($r->rooms as $rr) {
                $rr->update(['status' => 'checked_out']);
                if ($rr->room_id) {
                    Room::whereKey($rr->room_id)->update(['fo_status' => 'vacant', 'hk_status' => 'dirty']);
                    $roomIds[] = $rr->room_id;
                }
            }
            if ($roomIds) {
                $this->createHousekeepingPickupTasks($r, $roomIds, $userId);
            }

            $this->audit->record('reservation.checked_out', $r, ['outstanding_balance' => $outstanding]);

            // Post-stay automation
            BuildGuestProfileJob::dispatch($r->primary_guest_id);
            SendPostStayFollowupJob::dispatch($r->id)->delay(now()->addHour());

            return $r->fresh(['rooms.room', 'folios']);
        });
    }

    /**
     * Cancel reservation atomically: validates state, computes policy penalty,
     * releases inventory, writes audit trail.
     */
    public function cancel(Reservation $r, string $reason, ?float $penalty = null, ?int $userId = null): Reservation
    {
        return DB::transaction(function () use ($r, $reason, $penalty, $userId) {
            $r = Reservation::whereKey($r->id)->lockForUpdate()->firstOrFail();

            if (in_array($r->status, ['cancelled', 'checked_out'], true)) {
                throw new ReservationValidationException("Reservasi berstatus '{$r->status}' tidak dapat dibatalkan.");
            }
            if ($r->status === 'checked_in') {
                throw new ReservationValidationException('Tamu sedang menginap. Gunakan check-out, bukan pembatalan.');
            }

            $computedPenalty = $penalty !== null
                ? max(0.0, min((float) $penalty, (float) $r->grand_total))
                : $this->computeCancellationPenalty($r);

            $r->status = 'cancelled';
            $r->cancelled_at = now();
            $r->cancellation_reason = $reason;
            $r->cancellation_penalty = $computedPenalty;
            $r->save();

            // Post penalty charge to the folio so the outstanding reflects the policy.
            $folio = $r->folios()->where('status', 'open')->first();
            if ($folio && $computedPenalty > 0) {
                $folio->charges()->create([
                    'property_id' => $r->property_id,
                    'charge_date' => now()->toDateString(),
                    'description' => 'Cancellation penalty',
                    'category' => 'other',
                    'qty' => 1,
                    'unit_price' => $computedPenalty,
                    'amount' => $computedPenalty,
                    'source_type' => 'cancellation',
                    'source_ref' => $r->ref,
                    'posted_by_user_id' => $userId,
                ]);
                // Void the pre-billed stay charges (room/service/tax) — the stay will not be consumed.
                $folio->charges()
                    ->where('source_type', 'reservation_full')
                    ->where('is_void', false)
                    ->update(['is_void' => true, 'void_reason' => 'Reservation cancelled: '.$reason]);
                $folio->recalculate();
            }

            $this->releaseInventory($r);

            $this->audit->record('reservation.cancelled', $r, [
                'reason' => $reason,
                'penalty' => $computedPenalty,
            ]);

            return $r->fresh(['rooms', 'folios']);
        });
    }

    /**
     * Penalty percentage from the reservation's cancellation policy.
     * Chooses the matching rule (highest days_before <= days remaining).
     */
    public function computeCancellationPenalty(Reservation $r): float
    {
        $daysBefore = now()->startOfDay()->diffInDays(Carbon::parse($r->check_in)->startOfDay(), false);

        $policyId = null;
        foreach ($r->rooms as $rr) {
            if ($rr->rate_plan_id) {
                $plan = RatePlan::find($rr->rate_plan_id);
                $policyId = $plan?->cancellation_policy_id;
                if ($policyId) {
                    break;
                }
            }
        }
        $policy = $policyId
            ? CancellationPolicy::where('property_id', $r->property_id)->find($policyId)
            : CancellationPolicy::where('property_id', $r->property_id)->where('is_default', true)->where('is_active', true)->first();

        if (! $policy || ! is_array($policy->rules) || $policy->rules === []) {
            return 0.0;
        }

        $rules = collect($policy->rules)
            ->map(fn ($rule) => ['days_before' => (int) ($rule['days_before'] ?? 0), 'penalty_pct' => (float) ($rule['penalty_pct'] ?? 0)])
            ->sortByDesc('days_before');

        $matched = $rules->first(fn ($rule) => $daysBefore >= $rule['days_before']) ?? $rules->last();

        return round(((float) $r->grand_total) * $matched['penalty_pct'] / 100, 2);
    }

    public function moveRoom(Reservation $r, int $reservationRoomId, int $newRoomId): Reservation
    {
        return DB::transaction(function () use ($r, $reservationRoomId, $newRoomId) {
            $rr = $r->rooms()->lockForUpdate()->findOrFail($reservationRoomId);
            $newRoom = Room::where('property_id', $r->property_id)
                ->where('is_active', true)
                ->where('fo_status', '!=', 'out_of_order')
                ->lockForUpdate()
                ->findOrFail($newRoomId);

            $conflict = ReservationRoom::where('room_id', $newRoomId)
                ->where('id', '!=', $rr->id)
                ->whereHas('reservation', fn ($q) => $q->whereIn('status', ['checked_in', 'confirmed'])
                    ->where('property_id', $r->property_id))
                ->exists();
            if ($conflict) {
                throw new ReservationValidationException('Kamar tersebut sudah ditempati reservasi aktif lain.');
            }

            $oldRoomId = $rr->room_id;
            $rr->room_id = $newRoomId;
            $rr->save();

            if ($oldRoomId && $r->status === 'checked_in') {
                Room::whereKey($oldRoomId)->update(['fo_status' => 'vacant', 'hk_status' => 'dirty']);
            }
            if ($r->status === 'checked_in') {
                Room::whereKey($newRoomId)->update(['fo_status' => 'occupied']);
            } elseif ($oldRoomId) {
                Room::whereKey($oldRoomId)->update(['fo_status' => 'vacant']);
            }

            $this->audit->record('reservation.room_moved', $r, [
                'reservation_room_id' => $rr->id,
                'from_room_id' => $oldRoomId,
                'to_room_id' => $newRoomId,
            ]);

            return $r->fresh();
        });
    }

    /**
     * Walk-in: create a same-day, immediately checked-in reservation.
     * Uses the property's default rate plan (never a hardcoded ID) and the
     * shared PricingService so walk-in pricing matches every other surface.
     * All selected rooms go through create() so totals & folio charges cover
     * every room (no undercharge).
     *
     * @param  array<int, Room>  $rooms
     */
    public function createWalkIn(Property $property, array $rooms, array $guestData, Carbon $checkOut, ?int $userId = null): Reservation
    {
        $ratePlanId = $this->pricing->defaultRatePlanId($property->id)
            ?? throw new ReservationValidationException('Property belum memiliki rate plan aktif. Tambahkan rate plan terlebih dahulu.');

        $checkIn = now()->startOfDay();

        return $this->createCheckedIn([
            'property_id' => $property->id,
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkOut->toDateString(),
            'rooms' => collect($rooms)->map(fn (Room $room) => [
                'room_type_id' => $room->room_type_id,
                'rate_plan_id' => $ratePlanId,
                'room_id' => $room->id,
                'adults' => $guestData['adults'] ?? 1,
                'children' => $guestData['children'] ?? 0,
            ])->all(),
            'primary_guest' => $guestData,
            'source' => 'walk_in',
            'created_by_user_id' => $userId,
        ]);
    }

    /**
     * Create a reservation that is immediately checked in (walk-in / kiosk).
     * Reuses create() then checks in with an already-assigned, clean room.
     */
    public function createCheckedIn(array $payload): Reservation
    {
        return DB::transaction(function () use ($payload) {
            $reservation = $this->create($payload);

            $reservation = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            $reservation->status = 'checked_in';
            $reservation->checked_in_at = now();
            $reservation->save();

            foreach ($reservation->rooms as $rr) {
                if ($rr->room_id) {
                    $rr->update(['status' => 'occupied']);
                    Room::whereKey($rr->room_id)->update(['fo_status' => 'occupied']);
                    try {
                        app(MinibarService::class)->snapshotOnCheckIn($rr->room_id);
                    } catch (\Exception $e) {
                        Log::warning('Minibar snapshot failed on walk-in check-in', [
                            'reservation_id' => $reservation->id,
                            'room_id' => $rr->room_id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            return $reservation->fresh(['rooms.room', 'folios']);
        });
    }

    protected function assertRoomTypeAndPlanBelongToProperty(int $propertyId, int $roomTypeId, int $ratePlanId): void
    {
        $roomTypeOk = RoomType::where('property_id', $propertyId)->whereKey($roomTypeId)->exists();
        if (! $roomTypeOk) {
            throw new ReservationValidationException('Room type tidak valid untuk property ini.');
        }

        $planOk = RatePlan::where('property_id', $propertyId)->whereKey($ratePlanId)->where('is_active', true)->exists();
        if (! $planOk) {
            throw new ReservationValidationException('Rate plan tidak valid atau tidak aktif untuk property ini.');
        }
    }

    protected function resolveGuest(array $g, int $propertyId): Guest
    {
        $email = $g['email'] ?? null;

        if ($email && $existing = Guest::where('property_id', $propertyId)->where('email', $email)->first()) {
            $existing->update(array_filter($g, fn ($v) => $v !== null && $v !== ''));

            return $existing;
        }

        return Guest::create($g + ['property_id' => $propertyId]);
    }

    /**
     * Verify availability under a row lock and throw if sold out.
     * Fallback policy when no inventory rows exist: physical room capacity
     * minus active physical room assignments for the same room type.
     */
    protected function checkAvailability(int $propertyId, int $roomTypeId, Carbon $in, Carbon $out, int $alreadyInPayload = 0): void
    {
        $cursor = $in->copy();
        while ($cursor->lt($out)) {
            $inv = Inventory::where('property_id', $propertyId)
                ->where('room_type_id', $roomTypeId)
                ->where('date', $cursor->toDateString())
                ->lockForUpdate()
                ->first();

            if ($inv && $inv->available <= 0) {
                throw new RoomSoldOutException($cursor->toDateString());
            }

            if (! $inv && ($this->physicalAvailability($propertyId, $roomTypeId, $cursor->toDateString()) - $alreadyInPayload) <= 0) {
                throw new RoomSoldOutException($cursor->toDateString());
            }

            $cursor->addDay();
        }
    }

    /** Physical capacity: active, non-OOO rooms not already assigned to an active reservation that night. */
    protected function physicalAvailability(int $propertyId, int $roomTypeId, string $date): int
    {
        $total = Room::where('property_id', $propertyId)
            ->where('room_type_id', $roomTypeId)
            ->where('is_active', true)
            ->where('fo_status', '!=', 'out_of_order')
            ->count();

        $assigned = ReservationRoom::where('room_type_id', $roomTypeId)
            ->whereNotNull('room_id')
            ->whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>', $date)
            ->whereHas('reservation', fn ($q) => $q->where('property_id', $propertyId)
                ->whereIn('status', ['confirmed', 'checked_in']))
            ->count();

        return $total - $assigned;
    }

    protected function reserveInventory(int $propertyId, array $rooms, Carbon $in, Carbon $out): void
    {
        foreach ($rooms as $r) {
            $cursor = $in->copy();
            while ($cursor->lt($out)) {
                $inv = Inventory::where('property_id', $propertyId)
                    ->where('room_type_id', $r['room_type_id'])
                    ->where('date', $cursor->toDateString())
                    ->lockForUpdate()
                    ->first();

                if ($inv && $inv->available <= 0) {
                    throw new RoomSoldOutException($cursor->toDateString());
                }

                if (! $inv) {
                    // Initialize from the raw physical capacity of the room type.
                    // (Assignments are tracked via `sold`; the current
                    // reservation's own rooms are counted there.)
                    $capacity = Room::where('property_id', $propertyId)
                        ->where('room_type_id', $r['room_type_id'])
                        ->where('is_active', true)
                        ->where('fo_status', '!=', 'out_of_order')
                        ->count();

                    Inventory::create([
                        'property_id' => $propertyId,
                        'room_type_id' => $r['room_type_id'],
                        'date' => $cursor->toDateString(),
                        'total' => max(1, $capacity),
                    ]);
                }

                Inventory::where('property_id', $propertyId)
                    ->where('room_type_id', $r['room_type_id'])
                    ->where('date', $cursor->toDateString())
                    ->increment('sold');

                $cursor->addDay();
            }
        }
    }

    protected function releaseInventory(Reservation $r): void
    {
        foreach ($r->rooms as $rr) {
            $cursor = Carbon::parse($rr->check_in);
            while ($cursor->lt(Carbon::parse($rr->check_out))) {
                Inventory::where([
                    'property_id' => $r->property_id,
                    'room_type_id' => $rr->room_type_id,
                    'date' => $cursor->toDateString(),
                ])->where('sold', '>', 0)->decrement('sold');
                $cursor->addDay();
            }
        }
    }

    protected function createHousekeepingPickupTasks(Reservation $r, array $roomIds, ?int $userId): void
    {
        if (! class_exists(HkTask::class)) {
            return;
        }

        foreach ($roomIds as $roomId) {
            HkTask::create([
                'property_id' => $r->property_id,
                'room_id' => $roomId,
                'type' => 'cleaning',
                'priority' => 'normal',
                'status' => 'pending',
                'scheduled_date' => now()->toDateString(),
                'notes' => 'Checkout: '.$r->ref,
                'assignee_id' => null,
            ]);
        }
    }

    protected function serviceChargePct(Property $property): float
    {
        $settings = $property->settings;
        if (is_array($settings) && isset($settings['service_charge_pct'])) {
            return max(0.0, min(100.0, (float) $settings['service_charge_pct']));
        }

        return PricingService::DEFAULT_SERVICE_CHARGE_PCT;
    }

    protected function generateRef(Property $property): string
    {
        do {
            $ref = 'HMS-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Reservation::where('ref', $ref)->exists());

        return $ref;
    }
}
