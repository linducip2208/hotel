<?php

namespace App\Services\Banquet;

use App\Models\Event;
use App\Models\EventMenuItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventService
{
    public function create(array $data): Event
    {
        return DB::transaction(function () use ($data) {
            $event = Event::create($data + [
                'event_no' => 'EVT-'.now()->format('Ym').'-'.Str::upper(Str::random(5)),
                'status' => 'inquiry',
            ]);
            $this->recalculateTotal($event);
            return $event->fresh();
        });
    }

    public function addMenuItem(Event $event, array $item): EventMenuItem
    {
        $line = EventMenuItem::create([
            'event_id' => $event->id,
            'name' => $item['name'],
            'qty' => $item['qty'],
            'unit_price' => $item['unit_price'],
            'subtotal' => $item['qty'] * $item['unit_price'],
        ]);
        $this->recalculateTotal($event->fresh());
        return $line;
    }

    public function recalculateTotal(Event $event): void
    {
        $event->fnb_total = (float) $event->menuItems()->sum('subtotal');
        $event->grand_total = (float) $event->venue_rate + (float) $event->fnb_total + (float) $event->addons_total;
        $event->balance = $event->grand_total - $event->deposit_paid;
        $event->save();
    }

    /**
     * Generate Banquet Event Order (BEO) — printable summary.
     */
    public function generateBeo(Event $event): array
    {
        return [
            'event' => $event,
            'function_room' => $event->functionRoom,
            'menu_items' => $event->menuItems,
            'av_equipment' => $event->av_equipment,
            'expected_attendees' => $event->expected_attendees,
            'setup' => $event->setup,
            'totals' => [
                'venue' => $event->venue_rate,
                'fnb' => $event->fnb_total,
                'addons' => $event->addons_total,
                'grand_total' => $event->grand_total,
                'deposit_paid' => $event->deposit_paid,
                'balance' => $event->balance,
            ],
        ];
    }
}
