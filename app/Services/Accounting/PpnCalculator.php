<?php

namespace App\Services\Accounting;

class PpnCalculator
{
    public const RATE = 11.0;

    public function calculate(float $base): float
    {
        return round($base * self::RATE / 100, 2);
    }
}
