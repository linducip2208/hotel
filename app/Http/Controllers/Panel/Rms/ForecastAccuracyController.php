<?php

namespace App\Http\Controllers\Panel\Rms;

use App\Http\Controllers\Controller;
use App\Services\Rms\ForecastAccuracyService;
use Illuminate\Http\Request;

class ForecastAccuracyController extends Controller
{
    public function __construct(protected ForecastAccuracyService $service) {}

    public function index(Request $request)
    {
        $property = app('current_property');
        $days = min((int) ($request->input('days', 30)), 90);

        $data = $this->service->calculate($property, $days);

        return view('panel.rms.forecast-accuracy', array_merge(
            compact('property', 'days'),
            $data
        ));
    }
}
