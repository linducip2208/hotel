<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Rate;
use Illuminate\Http\Request;

class AriController extends Controller
{
    public function availability(Request $request)
    {
        $count = 0;
        foreach ($request->input('updates', []) as $u) {
            Inventory::updateOrCreate(
                ['property_id' => $u['property_id'] ?? 1, 'room_type_id' => $u['room_type_id'], 'date' => $u['date']],
                ['total' => $u['count']]
            );
            $count++;
        }
        return response()->json(['updated' => $count]);
    }

    public function rates(Request $request)
    {
        return app(RateController::class)->bulkUpdate($request);
    }

    public function restrictions(Request $request)
    {
        $count = 0;
        foreach ($request->input('updates', []) as $u) {
            Rate::where(['property_id' => $u['property_id'] ?? 1, 'room_type_id' => $u['room_type_id'], 'rate_plan_id' => $u['rate_plan_id'], 'date' => $u['date']])
                ->update(array_filter([
                    'min_los' => $u['min_los'] ?? null,
                    'max_los' => $u['max_los'] ?? null,
                    'cta' => $u['cta'] ?? null,
                    'ctd' => $u['ctd'] ?? null,
                    'closed' => $u['closed'] ?? null,
                ], fn ($v) => $v !== null));
            $count++;
        }
        return response()->json(['updated' => $count]);
    }
}
