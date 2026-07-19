<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\RateOverride;
use App\Models\RoomType;
use App\Services\Pricing\OpenPricingService;
use Illuminate\Http\Request;

class OpenPricingController extends Controller
{
    public function __construct(protected OpenPricingService $pricing) {}

    public function effective(Request $request)
    {
        $request->validate([
            'room_type_id' => 'required|integer',
            'channel_id'   => 'nullable|integer',
            'date'         => 'required|date_format:Y-m-d',
        ]);

        $property   = $request->user()->property;
        $roomTypeId = $request->integer('room_type_id');
        $channelId  = $request->integer('channel_id');
        $date       = $request->input('date');

        return response()->json(
            $this->pricing->effectivePrice($property->id, $roomTypeId, $channelId, $date)
        );
    }

    public function grid(Request $request)
    {
        $request->validate([
            'room_type_id' => 'required|integer',
            'channel_id'   => 'nullable|integer',
            'from'         => 'required|date_format:Y-m-d',
            'to'           => 'required|date_format:Y-m-d|after_or_equal:from',
        ]);

        $property  = $request->user()->property;
        $roomType  = RoomType::where('property_id', $property->id)->findOrFail($request->integer('room_type_id'));
        $channel   = $request->filled('channel_id') ? Channel::where('property_id', $property->id)->findOrFail($request->integer('channel_id')) : null;

        $grid = $this->pricing->availabilityGrid($property, $roomType, $channel, $request->from, $request->to);

        return response()->json($grid);
    }

    public function bulkUpsert(Request $request)
    {
        $request->validate([
            'overrides'                 => 'required|array|min:1|max:365',
            'overrides.*.room_type_id'  => 'required|integer',
            'overrides.*.channel_id'    => 'nullable|integer',
            'overrides.*.date'          => 'required|date_format:Y-m-d',
            'overrides.*.price'         => 'required|numeric|min:0',
            'overrides.*.min_stay'      => 'nullable|integer|min:1',
            'overrides.*.stop_sell'     => 'nullable|boolean',
            'overrides.*.closed_to_arrival' => 'nullable|boolean',
        ]);

        $property = $request->user()->property;
        $count    = $this->pricing->bulkUpsert($property, $request->input('overrides'));

        return response()->json(['upserted' => $count]);
    }

    public function destroy(Request $request, int $id)
    {
        $property = $request->user()->property;
        RateOverride::where('property_id', $property->id)->findOrFail($id)->delete();
        return response()->noContent();
    }
}
