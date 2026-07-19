<?php

use App\Models\Folio;
use App\Models\FolioCharge;
use App\Models\Guest;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\RoomType;
use App\Models\SpaTreatment;
use App\Services\Spa\SpaService;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Spa Hotel', 'slug' => 'spa-hotel', 'region_code' => 'ID-BA', 'total_rooms' => 20, 'is_active' => true,
    ]);
    $this->treatment = SpaTreatment::create([
        'property_id' => $this->property->id,
        'name' => 'Balinese Massage', 'code' => 'BAL-60',
        'duration_minutes' => 60,
        'price' => 350000,
        'is_active' => true,
    ]);
    $this->svc = app(SpaService::class);
});

it('books a spa appointment with correct end time', function () {
    $guest = Guest::create(['property_id' => $this->property->id, 'first_name' => 'Sari', 'last_name' => 'W', 'id_number' => '12345']);

    $appt = $this->svc->book([
        'treatment_id' => $this->treatment->id,
        'guest_id' => $guest->id,
        'start_at' => '2026-06-01 10:00:00',
    ]);

    expect($appt->status)->toBe('booked')
        ->and($appt->end_at->format('H:i'))->toBe('11:00')
        ->and((float) $appt->price)->toBe(350000.0);
});

it('completes appointment and posts folio charge', function () {
    $guest = Guest::create(['property_id' => $this->property->id, 'first_name' => 'Budi', 'last_name' => 'S', 'id_number' => '99999']);
    $rt = RoomType::create(['property_id' => $this->property->id, 'name' => 'Deluxe', 'code' => 'DLX', 'slug' => 'deluxe-spa', 'base_rate' => 800000, 'max_occupancy' => 2, 'is_active' => true]);
    $res = Reservation::create([
        'property_id' => $this->property->id, 'primary_guest_id' => $guest->id,
        'room_type_id' => $rt->id, 'ref' => 'HMS-SPA-001',
        'check_in' => '2026-06-01', 'check_out' => '2026-06-03', 'nights' => 2,
        'adults' => 1, 'pax' => 1, 'total_amount' => 1600000, 'status' => 'confirmed',
    ]);
    $folio = Folio::create([
        'property_id' => $this->property->id, 'reservation_id' => $res->id,
        'guest_id' => $guest->id, 'folio_no' => 'F-TEST-SPA',
        'status' => 'open', 'balance' => 0,
    ]);

    $appt = $this->svc->book([
        'treatment_id' => $this->treatment->id,
        'guest_id' => $guest->id,
        'folio_id' => $folio->id,
        'start_at' => '2026-06-01 14:00:00',
    ]);

    $this->svc->complete($appt->fresh());

    expect($appt->fresh()->status)->toBe('completed');
    $charge = FolioCharge::where('folio_id', $folio->id)->where('category', 'spa')->first();
    expect($charge)->not->toBeNull()
        ->and((float) $charge->amount)->toBe(350000.0);
});

it('books appointment without folio and completes silently', function () {
    $appt = $this->svc->book([
        'treatment_id' => $this->treatment->id,
        'start_at' => '2026-06-02 09:00:00',
    ]);

    $this->svc->complete($appt->fresh());

    expect($appt->fresh()->status)->toBe('completed');
    expect(FolioCharge::count())->toBe(0);
});
