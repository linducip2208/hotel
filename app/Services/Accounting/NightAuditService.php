<?php

namespace App\Services\Accounting;

use App\Models\CashierShift;
use App\Models\Folio;
use App\Models\FolioPayment;
use App\Models\Inventory;
use App\Models\NightAudit;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\Fo\PricingService;
use Illuminate\Support\Facades\DB;

class NightAuditService
{
    public function __construct(
        protected JournalPoster $journal,
        protected Pb1Calculator $pb1,
        protected PricingService $pricing,
    ) {}

    /**
     * Pre-check: issues that should be resolved before rolling the business date.
     * Informational — the audit can still run, but operators see what needs attention.
     *
     * @return array<int, array{severity: string, label: string, detail: string}>
     */
    public function precheck(Property $property, ?\DateTimeInterface $date = null): array
    {
        $auditDate = ($date ?? now())->format('Y-m-d');
        $issues = [];

        // Open cashier sessions (cash drawers not closed).
        $openShifts = CashierShift::where('property_id', $property->id)->whereNull('closed_at')->with('cashier')->get();
        foreach ($openShifts as $shift) {
            $issues[] = [
                'severity' => 'warning',
                'label' => 'Sesi kasir masih terbuka',
                'detail' => 'Shift #'.$shift->id.' ('.($shift->cashier?->name ?? 'unknown').') belum ditutup.',
            ];
        }

        // In-house reservations without an open folio.
        $noFolio = Reservation::where('property_id', $property->id)
            ->where('status', 'checked_in')
            ->whereDoesntHave('folios', fn ($q) => $q->where('status', 'open'))
            ->count();
        if ($noFolio > 0) {
            $issues[] = [
                'severity' => 'error',
                'label' => 'Reservasi in-house tanpa folio terbuka',
                'detail' => "{$noFolio} reservasi checked-in tidak memiliki folio — charge malam ini tidak akan terposting.",
            ];
        }

        // Unconfirmed gateway payments on open folios.
        $pendingPayments = FolioPayment::where('folio_payments.status', FolioPayment::STATUS_PENDING)
            ->where('is_void', false)
            ->whereHas('folio', fn ($q) => $q->where('property_id', $property->id)->where('status', 'open'))
            ->count();
        if ($pendingPayments > 0) {
            $issues[] = [
                'severity' => 'warning',
                'label' => 'Pembayaran online belum terkonfirmasi',
                'detail' => "{$pendingPayments} transaksi gateway pending pada folio terbuka.",
            ];
        }

        // Arrivals today still in 'tentative' status.
        $tentative = Reservation::where('property_id', $property->id)
            ->where('status', 'tentative')
            ->whereDate('check_in', '<=', $auditDate)
            ->count();
        if ($tentative > 0) {
            $issues[] = [
                'severity' => 'warning',
                'label' => 'Reservasi tentative belum dikonfirmasi',
                'detail' => "{$tentative} reservasi tentative dengan kedatangan hari ini atau sebelumnya.",
            ];
        }

        // Rooms occupied per FO but no active reservation.
        $conflicts = Room::where('property_id', $property->id)
            ->where('fo_status', 'occupied')
            ->whereDoesntHave('reservationRooms', fn ($q) => $q
                ->whereHas('reservation', fn ($r) => $r->where('property_id', $property->id)->where('status', 'checked_in')))
            ->count();
        if ($conflicts > 0) {
            $issues[] = [
                'severity' => 'error',
                'label' => 'Konflik status kamar',
                'detail' => "{$conflicts} kamar berstatus occupied tanpa reservasi aktif — periksa Front Office → Room.",
            ];
        }

        // Integration queue: failed jobs.
        $failedJobs = DB::table('failed_jobs')->count();
        if ($failedJobs > 0) {
            $issues[] = [
                'severity' => 'warning',
                'label' => 'Queue pekerjaan gagal',
                'detail' => "{$failedJobs} job gagal menunggu retry (sync channel, notifikasi, dll).",
            ];
        }

        return $issues;
    }

    public function run(Property $property, ?\DateTimeInterface $date = null, ?int $userId = null): NightAudit
    {
        $auditDate = ($date ?? now())->format('Y-m-d');

        return DB::transaction(function () use ($property, $auditDate, $userId) {
            $audit = NightAudit::where('property_id', $property->id)
                ->whereDate('audit_date', $auditDate)
                ->lockForUpdate()
                ->first()
                ?? NightAudit::create([
                    'property_id' => $property->id,
                    'audit_date' => $auditDate,
                    'status' => 'pending',
                    'run_by_user_id' => $userId,
                ]);

            if ($audit->status === 'completed') {
                return $audit; // idempotent — double-click / re-run is a no-op
            }
            if ($audit->status === 'running') {
                // Previous run crashed mid-transaction; recover by resetting.
                $audit->update(['status' => 'pending']);
            }

            $audit->update(['status' => 'running', 'started_at' => now()]);

            try {
                // 1. No-show: reservasi confirmed yang check_in nya hari ini dan belum check-in
                Reservation::where('property_id', $property->id)
                    ->where('status', 'confirmed')
                    ->whereDate('check_in', $auditDate)
                    ->update(['status' => 'no_show', 'cancelled_at' => now(), 'cancellation_reason' => 'auto: no-show at night audit']);

                // 2. Auto-post room charge ke folio per malam untuk in-house.
                // Skip reservations whose full-stay charges were already posted
                // at creation (source_type=reservation_full) — e.g. pre-paid
                // direct bookings and walk-ins — to prevent double charging.
                $inHouse = Reservation::with('rooms', 'folios')
                    ->where('property_id', $property->id)
                    ->where('status', 'checked_in')
                    ->whereDate('check_in', '<=', $auditDate)
                    ->whereDate('check_out', '>', $auditDate)
                    ->get();

                $totalRoomRev = 0;
                $totalService = 0;
                $totalPb1 = 0;
                foreach ($inHouse as $res) {
                    $folio = $res->folios->first();
                    if (! $folio) {
                        continue;
                    }
                    if ($folio->charges()->where('source_type', 'reservation_full')->where('is_void', false)->exists()) {
                        continue; // pre-billed at reservation creation
                    }

                    $nightRoomRev = 0;
                    foreach ($res->rooms as $rr) {
                        $rate = (float) $rr->subtotal / max(1, $res->nights);
                        $folio->charges()->create([
                            'property_id' => $property->id,
                            'charge_date' => $auditDate,
                            'description' => 'Room charge — '.$rr->roomType?->name,
                            'category' => 'room',
                            'amount' => $rate,
                            'tax_code' => 'PB1',
                            'is_taxable' => true,
                            'tax_amount' => $this->pb1->calculate($property, $rate),
                            'source_type' => 'night_audit',
                            'source_ref' => (string) $audit->id,
                        ]);
                        $nightRoomRev += $rate;
                    }

                    // Service charge as a folio charge so the folio can reach
                    // grand_total exactly (room + service + PB1).
                    $service = round($nightRoomRev * $this->serviceChargePct($property) / 100, 2);
                    if ($service > 0) {
                        $folio->charges()->create([
                            'property_id' => $property->id,
                            'charge_date' => $auditDate,
                            'description' => 'Service charge',
                            'category' => 'service_charge',
                            'qty' => 1,
                            'unit_price' => $service,
                            'amount' => $service,
                            'source_type' => 'night_audit',
                            'source_ref' => (string) $audit->id,
                        ]);
                    }

                    $folio->recalculate();

                    $totalRoomRev += $nightRoomRev;
                    $totalService += $service;
                    $totalPb1 += $this->pb1->calculate($property, $nightRoomRev);
                }

                // 3. Compute KPI summary
                $totalRooms = $property->total_rooms ?: 1;
                $sold = Inventory::where('property_id', $property->id)->whereDate('date', $auditDate)->sum('sold');
                $occupancyPct = round(($sold / max(1, $totalRooms)) * 100, 2);
                $adr = $sold > 0 ? round($totalRoomRev / $sold, 2) : 0;
                $revpar = round($totalRoomRev / max(1, $totalRooms), 2);

                $summary = [
                    'rooms_available' => $totalRooms,
                    'rooms_sold' => $sold,
                    'occupancy_pct' => $occupancyPct,
                    'adr' => $adr,
                    'revpar' => $revpar,
                    'room_revenue_gross' => $totalRoomRev,
                    'service_charge' => $totalService,
                    'pb1' => $totalPb1,
                ];

                // 4. Post aggregate journal: DR Piutang Tamu / CR Pendapatan Kamar + Service + PB1
                if ($totalRoomRev > 0) {
                    $this->journal->post(
                        $property->id,
                        "Night audit {$auditDate}",
                        [
                            ['account_code' => '1-1100', 'debit' => $totalRoomRev + $totalService + $totalPb1, 'description' => 'Piutang tamu (in-house)'],
                            ['account_code' => '4-1010', 'credit' => $totalRoomRev, 'description' => 'Pendapatan Kamar'],
                            ['account_code' => '4-2000', 'credit' => $totalService, 'description' => 'Service charge'],
                            ['account_code' => '2-1100', 'credit' => $totalPb1, 'description' => 'PB1 terhutang', 'tax_code' => 'PB1'],
                        ],
                        'night_audit',
                        $audit->id
                    );
                }

                $audit->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'summary' => $summary,
                ]);
            } catch (\Throwable $e) {
                $audit->update(['status' => 'failed', 'error_log' => $e->getMessage()]);
                throw $e;
            }

            return $audit->fresh();
        });
    }

    protected function serviceChargePct(Property $property): float
    {
        $settings = $property->settings;
        if (is_array($settings) && isset($settings['service_charge_pct'])) {
            return max(0.0, min(100.0, (float) $settings['service_charge_pct']));
        }

        return PricingService::DEFAULT_SERVICE_CHARGE_PCT;
    }
}
