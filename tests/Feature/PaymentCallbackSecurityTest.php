<?php

use App\Models\Folio;
use App\Models\FolioPayment;
use App\Models\Property;
use App\Models\Provider;
use App\Models\RatePlan;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\Fo\ReservationService;
use App\Services\Payment\PaymentGatewayService;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Pay Hotel', 'slug' => 'pay-hotel', 'region_code' => 'ID-JK', 'total_rooms' => 10, 'is_active' => true,
    ]);
    $this->roomType = RoomType::create([
        'property_id' => $this->property->id, 'code' => 'STD', 'name' => 'Standard', 'slug' => 'standard',
        'max_occupancy' => 2, 'base_rate' => 500000, 'is_active' => true,
    ]);
    // Physical room so capacity-based availability fallback allows booking.
    Room::create([
        'property_id' => $this->property->id, 'room_type_id' => $this->roomType->id,
        'number' => 'P-101', 'hk_status' => 'clean', 'fo_status' => 'vacant', 'is_active' => true,
    ]);

    // Midtrans-style payment provider with known secret.
    $this->secret = 'SB-Mid-server-test-secret';
    $this->provider = new Provider([
        'property_id' => $this->property->id,
        'integration_type' => 'payment',
        'name' => 'Midtrans',
        'slug' => 'midtrans-test',
        'api_format' => 'redirect_flow',
        'is_active' => true,
        'is_default' => true,
    ]);
    $this->provider->setApiKey('SB-Mid-client-key');
    $this->provider->setSecret($this->secret);
    $this->provider->save();
});

function createPaidReservation($property, $roomType, ReservationService $svc): Reservation
{
    return $svc->create([
        'property_id' => $property->id,
        'check_in' => now()->addDay()->toDateString(),
        'check_out' => now()->addDays(2)->toDateString(),
        'rooms' => [['room_type_id' => $roomType->id, 'rate_plan_id' => RatePlan::create([
            'property_id' => $property->id, 'code' => 'BAR-'.uniqid(), 'name' => 'BAR', 'is_active' => true,
        ])->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Pay', 'email' => 'pay'.uniqid().'@x.com'],
    ]);
}

it('rejects payment callback with invalid signature and does not update payment', function () {
    $svc = app(ReservationService::class);
    $r = createPaidReservation($this->property, $this->roomType, $svc);
    $gateway = app(PaymentGatewayService::class);

    // Create a pending gateway transaction (bypasses network charge by not using createTransaction).
    $folio = $r->folios->first();
    $payment = $folio->payments()->create([
        'property_id' => $this->property->id,
        'payment_date' => now()->toDateString(),
        'amount' => $r->grand_total,
        'method' => 'qris',
        'status' => FolioPayment::STATUS_PENDING,
        'provider_id' => $this->provider->id,
        'reference_no' => 'PAY-TEST-1',
    ]);

    $payload = [
        'order_id' => 'PAY-TEST-1',
        'status_code' => '200',
        'gross_amount' => (string) (int) $r->grand_total,
        'transaction_status' => 'settlement',
        'signature_key' => str_repeat('a', 128), // invalid
    ];

    $response = $this->postJson(route('booking.payment-callback', $r->ref), $payload);

    $response->assertStatus(403);
    expect($payment->fresh()->status)->toBe(FolioPayment::STATUS_PENDING);
    expect($r->fresh()->payment_status)->toBe('unpaid');
});

it('processes valid signed callback and marks payment paid', function () {
    $svc = app(ReservationService::class);
    $r = createPaidReservation($this->property, $this->roomType, $svc);
    $gateway = app(PaymentGatewayService::class);

    $folio = $r->folios->first();
    $amount = (int) $r->grand_total;
    $folio->payments()->create([
        'property_id' => $this->property->id,
        'payment_date' => now()->toDateString(),
        'amount' => $amount,
        'method' => 'qris',
        'status' => FolioPayment::STATUS_PENDING,
        'provider_id' => $this->provider->id,
        'reference_no' => 'PAY-TEST-2',
    ]);

    $payload = [
        'order_id' => 'PAY-TEST-2',
        'status_code' => '200',
        'gross_amount' => (string) $amount,
        'transaction_status' => 'settlement',
    ];
    $payload['signature_key'] = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$this->secret);

    $response = $this->postJson(route('booking.payment-callback', $r->ref), $payload);

    $response->assertStatus(200)->assertJson(['ok' => true, 'verified' => true]);
    $folio->refresh();
    expect((float) $folio->total_payments)->toBe((float) $amount);
    expect($folio->payments()->where('reference_no', 'PAY-TEST-2')->first()->status)->toBe(FolioPayment::STATUS_PAID);
    expect($r->fresh()->payment_status)->toBe('paid');
});

it('is idempotent — duplicate paid callbacks do not change state or double-count balance', function () {
    $svc = app(ReservationService::class);
    $r = createPaidReservation($this->property, $this->roomType, $svc);

    $folio = $r->folios->first();
    $amount = (int) $r->grand_total;
    $folio->payments()->create([
        'property_id' => $this->property->id,
        'payment_date' => now()->toDateString(),
        'amount' => $amount,
        'method' => 'qris',
        'status' => FolioPayment::STATUS_PENDING,
        'provider_id' => $this->provider->id,
        'reference_no' => 'PAY-TEST-3',
    ]);

    $payload = [
        'order_id' => 'PAY-TEST-3',
        'status_code' => '200',
        'gross_amount' => (string) $amount,
        'transaction_status' => 'settlement',
    ];
    $payload['signature_key'] = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$this->secret);

    $this->postJson(route('booking.payment-callback', $r->ref), $payload);
    $this->postJson(route('booking.payment-callback', $r->ref), $payload);

    $folio->refresh();
    // Payment recorded once, counted once — no double posting.
    expect($folio->payments()->count())->toBe(1);
    expect((float) $folio->total_payments)->toBe((float) $amount);
});

it('rejects callback with mismatched amount', function () {
    $svc = app(ReservationService::class);
    $r = createPaidReservation($this->property, $this->roomType, $svc);

    $folio = $r->folios->first();
    $folio->payments()->create([
        'property_id' => $this->property->id,
        'payment_date' => now()->toDateString(),
        'amount' => 500000,
        'method' => 'qris',
        'status' => FolioPayment::STATUS_PENDING,
        'provider_id' => $this->provider->id,
        'reference_no' => 'PAY-TEST-4',
    ]);

    $payload = [
        'order_id' => 'PAY-TEST-4',
        'status_code' => '200',
        'gross_amount' => '1', // mismatch
        'transaction_status' => 'settlement',
    ];
    $payload['signature_key'] = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$this->secret);

    $this->postJson(route('booking.payment-callback', $r->ref), $payload);

    expect($folio->payments()->where('reference_no', 'PAY-TEST-4')->first()->status)->toBe(FolioPayment::STATUS_PENDING);
});

it('does not let a failed callback demote a paid payment', function () {
    $svc = app(ReservationService::class);
    $r = createPaidReservation($this->property, $this->roomType, $svc);

    $folio = $r->folios->first();
    $payment = $folio->payments()->create([
        'property_id' => $this->property->id,
        'payment_date' => now()->toDateString(),
        'amount' => 500000,
        'method' => 'qris',
        'status' => FolioPayment::STATUS_PAID,
        'provider_id' => $this->provider->id,
        'reference_no' => 'PAY-TEST-5',
    ]);

    app(PaymentGatewayService::class)->handleCallback($r->ref, 'expire', ['order_id' => 'PAY-TEST-5']);

    expect($payment->fresh()->status)->toBe(FolioPayment::STATUS_PAID);
});

it('pending gateway payment does not reduce folio balance until confirmed', function () {
    $svc = app(ReservationService::class);
    $r = createPaidReservation($this->property, $this->roomType, $svc);

    $folio = $r->folios->first();
    $folio->payments()->create([
        'property_id' => $this->property->id,
        'payment_date' => now()->toDateString(),
        'amount' => 100000,
        'method' => 'qris',
        'status' => FolioPayment::STATUS_PENDING,
        'provider_id' => $this->provider->id,
        'reference_no' => 'PAY-TEST-6',
    ]);
    $folio->recalculate();

    // Pending gateway payments must NOT count toward folio payments yet.
    expect((float) $folio->total_payments)->toBe(0.0);

    $folio->payments()->where('reference_no', 'PAY-TEST-6')->first()->markPaid();
    $folio->recalculate();

    expect((float) $folio->total_payments)->toBe(100000.0);
});
