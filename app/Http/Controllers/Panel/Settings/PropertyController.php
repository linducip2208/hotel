<?php

namespace App\Http\Controllers\Panel\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function edit()
    {
        return view('panel.settings.property', ['property' => app('current_property')]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string',
            'address_line1' => 'nullable|string',
            'city' => 'nullable|string',
            'province' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'star_rating' => 'nullable|integer|between:1,5',
            'check_in_time' => 'nullable',
            'check_out_time' => 'nullable',
        ]);
        app('current_property')->update($data);
        return back()->with('status', 'Property updated.');
    }
}
