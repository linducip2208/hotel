<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestProfile;
use App\Models\RfmSegmentRule;
use App\Services\Guest\RfmSegmentationService;

class RfmController extends Controller
{
    public function index()
    {
        $property = app('current_property');
        $profiles = GuestProfile::whereHas('guest', function ($q) use ($property) {
            $q->whereHas('reservations', fn($r) => $r->where('property_id', $property->id));
        })->with('guest')->orderBy('rfm_score', 'desc')->limit(50)->get();

        $segments = RfmSegmentRule::where('property_id', $property->id)->get();
        $distribution = GuestProfile::whereNotNull('rfm_segment')
            ->whereHas('guest', fn($q) => $q->whereHas('reservations', fn($r) => $r->where('property_id', $property->id)))
            ->selectRaw('rfm_segment, count(*) as total')
            ->groupBy('rfm_segment')
            ->pluck('total', 'rfm_segment')
            ->toArray();

        return view('panel.rfm.index', compact('property', 'profiles', 'segments', 'distribution'));
    }

    public function calculate(RfmSegmentationService $service)
    {
        $property = app('current_property');
        $count = $service->calculateAll($property->id);
        return back()->with('success', "{$count} guest profiles berhasil di-segmentasi.");
    }

    public function segments()
    {
        $property = app('current_property');
        $rules = RfmSegmentRule::where('property_id', $property->id)->get();
        return view('panel.rfm.segments', compact('property', 'rules'));
    }

    public function storeSegment(Request $request)
    {
        $property = app('current_property');
        RfmSegmentRule::create(array_merge($request->all(), ['property_id' => $property->id]));
        return back()->with('success', 'Segment rule ditambahkan.');
    }

    public function updateSegment(Request $request, $id)
    {
        $rule = RfmSegmentRule::findOrFail($id);
        $rule->update($request->all());
        return back()->with('success', 'Segment rule diupdate.');
    }

    public function guestDetail($id)
    {
        $property = app('current_property');
        $guest = Guest::with('profile', 'reservations')->findOrFail($id);
        return view('panel.rfm.guest', compact('property', 'guest'));
    }
}
