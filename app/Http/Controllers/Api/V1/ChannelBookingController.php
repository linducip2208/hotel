<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Reservation;

class ChannelBookingController extends Controller
{
    private function property(): Property
    {
        return app('current_property') ?? Property::orderBy('id')->firstOrFail();
    }

    public function index()
    {
        $list = Reservation::where('property_id', $this->property()->id)
            ->where('source', 'like', 'ota:%')
            ->latest()
            ->limit(100)
            ->get();

        return response()->json(['data' => $list]);
    }
}
