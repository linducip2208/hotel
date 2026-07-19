<?php

namespace App\Services\Finance;

use App\Models\FxRate;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class FxRateService
{
    protected Client $http;

    protected array $supported = ['IDR', 'USD', 'SGD', 'MYR', 'AUD', 'EUR', 'JPY', 'CNY', 'GBP', 'THB'];

    public function __construct()
    {
        $this->http = new Client(['timeout' => 10]);
    }

    public function fetchLive(string $base = 'IDR'): array
    {
        return Cache::remember('fx_rates_' . $base, 3600, function () use ($base) {
            try {
                $resp = $this->http->get("https://open.er-api.com/v6/latest/{$base}");
                $data = json_decode((string) $resp->getBody(), true);
                $rates = $data['rates'] ?? [];

                foreach ($rates as $currency => $rate) {
                    if (in_array($currency, $this->supported)) {
                        FxRate::updateOrCreate(
                            [
                                'base_currency' => $base,
                                'quote_currency' => $currency,
                            ],
                            [
                                'rate' => $rate,
                                'rate_date' => now()->toDateString(),
                                'source' => 'open.er-api.com',
                            ]
                        );
                    }
                }

                return $rates;
            } catch (\Exception $e) {
                \Log::error("FX rate fetch failed: {$e->getMessage()}");
                return [];
            }
        });
    }

    public function convert(float $amount, string $from, string $to): float
    {
        $rates = $this->fetchLive($from);
        $rate = $rates[$to] ?? 1;
        return round($amount * $rate, 2);
    }

    public function getRateCard(string $base = 'IDR'): array
    {
        $rates = $this->fetchLive($base);
        $card = [];
        foreach ($this->supported as $currency) {
            if ($currency === $base) {
                continue;
            }
            $card[] = [
                'currency' => $currency,
                'rate' => $rates[$currency] ?? 0,
                'base' => $base,
            ];
        }
        return $card;
    }

    public function supportedCurrencies(): array
    {
        return $this->supported;
    }
}
