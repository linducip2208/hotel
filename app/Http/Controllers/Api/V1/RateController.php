<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Rate;
use Illuminate\Http\Request;

class RateController extends Controller
{
    public function index(Request $request)
    {
        $q = Rate::query();
        if ($id = $request->query('room_type_id')) $q->where('room_type_id', $id);
        if ($from = $request->query('from')) $q->where('date', '>=', $from);
        if ($to = $request->query('to')) $q->where('date', '<=', $to);
        return response()->json($q->paginate(200));
    }

    public function bulkUpdate(Request $request)
    {
        $count = 0;
        foreach ($request->input('updates', []) as $u) {
            Rate::updateOrCreate(
                ['property_id' => $u['property_id'], 'room_type_id' => $u['room_type_id'], 'rate_plan_id' => $u['rate_plan_id'], 'date' => $u['date']],
                $u
            );
            $count++;
        }
        return response()->json(['updated' => $count]);
    }
}
