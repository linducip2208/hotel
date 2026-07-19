<?php

namespace App\Services\Revenue;

use App\Models\Package;
use App\Models\PackageItem;
use App\Models\Reservation;
use App\Models\ReservationPackage;
use App\Models\PackageCustomization;

class DynamicPackageService
{
    public function buildPackage(int $packageId, array $customizations = []): array
    {
        $package = Package::with('items')->findOrFail($packageId);
        $totalPrice = (float) $package->base_price;

        $items = [];
        foreach ($package->items as $item) {
            $items[] = [
                'type' => $item->item_type,
                'name' => $item->name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'included' => true,
            ];
        }

        // Add customizations
        foreach ($customizations as $opt) {
            $modifier = (float) ($opt['price_modifier'] ?? 0);
            $totalPrice += $modifier;
            $items[] = [
                'type' => $opt['type'] ?? 'addon',
                'name' => $opt['name'],
                'unit_price' => $modifier,
                'included' => false,
            ];
        }

        return [
            'package_id' => $package->id,
            'package_name' => $package->name,
            'base_price' => (float) $package->base_price,
            'total_price' => $totalPrice,
            'items' => $items,
        ];
    }

    public function attachToReservation(int $reservationId, int $packageId, array $customizations = []): ReservationPackage
    {
        $built = $this->buildPackage($packageId, $customizations);
        $reservation = Reservation::findOrFail($reservationId);

        $rp = ReservationPackage::create([
            'property_id' => $reservation->property_id,
            'reservation_id' => $reservationId,
            'package_id' => $packageId,
            'price_charged' => $built['total_price'],
        ]);

        foreach ($customizations as $opt) {
            PackageCustomization::create([
                'reservation_package_id' => $rp->id,
                'property_id' => $reservation->property_id,
                'option_type' => $opt['type'] ?? 'addon',
                'reference_id' => $opt['reference_id'] ?? null,
                'name' => $opt['name'],
                'price_modifier' => $opt['price_modifier'] ?? 0,
            ]);
        }

        // Update reservation total
        $reservation->increment('total_addons', $built['total_price']);
        $reservation->increment('grand_total', $built['total_price']);
        $reservation->increment('balance', $built['total_price']);

        // Add to folio if active
        if ($reservation->status === 'checked_in') {
            $folio = \App\Models\Folio::where('reservation_id', $reservationId)
                ->where('status', 'open')
                ->first();
            if ($folio) {
                \App\Models\FolioCharge::create([
                    'folio_id' => $folio->id,
                    'property_id' => $reservation->property_id,
                    'category' => 'package',
                    'description' => "Package: {$built['package_name']}",
                    'charge_date' => today(),
                    'unit_price' => $built['total_price'],
                    'qty' => 1,
                    'amount' => $built['total_price'],
                    'is_taxable' => true,
                ]);
            }
        }

        return $rp;
    }

    public function getDynamicOptions(int $packageId): array
    {
        $package = Package::findOrFail($packageId);
        if (!$package->is_dynamic) return [];

        return $package->dynamic_options ?? [];
    }

    public function calculatePriceRange(int $packageId): array
    {
        $package = Package::findOrFail($packageId);
        $base = (float) $package->base_price;

        if (!$package->is_dynamic) {
            return ['min' => $base, 'max' => $base];
        }

        $options = $package->dynamic_options ?? [];
        $maxAddons = 0;
        foreach ($options as $opt) {
            if (isset($opt['price_modifier']) && $opt['price_modifier'] > 0) {
                $maxAddons += (float) $opt['price_modifier'];
            }
        }

        return ['min' => $base, 'max' => $base + $maxAddons];
    }
}
