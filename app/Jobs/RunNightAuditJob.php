<?php

namespace App\Jobs;

use App\Models\Property;
use App\Services\Accounting\NightAuditService;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunNightAuditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1; // night audit must not run twice
    public int $timeout = 300;

    public function __construct(
        public int $propertyId,
        public string $auditDate
    ) {}

    public function handle(NightAuditService $svc): void
    {
        $property = Property::findOrFail($this->propertyId);
        $svc->run($property, new DateTime($this->auditDate));
        Log::info("NightAudit complete", ['property' => $this->propertyId, 'date' => $this->auditDate]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error("NightAudit failed", [
            'property' => $this->propertyId,
            'date'     => $this->auditDate,
            'error'    => $e->getMessage(),
        ]);
    }
}
