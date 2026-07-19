<?php

use App\Models\ChartOfAccount;
use App\Models\Folio;
use App\Models\Guest;
use App\Models\Inventory;
use App\Models\JournalEntry;
use App\Models\NightAudit;
use App\Models\Property;
use App\Models\RatePlan;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\RoomType;
use App\Services\Accounting\NightAuditService;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Audit Hotel', 'slug' => 'audit-hotel',
        'region_code' => 'ID-JK', 'total_rooms' => 10, 'is_active' => true,
    ]);
    $this->rt = RoomType::create([
        'property_id' => $this->property->id,
        'name' => 'Standard', 'code' => 'STD', 'slug' => 'std-na',
        'base_rate' => 500000, 'max_occupancy' => 2, 'is_active' => true,
    ]);
    $this->rp = RatePlan::create([
        'property_id' => $this->property->id,
        'name' => 'BAR', 'code' => 'BAR', 'is_active' => true,
    ]);
    $this->guest = Guest::create(['first_name' => 'Tamu', 'property_id' => $this->property->id]);

    // COA accounts required by NightAuditService journal posting
    ChartOfAccount::create(['property_id' => $this->property->id, 'code' => '1-1100', 'name' => 'Piutang Tamu',    'type' => 'asset',     'normal_balance' => 'debit',  'is_active' => true]);
    ChartOfAccount::create(['property_id' => $this->property->id, 'code' => '4-1010', 'name' => 'Pendapatan Kamar','type' => 'revenue',   'normal_balance' => 'credit', 'is_active' => true]);
    ChartOfAccount::create(['property_id' => $this->property->id, 'code' => '4-2000', 'name' => 'Service Charge',  'type' => 'revenue',   'normal_balance' => 'credit', 'is_active' => true]);
    ChartOfAccount::create(['property_id' => $this->property->id, 'code' => '2-1100', 'name' => 'PB1 Payable',     'type' => 'liability', 'normal_balance' => 'credit', 'is_active' => true]);

    $this->svc = app(NightAuditService::class);
    $this->auditDate = '2026-09-01';
});

// Helper: create a checked-in reservation with folio for the audit date
function naReservation(array $override = []): Reservation
{
    $p  = test()->property;
    $rt = test()->rt;
    $rp = test()->rp;
    $g  = test()->guest;

    $res = Reservation::create(array_merge([
        'property_id'      => $p->id,
        'ref'              => 'HMS-NA-' . uniqid(),
        'primary_guest_id' => $g->id,
        'status'           => 'checked_in',
        'check_in'         => '2026-09-01',
        'check_out'        => '2026-09-02',
        'nights'           => 1,
        'adults'           => 1,
    ], $override));

    ReservationRoom::create([
        'reservation_id' => $res->id,
        'room_type_id'   => $rt->id,
        'rate_plan_id'   => $rp->id,
        'check_in'       => '2026-09-01',
        'check_out'      => '2026-09-02',
        'subtotal'       => 500000,
    ]);

    Folio::create([
        'property_id'    => $p->id,
        'reservation_id' => $res->id,
        'folio_no'       => 'F-NA-' . $res->id,
        'type'           => 'guest',
        'status'         => 'open',
    ]);

    return $res;
}

it('creates a completed NightAudit record', function () {
    $audit = $this->svc->run($this->property, new DateTime($this->auditDate));

    expect($audit->status)->toBe('completed')
        ->and($audit->audit_date->format('Y-m-d'))->toBe($this->auditDate)
        ->and($audit->property_id)->toBe($this->property->id);
});

it('posts room charge to folio for checked_in reservation', function () {
    $res = naReservation();

    $this->svc->run($this->property, new DateTime($this->auditDate));

    $folio  = $res->folios()->first();
    $charge = $folio->charges()->where('category', 'room')->first();

    expect($charge)->not->toBeNull()
        ->and((float) $charge->amount)->toBe(500000.0)
        ->and($charge->charge_date->format('Y-m-d'))->toBe($this->auditDate);
});

it('posts balanced aggregate journal entry for room revenue', function () {
    naReservation();

    $this->svc->run($this->property, new DateTime($this->auditDate));

    $entry = JournalEntry::where('property_id', $this->property->id)
        ->where('source_type', 'night_audit')
        ->first();

    expect($entry)->not->toBeNull()
        ->and($entry->status)->toBe('posted')
        ->and(round((float) $entry->total_debit, 2))->toBe(round((float) $entry->total_credit, 2))
        ->and($entry->lines()->count())->toBe(4);
});

it('is idempotent — second run returns existing completed audit', function () {
    $first  = $this->svc->run($this->property, new DateTime($this->auditDate));
    $second = $this->svc->run($this->property, new DateTime($this->auditDate));

    expect($first->id)->toBe($second->id)
        ->and(NightAudit::where('property_id', $this->property->id)->count())->toBe(1);
});

it('marks confirmed reservation as no_show when check_in equals audit date', function () {
    $noShow = Reservation::create([
        'property_id'      => $this->property->id,
        'ref'              => 'HMS-NS-' . uniqid(),
        'primary_guest_id' => $this->guest->id,
        'status'           => 'confirmed',
        'check_in'         => $this->auditDate,
        'check_out'        => '2026-09-02',
        'nights'           => 1,
        'adults'           => 1,
    ]);

    $this->svc->run($this->property, new DateTime($this->auditDate));

    expect($noShow->fresh()->status)->toBe('no_show');
});

it('stores occupancy KPI in summary', function () {
    naReservation();
    Inventory::create([
        'property_id' => $this->property->id,
        'room_type_id' => $this->rt->id,
        'date'        => $this->auditDate,
        'total' => 10, 'sold' => 4, 'blocked' => 0, 'out_of_order' => 0,
    ]);

    $audit = $this->svc->run($this->property, new DateTime($this->auditDate));

    expect((int) $audit->summary['rooms_sold'])->toBe(4)
        ->and((float) $audit->summary['occupancy_pct'])->toBe(40.0)
        ->and((float) $audit->summary['room_revenue_gross'])->toBe(500000.0);
});
