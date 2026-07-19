<?php

namespace App\Http\Controllers\Panel\Sustainability;

use App\Http\Controllers\Controller;
use App\Services\Sustainability\EnergyService;
use Illuminate\Http\Request;

class EnergyController extends Controller
{
    public function index(Request $request, EnergyService $service)
    {
        $dashboard = $service->getDashboard(app('current_property'));

        $year = $request->get('year', now()->year);
        $annualReport = $service->getAnnualReport(app('current_property'), (int) $year);

        return view('panel.sustainability.energy', array_merge($dashboard, [
            'annualReport' => $annualReport,
            'selectedYear' => $year,
        ]));
    }
}
