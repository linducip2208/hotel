<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\JournalEntryPosted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class PushToExternalAccounting implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 5;
    public array $backoff = [60, 300, 900, 3600];

    public function handle(JournalEntryPosted $event): void
    {
        // Future integration stub: push to Xero, Accurate Online, SAP, etc.
        // Will be implemented based on property's configured accounting integration.
        // For now, this serves as the extension point.
    }
}
