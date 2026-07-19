<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Folio;
use App\Models\FolioPayment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FolioPaymentReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Folio $folio,
        public readonly FolioPayment $folioPayment,
    ) {}
}
