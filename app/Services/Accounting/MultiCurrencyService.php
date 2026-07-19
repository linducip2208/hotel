<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\FxRate;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

final class MultiCurrencyService
{
    private const CACHE_TTL = 3600;
    private string $baseCurrency = 'IDR';

    /**
     * Convert an amount from one currency to another using historical FX rate.
     */
    public function convert(float $amount, string $fromCurrency, string $toCurrency, ?Carbon $date = null): float
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $date ??= now();

        // Direct rate lookup
        $rate = $this->getRate($fromCurrency, $toCurrency, $date);
        if ($rate !== null) {
            return round($amount * $rate, 2);
        }

        // Try cross-rate via base currency if available
        if ($fromCurrency !== $this->baseCurrency && $toCurrency !== $this->baseCurrency) {
            $rateFrom = $this->getRate($fromCurrency, $this->baseCurrency, $date);
            $rateTo = $this->getRate($toCurrency, $this->baseCurrency, $date);

            if ($rateFrom !== null && $rateTo !== null) {
                $inBase = $amount * $rateFrom;
                return round($inBase / $rateTo, 2);
            }
        }

        throw new RuntimeException("No FX rate found for {$fromCurrency} → {$toCurrency} on {$date->toDateString()}");
    }

    /**
     * Get the base currency equivalent of a foreign amount.
     */
    public function getBaseCurrencyEquivalent(float $amount, string $fromCurrency, Carbon $date): float
    {
        if ($fromCurrency === $this->baseCurrency) {
            return $amount;
        }

        $rate = $this->getRate($fromCurrency, $this->baseCurrency, $date);
        if ($rate === null) {
            throw new RuntimeException("No FX rate found for {$fromCurrency} → {$this->baseCurrency}");
        }

        return round($amount * $rate, 2);
    }

    /**
     * Post a journal entry with currency information.
     */
    public function postJournalInCurrency(
        int $propertyId,
        string $description,
        array $lines,
        string $currency,
        ?Carbon $date = null
    ): JournalEntry {
        $date ??= now();
        $poster = new JournalPoster();

        $convertedLines = [];
        foreach ($lines as $line) {
            $originalAmount = (float) ($line['debit'] ?? 0) ?: -(float) ($line['credit'] ?? 0);

            $convertedLines[] = [
                'account_code' => $line['account_code'],
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0,
                'description' => $line['description'] ?? $description,
                'tax_code' => $line['tax_code'] ?? null,
                '_currency' => $currency,
                '_original_amount' => $originalAmount,
            ];
        }

        return $poster->post(
            $propertyId,
            $description,
            $convertedLines,
            $line['source_type'] ?? null,
            $line['source_id'] ?? null
        );
    }

    /**
     * Update FX rates from an external API.
     */
    public function updateFxRates(): array
    {
        $provider = \App\Models\Provider::where('api_format', 'currency')
            ->where('is_active', true)
            ->first();

        if (! $provider) {
            throw new RuntimeException('No active currency provider configured.');
        }

        $apiKey = $provider->getApiKey();
        $baseUrl = rtrim((string) $provider->base_url, '/');

        $client = new \GuzzleHttp\Client(['base_uri' => $baseUrl, 'timeout' => 15]);
        $response = $client->get('/latest', [
            'query' => ['apikey' => $apiKey, 'base_currency' => $this->baseCurrency],
        ]);

        $data = json_decode((string) $response->getBody(), true);
        if (! isset($data['data']) && ! isset($data['rates'])) {
            throw new RuntimeException('Invalid FX rate API response.');
        }

        $rates = $data['data'] ?? $data['rates'] ?? [];
        $today = Carbon::now()->toDateString();
        $updated = 0;

        foreach ($rates as $currency => $rate) {
            FxRate::updateOrCreate(
                ['base_currency' => $this->baseCurrency, 'quote_currency' => $currency, 'rate_date' => $today],
                ['rate' => $rate, 'source' => 'api']
            );
            $updated++;
        }

        return ['updated' => $updated, 'date' => $today, 'source' => $provider->name];
    }

    private function getRate(string $from, string $to, Carbon $date): ?float
    {
        $cacheKey = "fx:{$from}:{$to}:{$date->toDateString()}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($from, $to, $date) {
            $rate = FxRate::lookup($from, $to, $date);
            if ($rate !== null) {
                return $rate;
            }

            return FxRate::lookup($from, $to);
        });
    }
}
