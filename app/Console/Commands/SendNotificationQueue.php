<?php

namespace App\Console\Commands;

use App\Models\NotificationLog;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Console\Command;

class SendNotificationQueue extends Command
{
    protected $signature = 'hotel:send-notifications';
    protected $description = 'Send pending notifications from queue';

    public function handle(NotificationDispatcher $dispatcher)
    {
        $pending = NotificationLog::where('status', 'pending')
            ->take(50)
            ->get();

        $sent = 0;
        foreach ($pending as $notification) {
            try {
                $dispatcher->dispatchFromLog($notification);
                $sent++;
            } catch (\Throwable $e) {
                $notification->update([
                    'status' => 'failed',
                    'error'  => $e->getMessage(),
                ]);
            }
        }

        $this->info("Sent {$sent} notifications.");
    }
}
