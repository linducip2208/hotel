<?php

namespace App\Observers;

use App\Models\Folio;
use App\Services\Audit\AuditLogger;
use App\Services\Webhooks\WebhookDispatcher;

class FolioObserver
{
    public function __construct(protected AuditLogger $audit, protected WebhookDispatcher $webhooks) {}

    public function updated(Folio $f): void
    {
        if ($f->wasChanged('status') && $f->status === 'closed') {
            $this->audit->record('folio.closed', $f);
            $this->webhooks->dispatch($f->property_id, 'folio.settled', $f->toArray());
        }
    }
}
