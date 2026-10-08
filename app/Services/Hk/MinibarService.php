<?php

namespace App\Services\Hk;

use App\Models\FolioCharge;
use App\Models\MinibarConsumption;
use App\Models\MinibarProduct;
use App\Models\MinibarStock;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

class MinibarService
{
    /**
     * Snapshot the physical stock when a guest checks in. Checkout auto-charge
     * only bills the difference between this snapshot and the current stock
     * that was NOT already recorded as consumption during the stay — so an
     * item is charged exactly once.
     */
    public function snapshotOnCheckIn(int $roomId): void
    {
        MinibarStock::where('room_id', $roomId)
            ->update(['snapshot_qty' => DB::raw('current_qty')]);
    }

    public function getRoomStock(int $roomId): array
    {
        return MinibarStock::where('room_id', $roomId)
            ->with('product')->get()->toArray();
    }

    /**
     * Record guest consumption: posts a folio charge (once) and decrements stock.
     * Never posts a second charge for the same consumption row.
     */
    public function recordConsumption(int $roomId, int $reservationId, array $items, int $userId, bool $decrementStock = true): array
    {
        $charges = [];
        foreach ($items as $item) {
            $product = MinibarProduct::findOrFail($item['product_id']);
            $stock = MinibarStock::where('room_id', $roomId)
                ->where('minibar_product_id', $item['product_id'])->first();

            if (! $stock) {
                continue;
            }

            $qty = (int) $item['qty'];
            if ($decrementStock) {
                $qty = min($qty, $stock->current_qty);
            }
            if ($qty <= 0) {
                continue;
            }

            $reservation = Reservation::findOrFail($reservationId);

            // Room and stock must belong to the reservation's property (IDOR guard).
            $room = Room::findOrFail($roomId);
            if ((int) $room->property_id !== (int) $reservation->property_id
                || (int) $stock->property_id !== (int) $reservation->property_id) {
                throw new \RuntimeException('Kamar atau stok minibar tidak sesuai dengan property reservasi.');
            }

            $folio = $reservation->folios()->where('status', 'open')->first();
            if (! $folio) {
                throw new \RuntimeException('Folio terbuka tidak ditemukan untuk posting konsumsi minibar.');
            }

            $consumption = MinibarConsumption::create([
                'property_id' => $reservation->property_id,
                'reservation_id' => $reservationId,
                'room_id' => $roomId,
                'minibar_product_id' => $item['product_id'],
                'qty' => $qty,
                'unit_price' => $product->selling_price,
                'total_amount' => (float) $product->selling_price * $qty,
                'charged_by_user_id' => $userId,
                'consumption_date' => now()->toDateString(),
            ]);

            $folioCharge = FolioCharge::create([
                'property_id' => $reservation->property_id,
                'folio_id' => $folio->id,
                'charge_date' => now()->toDateString(),
                'category' => 'minibar',
                'description' => "Minibar: {$product->name} x{$qty}",
                'qty' => $qty,
                'unit_price' => $product->selling_price,
                'amount' => (float) $product->selling_price * $qty,
                'is_void' => false,
            ]);

            $consumption->update(['folio_charge_id' => $folioCharge->id]);

            if ($decrementStock) {
                $stock->decrement('current_qty', $qty);
            }

            $folio->recalculate();
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
                $stock->update(['snapshot_qty' => $stock->current_qty]);
            } else {
                MinibarStock::create([
                    'property_id' => $this->propertyIdForRoom($roomId),
                    'room_id' => $roomId,
                    'minibar_product_id' => $item['product_id'],
                    'initial_qty' => $item['qty'],
                    'current_qty' => $item['qty'],
                    'snapshot_qty' => $item['qty'],
                ]);
            }
        }
    }

    /**
     * Charge consumption detected at checkout EXACTLY ONCE:
     * shortfall vs check-in snapshot minus consumption already recorded
     * (and charged) for this reservation.
     */
    public function autoChargeOnCheckout(int $reservationId, int $roomId, int $userId): array
    {
        $stock = MinibarStock::where('room_id', $roomId)->with('product')->get();
        $items = [];

        // Consumption already recorded (and charged) for this reservation.
        $recorded = MinibarConsumption::where('room_id', $roomId)
            ->where('reservation_id', $reservationId)
            ->selectRaw('minibar_product_id, SUM(qty) as total')
            ->groupBy('minibar_product_id')
            ->pluck('total', 'minibar_product_id');

        foreach ($stock as $s) {
            $recordedQty = (int) ($recorded[$s->minibar_product_id] ?? 0);

            // Items HK recorded during the stay already decremented current_qty
            // AND were charged — they must never be charged again.
            if ($s->snapshot_qty !== null) {
                $missingSinceSnapshot = max(0, (int) $s->snapshot_qty - (int) $s->current_qty);
                $unrecorded = $missingSinceSnapshot - $recordedQty;
                if ($unrecorded > 0) {
                    $items[] = ['product_id' => $s->minibar_product_id, 'qty' => $unrecorded];
                }

                continue;
            }

            // Legacy rows without a snapshot (checked in before this fix):
            // fall back to the difference — but exclude what was already
            // recorded for this reservation to avoid the historical double charge.
            $consumed = (int) $s->initial_qty - (int) $s->current_qty;
            $unrecorded = $consumed - $recordedQty;
            if ($unrecorded > 0) {
                $items[] = ['product_id' => $s->minibar_product_id, 'qty' => $unrecorded];
            }
        }

        if (empty($items)) {
            return [];
        }

        // Stock already reflects the count — record the charge without
        // decrementing again (prevents over-decrement / phantom stock loss).
        return $this->recordConsumption($roomId, $reservationId, $items, $userId, decrementStock: false);
    }

    protected function propertyIdForRoom(int $roomId): int
    {
        $room = Room::findOrFail($roomId);

        return $room->property_id;
    }
}
