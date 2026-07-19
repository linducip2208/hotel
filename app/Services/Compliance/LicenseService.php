<?php

namespace App\Services\Compliance;

use App\Models\Property;
use App\Models\PropertyLicense;
use Carbon\Carbon;

class LicenseService
{
    public function list(Property $property): \Illuminate\Database\Eloquent\Collection
    {
        return PropertyLicense::where('property_id', $property->id)
            ->orderBy('expiry_date')
            ->get();
    }

    public function create(Property $property, array $data): PropertyLicense
    {
        $data['property_id'] = $property->id;
        $data['status'] = $this->computeStatus($data['expiry_date'] ?? null);
        return PropertyLicense::create($data);
    }

    public function update(PropertyLicense $license, array $data): PropertyLicense
    {
        if (isset($data['expiry_date'])) {
            $data['status'] = $this->computeStatus($data['expiry_date']);
        }
        $license->update($data);
        return $license->fresh();
    }

    public function delete(PropertyLicense $license): void
    {
        $license->delete();
    }

    public function computeStatus(?string $expiryDate): string
    {
        if (!$expiryDate) return 'active';
        $expiry = Carbon::parse($expiryDate)->startOfDay();
        $now = now()->startOfDay();
        if ($expiry->isPast()) return 'expired';
        if ($expiry->diffInDays($now) <= 30) return 'expiring_soon';
        return 'active';
    }

    public function checkExpiry(Property $property): array
    {
        $expiring = PropertyLicense::where('property_id', $property->id)->get()
            ->filter(fn($l) => $l->daysUntilExpiry() <= $l->renewal_reminder_days && $l->daysUntilExpiry() >= 0)
            ->values();

        $expired = PropertyLicense::where('property_id', $property->id)
            ->where('status', '!=', 'expired')->get()
            ->filter(fn($l) => $l->daysUntilExpiry() < 0)
            ->values();

        foreach ($expired as $l) {
            $l->update(['status' => 'expired']);
        }

        foreach ($expiring as $l) {
            $l->update(['status' => 'expiring_soon']);
        }

        return ['expiring' => $expiring->count(), 'expired' => $expired->count()];
    }
}
