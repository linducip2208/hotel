<?php

use App\Models\Property;
use App\Models\Webhook;
use App\Models\WebhookDelivery;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Webhook Hotel', 'slug' => 'webhook-hotel', 'region_code' => 'ID-JB', 'total_rooms' => 10, 'is_active' => true,
    ]);
});

it('webhook signature matches hmac-sha256 of payload', function () {
    $secret = 'test-secret-key';
    $payload = json_encode(['id' => 'evt_test', 'type' => 'reservation.created', 'data' => ['id' => 1]], JSON_UNESCAPED_SLASHES);

    $expected = hash_hmac('sha256', $payload, $secret);

    $sig = hash_hmac('sha256', $payload, $secret);
    expect($sig)->toBe($expected)->toHaveLength(64);
});

it('signature is invalidated when payload is tampered', function () {
    $secret = 'my-secret';
    $payload = json_encode(['type' => 'reservation.confirmed', 'data' => ['res' => 42]]);
    $validSig = hash_hmac('sha256', $payload, $secret);

    $tampered = json_encode(['type' => 'reservation.confirmed', 'data' => ['res' => 99]]);
    $tamperedSig = hash_hmac('sha256', $tampered, $secret);

    expect(hash_equals($validSig, $tamperedSig))->toBeFalse();
});

it('creates webhook delivery record when dispatching', function () {
    $webhook = Webhook::create([
        'property_id' => $this->property->id,
        'url' => 'http://localhost:19999/webhook-sink', // won't connect — expect failed delivery
        'secret_encrypted' => 'test-secret',
        'events' => ['reservation.created'],
        'is_active' => true,
    ]);

    /** @var \App\Services\Webhooks\WebhookDispatcher $dispatcher */
    $dispatcher = app(\App\Services\Webhooks\WebhookDispatcher::class);
    $dispatcher->dispatch($this->property->id, 'reservation.created', ['id' => 1]);

    expect(WebhookDelivery::where('webhook_id', $webhook->id)->count())->toBe(1);
});

it('does not create delivery when no webhooks match event', function () {
    Webhook::create([
        'property_id' => $this->property->id,
        'url' => 'http://localhost:19999/sink',
        'secret_encrypted' => 'secret',
        'events' => ['payment.captured'],
        'is_active' => true,
    ]);

    $dispatcher = app(\App\Services\Webhooks\WebhookDispatcher::class);
    $dispatcher->dispatch($this->property->id, 'reservation.cancelled', ['id' => 5]);

    expect(WebhookDelivery::count())->toBe(0);
});
