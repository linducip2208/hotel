<?php

use App\Services\Accounting\PpnCalculator;

it('calculates 11% PPN', function () {
    $calc = new PpnCalculator();
    expect($calc->calculate(100000))->toBe(11000.0);
});
