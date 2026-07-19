<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\GuestRegistered;
use App\Jobs\BuildGuestProfileJob;
use App\Models\Guest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

final class CreateOrMergeGuestProfile implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(GuestRegistered $event): void
    {
        $guest = $event->guest;

        // Dedup: check for existing guest with same email or phone
        if ($guest->email) {
            $duplicate = Guest::where('id', '!=', $guest->id)
                ->where('email', $guest->email)
                ->where('property_id', $guest->property_id)
                ->first();

            if ($duplicate) {
                // Merge: reassign reservations, folios, etc. to the newer guest record
                DB::transaction(function () use ($guest, $duplicate): void {
                    $guest->reservations()->where('primary_guest_id', $duplicate->id)
                        ->update(['primary_guest_id' => $guest->id]);

                    $guest->folios()->where('guest_id', $duplicate->id)
                        ->update(['guest_id' => $guest->id]);

                    $duplicate->delete();
                });
            }
        }

        BuildGuestProfileJob::dispatch($guest->id);
    }
}
