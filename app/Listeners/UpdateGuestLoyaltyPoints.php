<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\FolioPaymentReceived;
use App\Models\LoyaltyMember;
use App\Models\LoyaltyTransaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class UpdateGuestLoyaltyPoints implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(FolioPaymentReceived $event): void
    {
        $folio  = $event->folio;
        $payment = $event->folioPayment;

        if (! $folio->guest_id) {
            return;
        }

        $loyaltyMember = LoyaltyMember::where('guest_id', $folio->guest_id)->first();
        if (! $loyaltyMember) {
            return;
        }

        // Award 1 point per 10,000 IDR spent
        $pointsEarned = (int) floor((float) $payment->amount / 10_000);

        if ($pointsEarned <= 0) {
            return;
        }

        $loyaltyMember->increment('current_points', $pointsEarned);
        $loyaltyMember->increment('lifetime_points', $pointsEarned);

        LoyaltyTransaction::create([
            'property_id'      => $folio->property_id,
            'loyalty_member_id' => $loyaltyMember->id,
            'guest_id'         => $folio->guest_id,
            'transaction_type'  => 'earn',
            'points'           => $pointsEarned,
            'description'      => 'Points earned from payment #' . $payment->id,
            'source_type'      => get_class($payment),
            'source_id'        => $payment->id,
        ]);
    }
}
