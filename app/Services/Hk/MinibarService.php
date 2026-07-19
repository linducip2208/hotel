<?php

namespace App\Services\Hk;

use App\Models\FolioCharge;
use App\Models\MinibarConsumption;
use App\Models\MinibarProduct;
use App\Models\MinibarStock;
use App\Models\Reservation;

class MinibarService
{
    public function getRoomStock(int $roomId): array
    {
        return MinibarStock::where('room_id', $roomId)
            ->with('product')->get()->toArray();
    }

    public function recordConsumption(int $roomId, int $reservationId, array $items, int $userId): array
    {
        $charges = [];
        foreach ($items as $item) {
            $product = MinibarProduct::findOrFail($item['product_id']);
            $stock = MinibarStock::where('room_id', $roomId)
                ->where('minibar_product_id', $item['product_id'])->first();

            if (! $stock) {
                continue;
            }

            $qty = min((int) $item['qty'], $stock->current_qty);
            if ($qty <= 0) {
                continue;
            }

            $consumption = MinibarConsumption::create([
                'property_id' => app('current_property')->id,
                'reservation_id' => $reservationId,
                'room_id' => $roomId,
                'minibar_product_id' => $item['product_id'],
                'qty' => $qty,
                'unit_price' => $product->selling_price,
                'total_amount' => (float) $product->selling_price * $qty,
                'charged_by_user_id' => $userId,
                'consumption_date' => now()->toDateString(),
            ]);

            $reservation = Reservation::find($reservationId);
            $folioId = $reservation ? $reservation->folios()->first()?->id : null;

            $folioCharge = FolioCharge::create([
                'property_id' => app('current_property')->id,
                'reservation_id' => $reservationId,
                'folio_id' => $folioId,
                'charge_date' => now()->toDateString(),
                'category' => 'minibar',
                'description' => "Minibar: {$product->name} x{$qty}",
                'amount' => (float) $product->selling_price * $qty,
                'is_void' => false,
            ]);

            $consumption->update(['folio_charge_id' => $folioCharge->id]);
            $stock->decrement('current_qty', $qty);
            $charges[] = $consumption;
        }
        return $charges;
    }

    public function restock(int $roomId, array $items): void
    {
        foreach ($items as $item) {
            $stock = MinibarStock::where('room_id', $roomId)
                ->where('minibar_product_id', $item['product_id'])->first();
            if ($stock) {
                $stock->increment('current_qty', $item['qty']);
            } else {
                MinibarStock::create([
                    'property_id' => app('current_property')->id,
                    'room_id' => $roomId,
                    'minibar_product_id' => $item['product_id'],
                    'initial_qty' => $item['qty'],
                    'current_qty' => $item['qty'],
                ]);
            }
        }
    }

    public function autoChargeOnCheckout(int $reservationId, int $roomId, int $userId): array
    {
        $stock = MinibarStock::where('room_id', $roomId)->with('product')->get();
        $items = [];
        foreach ($stock as $s) {
            $consumed = $s->initial_qty - $s->current_qty;
            if ($consumed > 0) {
                $items[] = ['product_id' => $s->minibar_product_id, 'qty' => $consumed];
            }
        }
        if (empty($items)) {
            return [];
        }
        return $this->recordConsumption($roomId, $reservationId, $items, $userId);
    }
}
