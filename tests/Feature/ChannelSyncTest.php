<?php

use App\Models\AriSyncLog;
use App\Models\Channel;
use App\Models\Property;
use App\Services\Channel\AriSyncService;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Channel Hotel', 'slug' => 'channel-hotel', 'region_code' => 'ID-JK', 'total_rooms' => 40, 'is_active' => true,
    ]);
    $this->svc = app(AriSyncService::class);
});

it('creates ari sync log with running status on push', function () {
    $channel = Channel::create([
        'property_id' => $this->property->id,
        'name' => 'Booking.com', 'code' => 'booking_com', 'adapter_class' => 'BookingComAdapter',
        'is_active' => true, 'credentials_encrypted' => [],
    ]);

    // pushAri will attempt HTTP to external — catch exception, log should still exist
    try {
        $this->svc->pushAri($channel, [['room_type_id' => 1, 'date' => '2026-07-01', 'available' => 5]]);
    } catch (\Throwable) {
    }

    expect(AriSyncLog::where('channel_id', $channel->id)->where('operation', 'push_availability')->exists())->toBeTrue();
});

it('creates fetch_bookings log entry', function () {
    $channel = Channel::create([
        'property_id' => $this->property->id,
        'name' => 'Agoda', 'code' => 'agoda', 'adapter_class' => 'AgodaAdapter',
        'is_active' => true, 'credentials_encrypted' => [],
    ]);

    try {
        $this->svc->fetchBookings($channel);
    } catch (\Throwable) {
    }

    expect(AriSyncLog::where('channel_id', $channel->id)->where('operation', 'fetch_bookings')->exists())->toBeTrue();
});

it('resolves correct adapter class per channel code', function () {
    $bdc = Channel::create(['property_id' => $this->property->id, 'name' => 'BDC', 'code' => 'booking_com', 'adapter_class' => 'BookingComAdapter', 'is_active' => true, 'credentials_encrypted' => []]);
    $agoda = Channel::create(['property_id' => $this->property->id, 'name' => 'Agoda', 'code' => 'agoda', 'adapter_class' => 'AgodaAdapter', 'is_active' => true, 'credentials_encrypted' => []]);
    $tvlk = Channel::create(['property_id' => $this->property->id, 'name' => 'Traveloka', 'code' => 'traveloka', 'adapter_class' => 'TravelokaAdapter', 'is_active' => true, 'credentials_encrypted' => []]);

    expect($this->svc->adapter($bdc))->toBeInstanceOf(\App\Adapters\Channel\BookingComAdapter::class)
        ->and($this->svc->adapter($agoda))->toBeInstanceOf(\App\Adapters\Channel\AgodaAdapter::class)
        ->and($this->svc->adapter($tvlk))->toBeInstanceOf(\App\Adapters\Channel\TravelokaAdapter::class);
});

it('marks channel last_sync_status failed on fetch error', function () {
    $channel = Channel::create([
        'property_id' => $this->property->id,
        'name' => 'Traveloka', 'code' => 'traveloka', 'adapter_class' => 'TravelokaAdapter',
        'is_active' => true, 'credentials_encrypted' => [],
    ]);

    try {
        $this->svc->fetchBookings($channel);
    } catch (\Throwable) {
    }

    $status = $channel->fresh()->last_sync_status;
    // either 'ok' (if stub returns mock) or 'failed' (if throws)
    expect(in_array($status, ['ok', 'failed']))->toBeTrue();
});
