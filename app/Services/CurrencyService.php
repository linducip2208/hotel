<?php

namespace App\Services;

use App\Models\FxRate;
use App\Models\Property;

class CurrencyService
{
    public const SESSION_KEY = 'display_currency';

    /** Currencies we can display, in priority order. */
    public function available(): array
    {
        return ['IDR', 'USD', 'EUR', 'SGD', 'MYR', 'AUD', 'GBP', 'JPY', 'CNY'];
    }

    public function defaultFor(?Property $property = null): string
    {
        $property ??= app()->bound('current_property') ? app('current_property') : Property::orderBy('id')->first();

        return $property?->currency_default ?? 'IDR';
    }

    public function selected(): string
    {
        $code = session(self::SESSION_KEY);
        if ($code && in_array($code, $this->available(), true)) {
            return $code;
        }

        return $this->defaultFor();
    }

    /**
     * Convert an amount in the property's base currency into the selected
     * display currency using the latest FxRate row. Falls back to the raw
     * amount when no rate is available.
     */
    public function convert(float $amount, ?string $from = null): float
    {
        $from ??= $this->defaultFor();
        $to = $this->selected();

        if ($from === $to) {
            return $amount;
        }

        $rate = FxRate::lookup($from, $to);
        if ($rate === null) {
            return $amount;
        }

        return round($amount * $rate, 2);
    }

    public function format(float $amount, ?string $currency = null): string
    {
        $currency ??= $this->selected();
        $amount = $this->convert($amount);

        try {
            $formatter = new \NumberFormatter(app()->getLocale(), \NumberFormatter::CURRENCY);

            return $formatter->formatCurrency($amount, $currency);
        } catch (\Throwable $e) {
            return $currency.' '.number_format($amount, 2);
        }
    }
}
