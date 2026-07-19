<?php

namespace App\Services\Revenue;

use App\Models\Package;
use App\Models\PackageItem;
use App\Models\ReservationPackage;
use App\Models\Reservation;
use App\Models\FolioCharge;

class PackageService
{
    public function getActivePackages(int $propertyId): array
    {
        return Package::where('property_id', $propertyId)
            ->where('is_active', true)
            ->with('items')
            ->orderBy('display_order')
            ->get()->toArray();
    }

    public function calculatePackagePrice(Package $package, int $nights): float
    {
        $itemsTotal = $package->items->sum(fn ($item) => $item->unit_price * $item->quantity);

        return $package->base_price * $nights + $itemsTotal;
    }

    public function attachToReservation(Reservation $reservation, Package $package): ReservationPackage
    {
        $nights = $reservation->check_in?->diffInDays($reservation->check_out) ?? 1;

        $price = $this->calculatePackagePrice($package, max(1, $nights));

        $rp = ReservationPackage::create([
            'property_id' => $reservation->property_id,
            'reservation_id' => $reservation->id,
            'package_id' => $package->id,
            'price_charged' => $price,
        ]);

        $folio = $reservation->folios()->first();
        $folioCharge = FolioCharge::create([
            'property_id' => $reservation->property_id,
            'folio_id' => $folio?->id,
            'charge_date' => now()->toDateString(),
            'category' => 'package',
            'description' => "Package: {$package->name}",
            'amount' => $price,
            'source_type' => 'reservation',
            'source_ref' => (string) $reservation->id,
            'is_void' => false,
        ]);

        $rp->update(['folio_charge_id' => $folioCharge->id]);

        return $rp;
    }

    public function detachFromReservation(ReservationPackage $rp): void
    {
        if ($rp->folioCharge) {
            $rp->folioCharge->update(['is_void' => true]);
        }
        $rp->delete();
    }
}
