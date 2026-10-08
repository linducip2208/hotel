<?php

use App\Models\MinibarProduct;
use App\Models\MinibarStock;
use App\Models\PosCategory;
use App\Models\PosMenuItem;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOutlet;
use App\Models\Property;
use App\Models\Provider;
use App\Models\RatePlan;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\Fo\FolioService;
use App\Services\Fo\ReservationService;
use App\Services\Hk\MinibarService;
use App\Services\Payment\PaymentGatewayService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->property = Property::create([
        'name' => 'Integrity Hotel', 'slug' => 'integrity-hotel', 'region_code' => 'ID-JK', 'total_rooms' => 5, 'is_active' => true,
    ]);
    $this->roomType = RoomType::create([
        'property_id' => $this->property->id, 'code' => 'STD', 'name' => 'Standard', 'slug' => 'std-int',
        'max_occupancy' => 2, 'base_rate' => 500000, 'is_active' => true,
    ]);
    $this->plan = RatePlan::create([
        'property_id' => $this->property->id, 'code' => 'BAR', 'name' => 'BAR', 'is_active' => true,
    ]);
    $this->roomA = Room::create([
        'property_id' => $this->property->id, 'room_type_id' => $this->roomType->id,
        'number' => 'I-101', 'hk_status' => 'clean', 'fo_status' => 'vacant', 'is_active' => true,
    ]);
    $this->roomB = Room::create([
        'property_id' => $this->property->id, 'room_type_id' => $this->roomType->id,
        'number' => 'I-102', 'hk_status' => 'clean', 'fo_status' => 'vacant', 'is_active' => true,
    ]);
    $this->user = User::create([
        'name' => 'Integrity Admin', 'email' => 'integrity@x.com',
        'password' => bcrypt('password-password'), 'property_id' => $this->property->id,
    ]);
    $this->user->assignRole('super_owner');
    $this->actingAs($this->user);
    $this->svc = app(ReservationService::class);
});

// ---------------------------------------------------------------------------
// WALK-IN MULTI-ROOM — totals & folio must cover EVERY selected room
// ---------------------------------------------------------------------------

it('walk-in with 2 rooms charges both rooms exactly once', function () {
    $reservation = $this->svc->createWalkIn(
        $this->property,
        [$this->roomA, $this->roomB],
        ['first_name' => 'Dua', 'email' => 'dua@x.com', 'adults' => 2],
        now()->addDay()->endOfDay(),
        $this->user->id,
    );

    // Both rooms physically assigned — no auto-assign swap.
    expect($reservation->rooms()->count())->toBe(2);
    expect($reservation->rooms()->whereNotNull('room_id')->count())->toBe(2);

    // total_room = 2 × base_rate (1 night), not just the first room.
    expect((float) $reservation->total_room)->toBe(1000000.0);

    // Folio carries full-stay charges for BOTH rooms: room + service + PB1.
    $folio = $reservation->folios->first();
    expect((float) $folio->total_charges)->toBe((float) $reservation->grand_total);
    expect((float) $folio->total_charges)->toBe(1210000.0); // 1,000,000 + 100,000 SC + 110,000 PB1

    // Both rooms occupied.
    expect($this->roomA->fresh()->fo_status)->toBe('occupied');
    expect($this->roomB->fresh()->fo_status)->toBe('occupied');
});

it('walk-in advance payment goes through FolioService and reduces folio balance', function () {
    $reservation = $this->svc->createWalkIn(
        $this->property,
        [$this->roomA],
        ['first_name' => 'Depo', 'email' => 'depo@x.com'],
        now()->addDay()->endOfDay(),
        $this->user->id,
    );

    $folio = $reservation->folios->first();
    app(FolioService::class)->postPayment($folio, [
        'amount' => 200000,
        'method' => 'cash',
        'reference_no' => 'WALKIN-'.$reservation->ref,
        'cashier_id' => $this->user->id,
    ]);

    $folio = $folio->fresh();
    expect((float) $folio->total_payments)->toBe(200000.0);
    expect((float) $folio->balance)->toBe((float) $reservation->grand_total - 200000.0);
});

// ---------------------------------------------------------------------------
// MINIBAR — every item charged exactly once (no ×2, no leak)
// ---------------------------------------------------------------------------

function minibarSetup($property, $room): array
{
    $cola = MinibarProduct::create([
        'property_id' => $property->id, 'name' => 'Cola', 'sku' => 'MB-COLA',
        'selling_price' => 30000, 'is_active' => true,
    ]);
    $stock = MinibarStock::create([
        'property_id' => $property->id, 'room_id' => $room->id,
        'minibar_product_id' => $cola->id, 'initial_qty' => 4, 'current_qty' => 4,
    ]);

    return [$cola, $stock];
}

it('minibar: consumption recorded during stay is NOT charged again at checkout', function () {
    [$cola, $stock] = minibarSetup($this->property, $this->roomA);

    $reservation = $this->svc->create([
        'property_id' => $this->property->id,
        'check_in' => now()->toDateString(),
        'check_out' => now()->addDay()->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Mbi', 'email' => 'mbi@x.com'],
    ]);
    $reservation->rooms->first()->update(['room_id' => $this->roomA->id]);
    $this->svc->checkIn($reservation, $this->user->id);

    $minibar = app(MinibarService::class);

    // Guest takes 2 colas; housekeeping records them → 1 charge of 2 units.
    $minibar->recordConsumption($this->roomA->id, $reservation->id, [
        ['product_id' => $cola->id, 'qty' => 2],
    ], $this->user->id);

    $folio = $reservation->folios->first();
    $minibarCharges = fn () => $folio->charges()->where('category', 'minibar')->where('is_void', false)->get();

    expect($minibarCharges()->sum('amount'))->toBe(60000.0);

    // Checkout auto-charge must NOT re-charge the recorded 2 colas.
    $minibar->autoChargeOnCheckout($reservation->id, $this->roomA->id, $this->user->id);

    expect($minibarCharges()->sum('amount'))->toBe(60000.0); // unchanged — no double charge
});

it('minibar: stock is not over-decremented by checkout auto-charge', function () {
    [$cola, $stock] = minibarSetup($this->property, $this->roomA);

    $reservation = $this->svc->create([
        'property_id' => $this->property->id,
        'check_in' => now()->toDateString(),
        'check_out' => now()->addDay()->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Stk', 'email' => 'stk@x.com'],
    ]);
    $reservation->rooms->first()->update(['room_id' => $this->roomA->id]);
    $this->svc->checkIn($reservation, $this->user->id);

    $minibar = app(MinibarService::class);
    $minibar->recordConsumption($this->roomA->id, $reservation->id, [
        ['product_id' => $cola->id, 'qty' => 2],
    ], $this->user->id);

    // current after record = 4 − 2 = 2. Auto-charge (no new shortfall) must
    // leave stock untouched.
    $minibar->autoChargeOnCheckout($reservation->id, $this->roomA->id, $this->user->id);

    expect((int) $stock->fresh()->current_qty)->toBe(2);
});

it('minibar: auto-charge called twice does not double-charge', function () {
    [$cola] = minibarSetup($this->property, $this->roomA);

    $reservation = $this->svc->create([
        'property_id' => $this->property->id,
        'check_in' => now()->toDateString(),
        'check_out' => now()->addDay()->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Dbl', 'email' => 'dbl@x.com'],
    ]);
    $reservation->rooms->first()->update(['room_id' => $this->roomA->id]);
    $this->svc->checkIn($reservation, $this->user->id);

    // Simulate a physical count showing 1 cola missing (never recorded).
    MinibarStock::where('room_id', $this->roomA->id)->update(['current_qty' => 3]);

    $minibar = app(MinibarService::class);
    $minibar->autoChargeOnCheckout($reservation->id, $this->roomA->id, $this->user->id);
    $minibar->autoChargeOnCheckout($reservation->id, $this->roomA->id, $this->user->id); // retry

    $folio = $reservation->folios->first();
    expect((float) $folio->charges()->where('category', 'minibar')->where('is_void', false)->sum('amount'))
        ->toBe(30000.0); // 1 cola, once
});

// ---------------------------------------------------------------------------
// POS — settle is idempotent; paid_total never exceeds grand_total
// ---------------------------------------------------------------------------

function createPosOrder($property, $outlet, $menuPrice): PosOrder
{
    $category = PosCategory::create([
        'outlet_id' => $outlet->id, 'name' => 'Drinks', 'is_active' => true,
    ]);
    $menu = PosMenuItem::create([
        'outlet_id' => $outlet->id, 'category_id' => $category->id, 'code' => 'M-'.uniqid(),
        'name' => 'Es Teh', 'price' => $menuPrice, 'is_available' => true,
    ]);

    $order = PosOrder::create([
        'property_id' => $property->id,
        'outlet_id' => $outlet->id,
        'order_no' => 'ORD-TEST-'.uniqid(),
        'status' => 'open',
        'subtotal' => $menuPrice,
        'service_charge' => round($menuPrice * 0.1, 2),
        'tax_total' => round($menuPrice * 1.1 * 0.11, 2),
        'grand_total' => $menuPrice + round($menuPrice * 0.1, 2) + round($menuPrice * 1.1 * 0.11, 2),
    ]);
    PosOrderItem::create([
        'order_id' => $order->id, 'menu_item_id' => $menu->id, 'name' => $menu->name,
        'unit_price' => $menuPrice, 'qty' => 1, 'subtotal' => $menuPrice,
    ]);

    return $order;
}

it('POS settle cannot be executed twice (no double folio charge)', function () {
    $outlet = PosOutlet::create([
        'property_id' => $this->property->id, 'name' => 'Resto', 'code' => 'R1',
        'type' => 'restaurant', 'is_active' => true,
    ]);
    $order = createPosOrder($this->property, $outlet, 100000);

    $reservation = $this->svc->create([
        'property_id' => $this->property->id,
        'check_in' => now()->toDateString(),
        'check_out' => now()->addDay()->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Pos', 'email' => 'pos@x.com'],
    ]);
    $reservation->rooms->first()->update(['room_id' => $this->roomA->id]);
    $this->svc->checkIn($reservation, $this->user->id);
    $folio = $reservation->folios->first();
    $balanceBefore = (float) $folio->fresh()->balance;

    $response = $this->postJson(route('panel.pos.orders.settle', $order->id), [
        'method' => 'charge_to_room',
        'folio_id' => $folio->id,
    ]);
    $response->assertOk();

    // Second attempt must be rejected.
    $response2 = $this->postJson(route('panel.pos.orders.settle', $order->id), [
        'method' => 'charge_to_room',
        'folio_id' => $folio->id,
    ]);
    $response2->assertStatus(422);

    // Only ONE fnb charge posted, NET amount (PPN via tax engine, not pre-taxed).
    $fnbCharges = $folio->fresh()->charges()->where('category', 'fnb')->where('is_void', false)->get();
    expect($fnbCharges->count())->toBe(1);
    // Net = subtotal + service (PPN dihitung tax engine, bukan dobel).
    expect((float) $fnbCharges->first()->amount)->toBe(110000.0);
    expect((float) $fnbCharges->first()->tax_amount)->toBe(12100.0); // 11% PPN on net — once
    expect((float) $folio->fresh()->balance)->toBe($balanceBefore + 122100.0); // net + PPN
});

it('POS cash settle never records paid_total above grand_total', function () {
    $outlet = PosOutlet::create([
        'property_id' => $this->property->id, 'name' => 'Bar', 'code' => 'B1',
        'type' => 'bar', 'is_active' => true,
    ]);
    $order = createPosOrder($this->property, $outlet, 50000); // grand_total = 56050

    $response = $this->postJson(route('panel.pos.orders.settle', $order->id), [
        'method' => 'cash',
        'amount' => 100000, // guest hands over 100k → change returned
    ]);
    $response->assertOk();

    expect((float) $order->fresh()->paid_total)->toBe((float) $order->grand_total);
    expect($order->fresh()->status)->toBe('settled');
});

// ---------------------------------------------------------------------------
// GATEWAY — pending transaction reuse (double-submit guard)
// ---------------------------------------------------------------------------

it('gateway createTransaction reuses existing pending transaction instead of duplicating', function () {
    $provider = new Provider([
        'property_id' => $this->property->id, 'integration_type' => 'payment',
        'name' => 'Midtrans', 'slug' => 'mid-int', 'api_format' => 'redirect_flow',
        'is_active' => true, 'is_default' => true,
    ]);
    $provider->setApiKey('key');
    $provider->setSecret('sec');
    $provider->save();

    $reservation = $this->svc->create([
        'property_id' => $this->property->id,
        'check_in' => now()->addDay()->toDateString(),
        'check_out' => now()->addDays(2)->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Pnd', 'email' => 'pnd@x.com'],
    ]);

    // Simulate a pending transaction created by a previous attempt.
    $folio = $reservation->folios->first();
    $existing = $folio->payments()->create([
        'property_id' => $this->property->id,
        'payment_date' => now()->toDateString(),
        'amount' => $reservation->grand_total,
        'method' => 'qris',
        'status' => 'pending',
        'reference_no' => 'PAY-EXISTING-1',
        'gateway_payload' => ['redirect_url' => 'https://pay.example/txn'],
    ]);

    $gateway = app(PaymentGatewayService::class);
    $result = $gateway->createTransaction($reservation, 'virtual_account');

    // Reuses the pending txn — no second payment row.
    expect($result['ok'])->toBeTrue();
    expect($result['transaction_id'])->toBe('PAY-EXISTING-1');
    expect($folio->payments()->count())->toBe(1);
    expect($folio->payments()->first()->id)->toBe($existing->id);
});

// ---------------------------------------------------------------------------
// FOLIO — guards against closed-folio edits and overpayment
// ---------------------------------------------------------------------------

it('folio rejects charge and payment after settlement', function () {
    $reservation = $this->svc->create([
        'property_id' => $this->property->id,
        'check_in' => now()->toDateString(),
        'check_out' => now()->addDay()->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Cls', 'email' => 'cls@x.com'],
    ]);
    $folio = $reservation->folios->first();
    $folio->update(['status' => 'closed', 'closed_at' => now()]);

    $this->post(route('panel.fo.folios.charges', $folio->id), [
        'description' => 'Late charge', 'category' => 'minibar', 'amount' => 50000,
    ])->assertSessionHasErrors('charge');

    $this->post(route('panel.fo.folios.payments', $folio->id), [
        'amount' => 50000, 'method' => 'cash',
    ])->assertSessionHasErrors('payment');
});

it('folio payment cannot exceed outstanding balance (overpayment guard)', function () {
    $reservation = $this->svc->create([
        'property_id' => $this->property->id,
        'check_in' => now()->toDateString(),
        'check_out' => now()->addDay()->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Ovr', 'email' => 'ovr@x.com'],
    ]);
    $folio = $reservation->folios->first(); // balance = grand_total = 660000? no: 605000
    $outstanding = (float) $folio->balance;

    $this->post(route('panel.fo.folios.payments', $folio->id), [
        'amount' => $outstanding + 1000000,
        'method' => 'cash',
    ])->assertSessionHasErrors('payment');

    expect($folio->payments()->count())->toBe(0);
});

it('folio payment records at most the outstanding (cash change not double-counted)', function () {
    $reservation = $this->svc->create([
        'property_id' => $this->property->id,
        'check_in' => now()->toDateString(),
        'check_out' => now()->addDay()->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Chg', 'email' => 'chg@x.com'],
    ]);
    $folio = $reservation->folios->first();
    $outstanding = (float) $folio->balance;

    $this->post(route('panel.fo.folios.payments', $folio->id), [
        'amount' => $outstanding, // paid exactly
        'method' => 'cash',
    ])->assertSessionHasNoErrors();

    $folio = $folio->fresh();
    expect((float) $folio->balance)->toBe(0.0);
    expect($folio->payments()->count())->toBe(1);
});

it('discount cannot exceed outstanding balance', function () {
    $reservation = $this->svc->create([
        'property_id' => $this->property->id,
        'check_in' => now()->toDateString(),
        'check_out' => now()->addDay()->toDateString(),
        'rooms' => [['room_type_id' => $this->roomType->id, 'rate_plan_id' => $this->plan->id, 'adults' => 1]],
        'primary_guest' => ['first_name' => 'Dsc', 'email' => 'dsc@x.com'],
    ]);
    $folio = $reservation->folios->first();

    $this->post(route('panel.fo.folios.discount', $folio->id), [
        'amount' => 99999999, 'reason' => 'test',
    ])->assertSessionHasErrors('discount');
});
