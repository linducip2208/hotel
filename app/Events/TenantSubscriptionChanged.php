<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\TenantSubscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantSubscriptionChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly TenantSubscription $subscription,
        public readonly string $changeType,
    ) {}
}
