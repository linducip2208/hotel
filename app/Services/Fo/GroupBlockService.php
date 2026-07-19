<?php

namespace App\Services\Fo;

use App\Models\Folio;
use App\Models\GroupBlock;
use App\Models\GroupBlockRoom;
use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupBlockService
{
    public function create(array $data): GroupBlock
    {
        return DB::transaction(function () use ($data) {
            $block = GroupBlock::create([
                'property_id' => $data['property_id'],
                'company_id' => $data['company_id'] ?? null,
                'block_code' => 'GRP-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
                'group_name' => $data['group_name'],
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'rooms_count' => collect($data['rooms'])->sum('rooms_count'),
                'negotiated_rate' => $data['negotiated_rate'] ?? null,
                'cutoff_date' => $data['cutoff_date'] ?? null,
                'status' => 'tentative',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['rooms'] as $r) {
                GroupBlockRoom::create([
                    'group_block_id' => $block->id,
                    'room_type_id' => $r['room_type_id'],
                    'rooms_count' => $r['rooms_count'],
                    'rate' => $r['rate'] ?? null,
                ]);

                $this->blockInventory($data['property_id'], $r['room_type_id'], $r['rooms_count'],
                    Carbon::parse($data['check_in']), Carbon::parse($data['check_out']));
            }

            // Create master folio
            $folio = Folio::create([
                'property_id' => $data['property_id'],
                'folio_no' => 'M-'.$block->block_code,
                'type' => 'master',
                'status' => 'open',
                'currency' => 'IDR',
            ]);
            $block->update(['master_folio_id' => $folio->id]);

            return $block->fresh(['rooms', 'masterFolio']);
        });
    }

    protected function blockInventory(int $propertyId, int $roomTypeId, int $count, Carbon $in, Carbon $out): void
    {
        $cursor = $in->copy();
        while ($cursor->lt($out)) {
            $inv = Inventory::firstOrCreate(
                ['property_id' => $propertyId, 'room_type_id' => $roomTypeId, 'date' => $cursor->toDateString()],
                ['total' => 0, 'sold' => 0, 'blocked' => 0, 'out_of_order' => 0]
            );
            $inv->increment('blocked', $count);
            $cursor->addDay();
        }
    }

    public function releaseUnpickedRooms(GroupBlock $block): void
    {
        if ($block->cutoff_date && $block->cutoff_date->isPast() && $block->status === 'definite') {
            foreach ($block->rooms as $room) {
                $unpicked = (int) ($room->rooms_count) - (int) ($room->rooms_picked_up ?? 0);
                if ($unpicked > 0) {
                    Inventory::where('property_id', $block->property_id)
                        ->where('room_type_id', $room->room_type_id)
                        ->whereBetween('date', [$block->check_in->toDateString(), $block->check_out->subDay()->toDateString()])
                        ->decrement('blocked', $unpicked);
                }
            }
            $block->update(['status' => 'completed']);
        }
    }
}
