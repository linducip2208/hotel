<?php

namespace App\Console\Commands\Guests;

use App\Jobs\BuildGuestProfileJob;
use App\Models\Guest;
use Illuminate\Console\Command;

class RebuildProfilesCommand extends Command
{
    protected $signature   = 'guests:rebuild-profiles {--guest= : Specific guest ID} {--sync : Run synchronously instead of queueing}';
    protected $description = 'Rebuild Guest 360 intelligence profiles from transaction history';

    public function handle(): int
    {
        $query = Guest::has('reservations');
        if ($id = $this->option('guest')) {
            $query->where('id', $id);
        }

        $guests  = $query->get();
        $sync    = $this->option('sync');
        $this->info("Rebuilding profiles for {$guests->count()} guest(s)...");

        foreach ($guests as $guest) {
            if ($sync) {
                dispatch_sync(new BuildGuestProfileJob($guest->id));
            } else {
                BuildGuestProfileJob::dispatch($guest->id);
            }
        }

        $this->info($sync ? 'Done.' : "All jobs queued.");
        return self::SUCCESS;
    }
}
