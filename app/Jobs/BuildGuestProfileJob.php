<?php

namespace App\Jobs;

use App\Models\Guest;
use App\Services\Guest\GuestProfileService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BuildGuestProfileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(public int $guestId) {}

    public function handle(GuestProfileService $svc): void
    {
        $guest = Guest::findOrFail($this->guestId);
        $svc->rebuild($guest);
    }
}
