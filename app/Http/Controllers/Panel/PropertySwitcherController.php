<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertySwitcherController extends Controller
{
    public function switch(Request $request, int $id)
    {
        $user = $request->user();
        $prop = Property::findOrFail($id);

        // Owner-level can switch any; staff scoped to assigned property
        if ($user->property_id && $user->property_id !== $prop->id && ! $user->hasRole(['super_owner', 'manager'])) {
            abort(403, 'Anda tidak punya akses ke property ini.');
        }

        session(['current_property_id' => $prop->id]);
        return back()->with('status', "Switched to {$prop->name}");
    }
}
