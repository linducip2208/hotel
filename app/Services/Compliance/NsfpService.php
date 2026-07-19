<?php

namespace App\Services\Compliance;

use App\Models\EFakturRecord;
use App\Models\NsfpPool;
use App\Models\Property;
use Illuminate\Support\Facades\DB;

class NsfpService
{
    public function next(Property $property): string
    {
        return DB::transaction(function () use ($property) {
            $pool = NsfpPool::where('property_id', $property->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->orderBy('id')
                ->first();
            if (! $pool) {
                throw new \RuntimeException('No active NSFP pool. Request more from DJP.');
            }
            $next = $this->increment($pool->current_serial);
            $pool->current_serial = $next;
            if ($next === $pool->range_end) {
                $pool->is_active = false;
            }
            $pool->save();
            return $next;
        });
    }

    public function generateDemoPool(int $propertyId, int $count = 10): array
    {
        $rangeStart = (int) (now()->format('ymd') . '00000');
        $generated = [];

        for ($i = 0; $i < $count; $i++) {
            $nsfp = str_pad((string) ($rangeStart + $i), 13, '0', STR_PAD_LEFT);

            EFakturRecord::updateOrCreate(
                ['nomor_faktur' => $nsfp, 'property_id' => $propertyId],
                [
                    'kode_status' => '01',
                    'status' => 'available',
                    'request_payload' => ['source' => 'demo_pool'],
                    'response_payload' => ['simulated' => true],
                ]
            );

            $generated[] = $nsfp;
        }

        return ['success' => true, 'count' => $count, 'nsfp_list' => $generated];
    }

    protected function increment(string $serial): string
    {
        $num = (int) $serial + 1;
        return str_pad((string) $num, strlen($serial), '0', STR_PAD_LEFT);
    }
}
