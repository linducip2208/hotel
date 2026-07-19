<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Reservation;
use Illuminate\Console\Command;

class EscalateOverdueOrders extends Command
{
    protected $signature = 'hotel:escalate-overdue';
    protected $description = 'Escalate overdue reservations and log to audit';

    public function handle()
    {
        $overdue = Reservation::where('status', 'checked_in')
            ->whereDate('check_out', '<', now()->subDay()->toDateString())
            ->get();

        foreach ($overdue as $res) {
            $res->update(['status' => 'overdue']);

            AuditLog::create([
                'property_id' => $res->property_id,
                'user_id'     => null,
                'user_type'   => 'system',
                'action'      => 'escale_overdue',
                'auditable_type' => Reservation::class,
                'auditable_id'   => $res->id,
                'metadata'    => [
                    'check_out' => $res->check_out->toDateString(),
                    'ref'       => $res->ref,
                ],
            ]);
        }

        $this->info('Escalated ' . $overdue->count() . ' overdue reservations.');
    }
}
