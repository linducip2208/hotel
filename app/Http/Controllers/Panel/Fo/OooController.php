<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\OutOfOrderPeriod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OooController extends Controller
{
    public function index()
    {
        $periods = OutOfOrderPeriod::where('property_id', app('current_property')->id)
            ->with('room')->latest('from_date')->paginate(50);
        return view('panel.fo.ooo', compact('periods'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id' => 'required|integer',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string',
            'description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, $request) {
            $room = \App\Models\Room::where('property_id', app('current_property')->id)->findOrFail($data['room_id']);
            $period = OutOfOrderPeriod::create($data + [
                'property_id' => app('current_property')->id,
                'created_by_user_id' => $request->user()?->id,
                'status' => 'active',
            ]);
            $room->update(['hk_status' => 'out_of_order']);

            // Increment inventory.out_of_order per night
            $cursor = Carbon::parse($data['from_date']);
            $end = Carbon::parse($data['to_date']);
            while ($cursor->lte($end)) {
                Inventory::firstOrCreate(
                    ['property_id' => app('current_property')->id, 'room_type_id' => $room->room_type_id, 'date' => $cursor->toDateString()],
                    ['total' => 0, 'sold' => 0, 'blocked' => 0, 'out_of_order' => 0]
                )->increment('out_of_order');
                $cursor->addDay();
            }
        });
        return back();
    }

    public function clear(int $id)
    {
        $period = OutOfOrderPeriod::where('property_id', app('current_property')->id)->findOrFail($id);
        DB::transaction(function () use ($period) {
            $period->update(['status' => 'cleared', 'cleared_at' => now()]);
            $period->room?->update(['hk_status' => 'dirty']);

            $cursor = $period->from_date->copy();
            while ($cursor->lte($period->to_date)) {
                Inventory::where([
                    'property_id' => $period->property_id,
                    'room_type_id' => $period->room->room_type_id,
                    'date' => $cursor->toDateString(),
                ])->where('out_of_order', '>', 0)->decrement('out_of_order');
                $cursor->addDay();
            }
        });
        return back();
    }
}
