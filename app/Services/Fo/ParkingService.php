<?php

namespace App\Services\Fo;

use App\Models\Folio;
use App\Models\FolioCharge;
use App\Models\Guest;
use App\Models\ParkingRecord;
use App\Models\ParkingSlot;
use App\Models\Property;
use App\Models\Reservation;
use Carbon\Carbon;

class ParkingService
{
    public function getSlots(Property $property): array
    {
        $slots = ParkingSlot::where('property_id', $property->id)
            ->orderBy('area')
            ->orderBy('slot_number')
            ->get()
            ->groupBy('area')
            ->toArray();

        $stats = [
            'total' => ParkingSlot::where('property_id', $property->id)->count(),
            'available' => ParkingSlot::where('property_id', $property->id)->where('status', 'available')->count(),
            'occupied' => ParkingSlot::where('property_id', $property->id)->where('status', 'occupied')->count(),
            'reserved' => ParkingSlot::where('property_id', $property->id)->where('status', 'reserved')->count(),
            'maintenance' => ParkingSlot::where('property_id', $property->id)->where('status', 'maintenance')->count(),
        ];

        return compact('slots', 'stats');
    }

    public function getActiveRecords(Property $property): array
    {
        return ParkingRecord::where('property_id', $property->id)
            ->where('status', 'parked')
            ->with(['parkingSlot', 'guest', 'reservation'])
            ->orderByDesc('check_in')
            ->get()
            ->toArray();
    }

    public function checkIn(Property $property, array $data): ParkingRecord
    {
        $slot = ParkingSlot::where('property_id', $property->id)
            ->where('id', $data['parking_slot_id'])
            ->firstOrFail();

        if ($slot->status !== 'available') {
            throw new \RuntimeException('Slot parkir tidak tersedia.');
        }

        $slot->update(['status' => 'occupied']);

        return ParkingRecord::create([
            'property_id' => $property->id,
            'parking_slot_id' => $slot->id,
            'reservation_id' => $data['reservation_id'] ?? null,
            'guest_id' => $data['guest_id'] ?? null,
            'vehicle_plate' => $data['vehicle_plate'],
            'vehicle_type' => $data['vehicle_type'] ?? 'car',
            'vehicle_brand' => $data['vehicle_brand'] ?? null,
            'vehicle_color' => $data['vehicle_color'] ?? null,
            'daily_rate' => $data['daily_rate'] ?? 0,
            'is_valet' => $data['is_valet'] ?? false,
            'valet_by_user_id' => $data['valet_by_user_id'] ?? null,
            'valet_key_location' => $data['valet_key_location'] ?? null,
        ]);
    }

    public function checkOut(int $recordId): ParkingRecord
    {
        $record = ParkingRecord::with('parkingSlot')->findOrFail($recordId);

        if ($record->status === 'exited') {
            throw new \RuntimeException('Kendaraan sudah keluar.');
        }

        $now = now();
        $duration = $record->check_in->diffInHours($now);
        $hours = max(1, ceil($duration));
        $totalCharge = $hours * $record->daily_rate;

        $record->update([
            'check_out' => $now,
            'total_charge' => $totalCharge,
            'status' => 'exited',
        ]);

        $record->parkingSlot->update(['status' => 'available']);

        return $record->fresh();
    }

    public function calculateCharge(ParkingRecord $record): float
    {
        $now = now();
        $duration = $record->check_in->diffInHours($now);
        $hours = max(1, ceil($duration));
        return $hours * $record->daily_rate;
    }

    public function getValetRecords(Property $property): array
    {
        return ParkingRecord::where('property_id', $property->id)
            ->where('is_valet', true)
            ->where('status', 'parked')
            ->with(['parkingSlot', 'guest', 'valetByUser'])
            ->orderByDesc('check_in')
            ->get()
            ->toArray();
    }

    public function chargeToFolio(ParkingRecord $record, Folio $folio): FolioCharge
    {
        $amount = $record->total_charge > 0 ? $record->total_charge : $this->calculateCharge($record);

        $charge = $folio->charges()->create([
            'property_id' => $folio->property_id,
            'amount' => $amount,
            'tax_amount' => 0,
            'description' => 'Parkir - ' . $record->vehicle_plate . ' (' . $record->parkingSlot->slot_number . ')',
            'source_type' => ParkingRecord::class,
            'source_id' => $record->id,
        ]);

        $record->update(['folio_charge_id' => $charge->id]);
        $folio->recalculate();

        return $charge;
    }
}
