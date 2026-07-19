<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Property;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AriSyncCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Property $property,
        public readonly array $summary = [],
    ) {}
}
