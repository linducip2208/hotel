<?php

namespace App\Services\Fo;

use App\Models\Inventory;
use App\Models\WaitlistEntry;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Support\Facades\Log;

class WaitlistService
{
    public function add(array $data): WaitlistEntry
    {
        return WaitlistEntry::create($data + ['status' => 'waiting']);
    }

    /** Periodic check: cari waitlist yang sekarang punya availability, notify. */
    public function processNotifications(int $propertyId): int
    {
        $count = 0;
        WaitlistEntry::where('property_id', $propertyId)
            ->where('status', 'waiting')
            ->each(function (WaitlistEntry $w) use (&$count) {
                if ($this->hasAvailability($w)) {
                    $w->update(['status' => 'notified', 'notified_at' => now()]);
                    Log::info("Waitlist notify entry #{$w->id}");
                    $count++;
                }
            });
        return $count;
    }

    protected function hasAvailability(WaitlistEntry $w): bool
    {
        if (! $w->preferred_room_type_id) return false;
        $cursor = $w->check_in->copy();
        while ($cursor->lt($w->check_out)) {
            $inv = Inventory::where([
                'property_id' => $w->property_id,
                'room_type_id' => $w->preferred_room_type_id,
                'date' => $cursor->toDateString(),
            ])->first();
            if (! $inv || $inv->available < $w->rooms) return false;
            $cursor->addDay();
        }
        return true;
    }
}
