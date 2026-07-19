<?php

namespace App\Jobs;

use App\Models\Property;
use App\Services\Pricing\DynamicPricingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ApplyDynamicPricingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 180;

    public function __construct(public int $propertyId) {}

    public function handle(DynamicPricingService $svc): void
    {
        $property = Property::findOrFail($this->propertyId);
        $applied = $svc->applyRules($property);
        Log::info("DynamicPricing applied", ['property' => $this->propertyId, 'overrides' => $applied]);
    }
}
