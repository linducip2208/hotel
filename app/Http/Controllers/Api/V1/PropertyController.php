<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    private function property(): Property
    {
        return app('current_property') ?? Property::orderBy('id')->firstOrFail();
    }

    public function index()
    {
        return response()->json($this->property());
    }

    public function show(int $id)
    {
        $property = $this->property();

        abort_if($property->id !== $id, 403);

        return response()->json($property);
    }

    public function update(Request $request, int $id)
    {
        $property = $this->property();

        abort_if($property->id !== $id, 403);

        $validated = $request->validate([
            'name'             => 'sometimes|string|max:150',
            'legal_name'       => 'nullable|string|max:191',
            'email'            => 'nullable|email|max:191',
            'phone'            => 'nullable|string|max:30',
            'address_line1'    => 'nullable|string|max:191',
            'city'             => 'nullable|string|max:100',
            'country'          => 'nullable|string|size:2',
            'timezone'         => 'nullable|string|max:60',
            'currency'         => 'nullable|string|size:3',
            'check_in_time'    => 'nullable|date_format:H:i',
            'check_out_time'   => 'nullable|date_format:H:i',
            'star_rating'      => 'nullable|integer|between:1,5',
            'default_language' => 'nullable|string|max:10',
            'logo_url'         => 'nullable|url|max:500',
            'settings'         => 'nullable|array',
        ]);

        $property->update($validated);

        return response()->json($property->fresh());
    }
}
