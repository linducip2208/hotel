<?php

namespace App\Http\Controllers\Panel\Guest;

use App\Http\Controllers\Controller;
use App\Services\Guest\CrossPropertyService;
use Illuminate\Http\Request;

class CrossPropertyController extends Controller
{
    public function __construct(protected CrossPropertyService $service) {}

    public function search(Request $request)
    {
        $results = [];
        if ($request->filled('q')) {
            $results = $this->service->searchAcrossProperties($request->q);
        }

        return view('panel.guests.cross-property', compact('results'));
    }

    public function profile(Request $request)
    {
        $guestIds = $request->get('ids', []);
        if (empty($guestIds)) {
            return redirect()->route('panel.guests.cross-property')->with('error', 'Pilih tamu terlebih dahulu.');
        }

        $profile = $this->service->getUnifiedProfile((array) $guestIds);
        return view('panel.guests.cross-property', ['profile' => $profile, 'results' => []]);
    }
}
