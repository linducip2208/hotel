<?php

namespace App\Services\Accounting;

use App\Models\Folio;
use App\Models\Inventory;
use App\Models\NightAudit;
use App\Models\Property;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class NightAuditService
{
    public function __construct(protected JournalPoster $journal, protected Pb1Calculator $pb1) {}

    public function run(Property $property, ?\DateTimeInterface $date = null, ?int $userId = null): NightAudit
    {
        $auditDate = ($date ?? now())->format('Y-m-d');

        return DB::transaction(function () use ($property, $auditDate, $userId) {
            $audit = NightAudit::where('property_id', $property->id)
                ->whereDate('audit_date', $auditDate)
                ->first()
                ?? NightAudit::create([
                    'property_id'    => $property->id,
                    'audit_date'     => $auditDate,
                    'status'         => 'pending',
                    'run_by_user_id' => $userId,
                ]);

            if ($audit->status === 'completed') {
                return $audit;
            }

            $audit->update(['status' => 'running', 'started_at' => now()]);

            try {
                // 1. No-show: reservasi confirmed yang check_in nya hari ini dan belum check-in
                Reservation::where('property_id', $property->id)
                    ->where('status', 'confirmed')
                    ->whereDate('check_in', $auditDate)
                    ->update(['status' => 'no_show', 'cancelled_at' => now(), 'cancellation_reason' => 'auto: no-show at night audit']);

                // 2. Auto-post room charge ke folio per malam untuk in-house
                $inHouse = Reservation::with('rooms', 'folios')
                    ->where('property_id', $property->id)
                    ->where('status', 'checked_in')
                    ->whereDate('check_in', '<=', $auditDate)
                    ->whereDate('check_out', '>', $auditDate)
                    ->get();

                $totalRoomRev = 0;
                foreach ($inHouse as $res) {
                    $folio = $res->folios->first();
                    if (! $folio) continue;

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
                        $totalRoomRev += $rate;
                    }
                    $folio->recalculate();
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
                ];

                // 4. Post aggregate journal: DR Piutang Tamu / CR Pendapatan Kamar + PB1
                if ($totalRoomRev > 0) {
                    $pb1 = $this->pb1->calculate($property, $totalRoomRev);
                    $service = round($totalRoomRev * 0.10, 2);
                    $this->journal->post(
                        $property->id,
                        "Night audit {$auditDate}",
                        [
                            ['account_code' => '1-1100', 'debit' => $totalRoomRev + $service + $pb1, 'description' => 'Piutang tamu (in-house)'],
                            ['account_code' => '4-1010', 'credit' => $totalRoomRev, 'description' => 'Pendapatan Kamar'],
                            ['account_code' => '4-2000', 'credit' => $service, 'description' => 'Service charge'],
                            ['account_code' => '2-1100', 'credit' => $pb1, 'description' => 'PB1 terhutang', 'tax_code' => 'PB1'],
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
}
