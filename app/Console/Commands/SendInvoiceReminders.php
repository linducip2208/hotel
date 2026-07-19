<?php

namespace App\Console\Commands;

use App\Models\Folio;
use App\Models\NotificationLog;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendInvoiceReminders extends Command
{
    protected $signature = 'hotel:send-reminders';
    protected $description = 'Send H-1 check-in reminders and invoice due reminders';

    public function handle()
    {
        // H-1 check-in reminder
        $tomorrow = Reservation::whereDate('check_in', now()->addDay()->toDateString())
            ->whereIn('status', ['confirmed', 'tentative'])
            ->with('primaryGuest')
            ->get();

        foreach ($tomorrow as $res) {
            $email = $res->primaryGuest?->email;
            if (!$email) continue;

            NotificationLog::create([
                'property_id'    => $res->property_id,
                'channel'        => 'email',
                'event'          => 'checkin_reminder',
                'recipient'      => $email,
                'notifiable_type' => Reservation::class,
                'notifiable_id'  => $res->id,
                'status'         => 'pending',
                'idempotency_key'=> 'checkin-h1-' . $res->id . '-' . now()->toDateString(),
                'metadata'       => [
                    'ref'      => $res->ref,
                    'check_in' => $res->check_in->format('d M Y'),
                ],
            ]);
        }

        // Invoice due in 3 days
        $dueSoon = Folio::where('status', 'open')
            ->whereHas('reservation', fn($q) => $q->whereDate('check_out', '<', now()->addDays(3)->toDateString()))
            ->with('reservation.primaryGuest')
            ->get();

        foreach ($dueSoon as $folio) {
            $email = $folio->reservation?->primaryGuest?->email;
            if (!$email) continue;

            NotificationLog::create([
                'property_id'    => $folio->property_id,
                'channel'        => 'email',
                'event'          => 'invoice_reminder',
                'recipient'      => $email,
                'notifiable_type' => Reservation::class,
                'notifiable_id'  => $folio->reservation_id,
                'status'         => 'pending',
                'idempotency_key'=> 'inv-due-' . $folio->id . '-' . now()->toDateString(),
                'metadata'       => [
                    'ref'          => $folio->reservation?->ref,
                    'folio_id'     => $folio->id,
                    'balance'      => (float) ($folio->balance ?? $folio->total_charges ?? 0),
                ],
            ]);
        }

        $this->info('Reminders queued: ' . $tomorrow->count() . ' check-in + ' . $dueSoon->count() . ' invoices.');
    }
}
