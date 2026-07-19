<?php

namespace App\Http\Controllers\Panel\Concierge;

use App\Http\Controllers\Controller;
use App\Models\PointOfInterest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PoiController extends Controller
{
    public function index()
    {
        $pois = PointOfInterest::where('property_id', app('current_property')->id)
            ->orWhereNull('property_id')->paginate(50);
        return view('panel.concierge.pois', compact('pois'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'category' => 'required|string',
            'city' => 'nullable|string',
            'description' => 'nullable|string',
            'distance_meters' => 'nullable|integer',
            'rating' => 'nullable|integer|between:1,5',
            'phone' => 'nullable|string',
            'website' => 'nullable|url',
        ]);
        PointOfInterest::create($data + [
            'property_id' => app('current_property')->id,
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
            'is_active' => true,
        ]);
        return back();
    }
}
