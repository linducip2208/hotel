<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Folio;
use App\Models\FolioCharge;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FolioCharged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Folio $folio,
        public readonly FolioCharge $folioCharge,
    ) {}
}
