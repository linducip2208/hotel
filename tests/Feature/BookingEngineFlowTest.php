<?php

use App\Models\Inventory;
use App\Models\PromoCode;
use App\Models\Property;
use App\Models\Provider;
use App\Models\Rate;
use App\Models\RatePlan;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\Fo\PricingService;
use App\Services\Fo\ReservationService;
use App\Services\Fo\ReservationValidationException;
use App\Services\Fo\RoomSoldOutException;
use App\Services\Payment\PaymentGatewayService;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Book Hotel', 'slug' => 'book-hotel', 'region_code' => 'ID-JK', 'total_rooms' => 10, 'is_active' => true,
    ]);
    $this->roomType = RoomType::create([
        'property_id' => $this->property->id, 'code' => 'STD', 'name' => 'Standard', 'slug' => 'standard',
        'max_occupancy' => 3, 'base_rate' => 500000, 'is_active' => true,
    ]);
    Room::create([
        'property_id' => $this->property->id, 'room_type_id' => $this->roomType->id,
        'number' => 'B-101', 'hk_status' => 'clean', 'fo_status' => 'vacant', 'is_active' => true,
    ]);
    $this->plan = RatePlan::create([
        'property_id' => $this->property->id, 'code' => 'BAR', 'name' => 'Best Available', 'is_active' => true, 'is_refundable' => true,
    ]);
    $this->nrPlan = RatePlan::create([
        'property_id' => $this->property->id, 'code' => 'NRR', 'name' => 'Non-refundable', 'is_active' => true, 'is_refundable' => false,
    ]);

    foreach (range(0, 3) as $d) {
        $date = now()->addDays($d)->toDateString();
        foreach ([$this->plan, $this->nrPlan] as $p) {
            Rate::create([
                'property_id' => $this->property->id, 'room_type_id' => $this->roomType->id,
                'rate_plan_id' => $p->id, 'date' => $date, 'amount' => $p->id === $this->plan->id ? 500000 : 450000,
            ]);
        }
        Inventory::create([
            'property_id' => $this->property->id, 'room_type_id' => $this->roomType->id,
            'date' => $date, 'total' => 2,
        ]);
    }

    $this->pricing = app(PricingService::class);
});

it('prices the same for search, checkout, and committed reservation', function () {
    $checkIn = now()->addDay()->startOfDay();
    $checkOut = now()->addDays(3)->startOfDay();

    $quote = $this->pricing->quote($this->property, $this->roomType->id, $this->plan->id, $checkIn, $checkOut);
    expect($quote['room_total'])->toBe(1000000.0); // 2 nights × 500000
    expect($quote['service_charge'])->toBe(100000.0); // 10%
    expect($quote['grand_total'])->toBe(1210000.0); // 1000000 + 100000 + 110000 PB1

    $r = app(ReservationService::class)->create([
        'property_id' => $this->property->id,
        'check_in' => $checkIn->toDateString(),
        'check_out' => $checkOut->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Book', 'email' => 'book@x.com'],
    ]);

    // The committed reservation MUST use the same numbers as the quote.
    expect((float) $r->total_room)->toBe($quote['room_total']);
    expect((float) $r->grand_total)->toBe($quote['grand_total']);
});

it('falls back to base_rate when a rate plan has no daily rates', function () {
    $emptyPlan = RatePlan::create([
        'property_id' => $this->property->id, 'code' => 'EMPTY', 'name' => 'No Rates', 'is_active' => true,
    ]);
    $checkIn = now()->addDay()->startOfDay();
    $checkOut = now()->addDays(2)->startOfDay();

    $quote = $this->pricing->quote($this->property, $this->roomType->id, $emptyPlan->id, $checkIn, $checkOut);
    expect($quote['room_total'])->toBe(500000.0); // base_rate fallback, not Rp 0
    expect($quote['nightly'][0]['from_fallback'])->toBeTrue();
});

it('lists bookable rate plans with sellable flags', function () {
    $checkIn = now()->addDay()->startOfDay();
    $checkOut = now()->addDays(2)->startOfDay();
    $plans = $this->pricing->ratePlansForStay($this->property, $this->roomType->id, $checkIn, $checkOut, 1);

    expect($plans->count())->toBe(2);
    $bar = $plans->firstWhere('id', $this->plan->id);
    expect($bar['sellable'])->toBeTrue();
    expect($bar['total'])->toBe(500000.0);
});

it('blocks rate plan closed to arrival', function () {
    Rate::where('rate_plan_id', $this->plan->id)->whereDate('date', now()->addDay())->update(['cta' => true]);

    $checkIn = now()->addDay()->startOfDay();
    $checkOut = now()->addDays(2)->startOfDay();
    $plans = $this->pricing->ratePlansForStay($this->property, $this->roomType->id, $checkIn, $checkOut, 1);

    $bar = $plans->firstWhere('id', $this->plan->id);
    expect($bar['sellable'])->toBeFalse();
    expect($bar['block_reason'])->toContain('arrival');
});

it('blocks booking when sold out', function () {
    Inventory::where('room_type_id', $this->roomType->id)->update(['total' => 0]);

    $checkIn = now()->addDay()->startOfDay();
    $checkOut = now()->addDays(2)->startOfDay();

    expect(fn () => app(ReservationService::class)->create([
        'property_id' => $this->property->id,
        'check_in' => $checkIn->toDateString(),
        'check_out' => $checkOut->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Full', 'email' => 'full@x.com'],
    ]))->toThrow(RoomSoldOutException::class);
});

it('offers payment methods only from the configured provider', function () {
    $gateway = app(PaymentGatewayService::class);

    // No provider → no methods.
    expect($gateway->availablePaymentMethods($this->property->id))->toBe([]);

    // Provider configured → default method set.
    $provider = new Provider([
        'property_id' => $this->property->id, 'integration_type' => 'payment',
        'name' => 'Midtrans', 'slug' => 'mid', 'api_format' => 'redirect_flow',
        'is_active' => true, 'is_default' => true,
    ]);
    $provider->setApiKey('key');
    $provider->setSecret('sec');
    $provider->save();

    $methods = collect($gateway->availablePaymentMethods($this->property->id))->pluck('id')->all();
    expect($methods)->toContain('qris');
    expect($methods)->toContain('virtual_account');
    expect($methods)->toContain('credit_card');

    // QRIS-only provider → only qris offered.
    $provider->update(['api_format' => 'qris_flow']);
    $methods = collect($gateway->availablePaymentMethods($this->property->id))->pluck('id')->all();
    expect($methods)->toBe(['qris']);
});

it('booking submit happy path: creates reservation, folio charged full stay, graceful gateway failure', function () {
    $provider = new Provider([
        'property_id' => $this->property->id, 'integration_type' => 'payment',
        'name' => 'Midtrans', 'slug' => 'mid-happy', 'api_format' => 'redirect_flow',
        'is_active' => true, 'is_default' => true,
    ]);
    $provider->setApiKey('key');
    $provider->setSecret('sec');
    $provider->save();

    $response = $this->post(route('booking.submit'), [
        'check_in' => now()->addDay()->toDateString(),
        'check_out' => now()->addDays(2)->toDateString(),
        'room_type_id' => $this->roomType->id,
        'rate_plan_id' => $this->plan->id,
        'adults' => 2,
        'children' => 1,
        'first_name' => 'Happy',
        'last_name' => 'Path',
        'email' => 'happy@x.com',
        'phone' => '081234567890',
        'payment_method' => 'qris',
        'agree_policy' => '1',
    ]);

    $response->assertRedirect();

    $r = Reservation::where('source', 'direct')->whereHas('primaryGuest', fn ($q) => $q->where('email', 'happy@x.com'))->first();
    expect($r)->not->toBeNull();
    expect($r->status)->toBe('confirmed');

    // Folio carries the full-stay charge so the gateway can charge the real amount.
    $folio = $r->folios->first();
    expect((float) $folio->total_charges)->toBe((float) $r->grand_total);
    expect((float) $folio->balance)->toBe((float) $r->grand_total);

    // Gateway without reachable endpoint fails gracefully — no phantom payment recorded.
    expect($folio->payments()->count())->toBe(0);
    expect($r->fresh()->payment_status)->toBe('unpaid');
});

it('promo code applies discount consistently in quote, reservation, and folio', function () {
    $promo = PromoCode::create([
        'property_id' => $this->property->id,
        'code' => 'HEMAT10',
        'discount_type' => 'pct',
        'discount_value' => 10,
        'is_active' => true,
    ]);

    $checkIn = now()->addDay()->startOfDay();
    $checkOut = now()->addDays(2)->startOfDay();

    // Quote: 500000 − 10% = 450000 net → SC 45000 → PB1 49500 → total 544500.
    $quote = $this->pricing->quote($this->property, $this->roomType->id, $this->plan->id, $checkIn, $checkOut, 1, 0, $promo);
    expect($quote['discount'])->toBe(50000.0);
    expect($quote['grand_total'])->toBe(544500.0);

    // Committed reservation must use the same numbers.
    $r = app(ReservationService::class)->create([
        'property_id' => $this->property->id,
        'check_in' => $checkIn->toDateString(),
        'check_out' => $checkOut->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Promo', 'email' => 'promo@x.com'],
        'promo_code' => 'hemat10', // case-insensitive lookup
    ]);

    expect((float) $r->total_room)->toBe(500000.0);
    expect((float) $r->discount_amount)->toBe(50000.0);
    expect($r->promo_code)->toBe('HEMAT10');
    expect((float) $r->grand_total)->toBe(544500.0);

    // Folio carries the discounted full-stay charge.
    $folio = $r->folios->first();
    expect((float) $folio->total_charges)->toBe(544500.0);

    // Usage counted exactly once.
    expect((int) $promo->fresh()->usage_count)->toBe(1);
});

it('rejects invalid promo code', function () {
    expect(fn () => app(ReservationService::class)->create([
        'property_id' => $this->property->id,
        'check_in' => now()->addDay()->toDateString(),
        'check_out' => now()->addDays(2)->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Bad', 'email' => 'bad@x.com'],
        'promo_code' => 'NOTEXIST',
    ]))->toThrow(ReservationValidationException::class);
});

it('booking engine checkout page renders breakdown and payment methods', function () {
    $provider = new Provider([
        'property_id' => $this->property->id, 'integration_type' => 'payment',
        'name' => 'Midtrans', 'slug' => 'mid2', 'api_format' => 'redirect_flow',
        'is_active' => true, 'is_default' => true,
    ]);
    $provider->setApiKey('key');
    $provider->setSecret('sec');
    $provider->save();

    $response = $this->get(route('booking.checkout', [
        'check_in' => now()->addDay()->toDateString(),
        'check_out' => now()->addDays(2)->toDateString(),
        'room_type_id' => $this->roomType->id,
        'rate_plan_id' => $this->plan->id,
        'adults' => 1,
    ]));

    $response->assertOk();
    $response->assertSee('Ringkasan Harga');
    $response->assertSee('Service Charge');
    $response->assertSee('QRIS');
    $response->assertSee('Best Available');
    // No hardcoded plan id — the chosen plan is echoed.
    $response->assertSee('value="'.$this->plan->id.'"', false);
});

it('booking submit rejects forged room type from another property', function () {
    $other = Property::create([
        'name' => 'Other Book', 'slug' => 'other-book', 'region_code' => 'ID-YO', 'total_rooms' => 5, 'is_active' => true,
    ]);
    $otherRt = RoomType::create([
        'property_id' => $other->id, 'code' => 'OTR', 'name' => 'Other', 'slug' => 'other',
        'max_occupancy' => 2, 'base_rate' => 300000, 'is_active' => true,
    ]);

    $response = $this->post(route('booking.submit'), [
        'check_in' => now()->addDay()->toDateString(),
        'check_out' => now()->addDays(2)->toDateString(),
        'room_type_id' => $otherRt->id,
        'rate_plan_id' => $this->plan->id,
        'adults' => 1,
        'first_name' => 'Hack',
        'email' => 'hack@x.com',
        'phone' => '08123',
        'payment_method' => 'qris',
        'agree_policy' => '1',
    ]);

    $response->assertSessionHasErrors('room_type_id');
    expect(Reservation::where('primary_guest_id', '>', 0)->where('source', 'direct')->count())->toBe(0);
});

it('booking submit rejects invalid payment method', function () {
    $provider = new Provider([
        'property_id' => $this->property->id, 'integration_type' => 'payment',
        'name' => 'QRIS Only', 'slug' => 'qris-only', 'api_format' => 'qris_flow',
        'is_active' => true, 'is_default' => true,
    ]);
    $provider->setApiKey('key');
    $provider->setSecret('sec');
    $provider->save();

    $response = $this->post(route('booking.submit'), [
        'check_in' => now()->addDay()->toDateString(),
        'check_out' => now()->addDays(2)->toDateString(),
        'room_type_id' => $this->roomType->id,
        'rate_plan_id' => $this->plan->id,
        'adults' => 1,
        'first_name' => 'Pay',
        'email' => 'pay@x.com',
        'phone' => '08123',
        'payment_method' => 'credit_card', // not offered by qris_flow provider
        'agree_policy' => '1',
    ]);

    $response->assertRedirect(); // falls back to confirmation with payment_error
    $response->assertSessionHas('payment_error');
});
