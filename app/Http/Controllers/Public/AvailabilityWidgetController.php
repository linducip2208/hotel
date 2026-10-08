<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;

class AvailabilityWidgetController extends Controller
{
    public function show(Request $request, AvailabilityService $service)
    {
        $month = $request->query('month');
        $calendar = $service->calendar(null, $month);

        return view('public.widgets.availability', [
            'calendar' => $calendar,
            'property' => app()->bound('current_property') ? app('current_property') : Property::orderBy('id')->first(),
        ]);
    }
}
