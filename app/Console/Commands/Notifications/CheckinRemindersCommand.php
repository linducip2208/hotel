<?php

namespace App\Console\Commands\Notifications;

use App\Jobs\SendCheckinReminderJob;
use App\Models\Reservation;
use Illuminate\Console\Command;

class CheckinRemindersCommand extends Command
{
    protected $signature   = 'notifications:checkin-reminders {--date= : Check-in date (Y-m-d), defaults to tomorrow}';
    protected $description = 'Dispatch check-in reminder notifications for reservations arriving tomorrow';

    public function handle(): int
    {
        $date = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))
            : now()->addDay();

        $reservations = Reservation::whereDate('check_in', $date->toDateString())
            ->whereIn('status', ['confirmed', 'tentative'])
            ->with('primaryGuest')
            ->get();

        $this->info("Sending reminders for {$reservations->count()} reservation(s) checking in on {$date->toDateString()}...");

        foreach ($reservations as $reservation) {
            SendCheckinReminderJob::dispatch($reservation->id);
        }

        $this->info('All reminder jobs queued.');
        return self::SUCCESS;
    }
}
