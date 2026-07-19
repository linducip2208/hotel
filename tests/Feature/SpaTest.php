<?php

use App\Models\Guest;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\RoomType;
use App\Models\SpaAppointment;
use App\Models\SpaCabin;
use App\Models\SpaTherapist;
use App\Models\SpaTreatment;
use App\Services\Spa\SpaService;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Spa Hotel', 'slug' => 'spa-test', 'region_code' => 'ID-BA', 'total_rooms' => 20, 'is_active' => true,
    ]);
    $this->treatment = SpaTreatment::create([
        'property_id' => $this->property->id,
        'name' => 'Traditional Balinese Massage', 'code' => 'BAL-90',
        'duration_minutes' => 90, 'price' => 550000, 'is_active' => true,
    ]);
    $this->therapist = SpaTherapist::create([
        'property_id' => $this->property->id,
        'name' => 'Ayuni', 'gender' => 'F', 'is_active' => true,
    ]);
    $this->cabin = SpaCabin::create([
        'property_id' => $this->property->id,
        'name' => 'Lotus Room', 'type' => 'single', 'is_active' => true,
    ]);
    $this->svc = app(SpaService::class);
});

it('lists treatments by property', function () {
    SpaTreatment::create([
        'property_id' => $this->property->id, 'name' => 'Foot Reflexology', 'code' => 'FOOT-45',
        'duration_minutes' => 45, 'price' => 200000, 'is_active' => true,
    ]);

    $treatments = SpaTreatment::where('property_id', $this->property->id)->get();
    expect($treatments)->toHaveCount(2);
});

it('creates treatment with required fields', function () {
    $t = SpaTreatment::create([
        'property_id' => $this->property->id,
        'name' => 'Aromatherapy', 'code' => 'ARO-60',
        'duration_minutes' => 60, 'price' => 380000, 'is_active' => true,
    ]);

    expect($t->code)->toBe('ARO-60')
        ->and((float) $t->price)->toBe(380000.0)
        ->and($t->is_active)->toBeTrue();
});

it('books appointment with therapist and cabin', function () {
    $guest = Guest::create([
        'property_id' => $this->property->id, 'first_name' => 'Maya', 'last_name' => 'Putri', 'id_number' => '99001',
    ]);

    $appt = $this->svc->book([
        'treatment_id' => $this->treatment->id,
        'therapist_id' => $this->therapist->id,
        'cabin_id' => $this->cabin->id,
        'guest_id' => $guest->id,
        'start_at' => '2026-07-15 09:00:00',
    ]);

    expect($appt->status)->toBe('booked')
        ->and($appt->end_at->format('H:i'))->toBe('10:30')
        ->and((float) $appt->price)->toBe(550000.0)
        ->and($appt->therapist_id)->toBe($this->therapist->id)
        ->and($appt->cabin_id)->toBe($this->cabin->id);
});

it('completes appointment successfully', function () {
    $guest = Guest::create([
        'property_id' => $this->property->id, 'first_name' => 'Doni', 'last_name' => 'Hartono', 'id_number' => '88002',
    ]);

    $appt = $this->svc->book([
        'treatment_id' => $this->treatment->id,
        'guest_id' => $guest->id,
        'start_at' => '2026-07-15 14:00:00',
    ]);

    $this->svc->complete($appt->fresh());
    expect($appt->fresh()->status)->toBe('completed');
});

it('cancels appointment', function () {
    $appt = $this->svc->book([
        'treatment_id' => $this->treatment->id,
        'start_at' => '2026-07-16 11:00:00',
    ]);

    $appt->update(['status' => 'cancelled']);
    expect($appt->fresh()->status)->toBe('cancelled');
});

it('deletes appointment', function () {
    $appt = $this->svc->book([
        'treatment_id' => $this->treatment->id,
        'start_at' => '2026-07-17 08:00:00',
    ]);

    $appt->delete();
    expect(SpaAppointment::find($appt->id))->toBeNull();
});

it('creates therapist', function () {
    $t = SpaTherapist::create([
        'property_id' => $this->property->id, 'name' => 'Komang', 'gender' => 'M', 'is_active' => true,
    ]);

    expect($t->name)->toBe('Komang')
        ->and($t->gender)->toBe('M');
});

it('creates cabin', function () {
    $c = SpaCabin::create([
        'property_id' => $this->property->id, 'name' => 'Jasmine Room', 'type' => 'couple', 'is_active' => true,
    ]);

    expect($c->type)->toBe('couple')
        ->and($c->is_active)->toBeTrue();
});
