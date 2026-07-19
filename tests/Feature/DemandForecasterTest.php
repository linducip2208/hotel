<?php

use App\Models\Property;
use App\Services\Rms\DemandForecaster;
use Carbon\Carbon;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Forecast Hotel', 'slug' => 'forecast-hotel', 'region_code' => 'ID-BA', 'total_rooms' => 50, 'is_active' => true,
    ]);
    $this->svc = app(DemandForecaster::class);
});

it('returns one entry per date in range', function () {
    $from = Carbon::parse('2026-08-01');
    $to = Carbon::parse('2026-08-03');

    $result = $this->svc->forecast($this->property, $from, $to);

    expect($result)->toHaveCount(3)
        ->and($result[0]['date'])->toBe('2026-08-01')
        ->and($result[2]['date'])->toBe('2026-08-03');
});

it('each forecast entry has required keys', function () {
    $result = $this->svc->forecast($this->property, Carbon::parse('2026-08-10'), Carbon::parse('2026-08-10'));

    $keys = ['date', 'last_year_occupancy_pct', 'current_booked', 'total_rooms', 'forecast_occupancy_pct', 'suggested_rate_modifier_pct'];
    foreach ($keys as $key) {
        expect($result[0])->toHaveKey($key);
    }
});

it('forecast_occupancy_pct is between 0 and 100', function () {
    $result = $this->svc->forecast($this->property, Carbon::parse('2026-09-01'), Carbon::parse('2026-09-07'));

    foreach ($result as $day) {
        expect($day['forecast_occupancy_pct'])->toBeGreaterThanOrEqual(0)
            ->toBeLessThanOrEqual(100);
    }
});

it('suggests 25% rate uplift at 90%+ occupancy', function () {
    $ref = new \ReflectionMethod($this->svc, 'suggestRateModifier');
    $ref->setAccessible(true);

    expect($ref->invoke($this->svc, 95))->toBe(25.0)
        ->and($ref->invoke($this->svc, 85))->toBe(15.0)
        ->and($ref->invoke($this->svc, 25))->toBe(-20.0);
});

it('uses property total_rooms as denominator', function () {
    $result = $this->svc->forecast($this->property, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-01'));
    expect($result[0]['total_rooms'])->toBe(50);
});
