<?php

namespace App\Jobs;

use App\Models\Property;
use App\Services\Channel\ParityMonitorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckChannelParityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(public int $propertyId) {}

    public function handle(ParityMonitorService $svc): void
    {
        $property = Property::findOrFail($this->propertyId);
        $svc->checkAndAlert($property);
    }
}
