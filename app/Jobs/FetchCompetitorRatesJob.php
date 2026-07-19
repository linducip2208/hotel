<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Rms\RateShopperService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class FetchCompetitorRatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?int $propertyId = null,
        public ?string $date = null,
    ) {}

    public function handle(RateShopperService $svc): void
    {
        $date = Carbon::parse($this->date ?? now()->addDays(7)->toDateString());

        if ($this->propertyId) {
            $svc->fetchCompetitorRates($this->propertyId, $date);
        } else {
            $properties = \App\Models\Property::where('is_active', true)->get();
            foreach ($properties as $property) {
                try {
                    $svc->fetchCompetitorRates($property->id, $date);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Rate shopper failed for property {$property->id}: {$e->getMessage()}");
                }
            }
        }
    }
}
