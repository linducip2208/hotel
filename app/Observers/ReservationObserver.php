<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Services\Audit\AuditLogger;
use App\Services\Webhooks\WebhookDispatcher;

class ReservationObserver
{
    public function __construct(
        protected AuditLogger $audit,
        protected WebhookDispatcher $webhooks,
    ) {}

    public function created(Reservation $r): void
    {
        $this->audit->record('reservation.created', $r, ['ref' => $r->ref]);
        $this->webhooks->dispatch($r->property_id, 'reservation.created', $r->toArray());
    }

    public function updated(Reservation $r): void
    {
        if ($r->wasChanged('status')) {
            $this->audit->record('reservation.status_changed', $r, ['from' => $r->getOriginal('status'), 'to' => $r->status]);
            $this->webhooks->dispatch($r->property_id, 'reservation.'.$this->statusEvent($r->status), $r->toArray());
        }
    }

    protected function statusEvent(string $status): string
    {
        return match ($status) {
            'checked_in' => 'checked_in',
            'checked_out' => 'checked_out',
            'cancelled' => 'cancelled',
            'no_show' => 'no_show',
            default => 'updated',
        };
    }
}
