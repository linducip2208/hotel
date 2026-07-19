<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\GuestRegistered;
use App\Models\NotificationLog;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendWelcomeMessage implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function handle(GuestRegistered $event, NotificationDispatcher $dispatcher): void
    {
        $guest = $event->guest;

        if (! $guest->email) {
            return;
        }

        $key = "welcome_guest:{$guest->id}";
        if (NotificationLog::where('idempotency_key', $key)->exists()) {
            return;
        }

        $dispatcher->welcomeGuest($guest);

        NotificationLog::create([
            'property_id'     => $guest->property_id,
            'channel'         => 'mail',
            'event'           => 'welcome_guest',
            'recipient'       => $guest->email,
            'notifiable_type' => get_class($guest),
            'notifiable_id'   => $guest->id,
            'status'          => 'sent',
            'idempotency_key' => $key,
            'sent_at'         => now(),
        ]);
    }
}
