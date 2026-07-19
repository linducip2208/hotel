<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\FolioPayment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentGatewayCallbackReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly FolioPayment $folioPayment,
        public readonly array $rawCallback = [],
    ) {}
}
