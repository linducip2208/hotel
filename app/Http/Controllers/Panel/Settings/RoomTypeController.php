<?php

namespace App\Http\Controllers\Panel\Settings;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoomTypeController extends Controller
{
    public function index()
    {
        $roomTypes = RoomType::where('property_id', app('current_property')->id)
            ->orderBy('display_order')->get();
        return view('panel.settings.room-types.index', compact('roomTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'code'          => 'required|string|max:50|unique:room_types,code',
            'base_rate'     => 'required|numeric|min:0',
            'max_occupancy' => 'required|integer|min:1|max:20',
            'max_adults'    => 'nullable|integer|min:1|max:20',
            'max_children'  => 'nullable|integer|min:0|max:20',
            'size_sqm'      => 'nullable|numeric|min:0',
            'view'          => 'nullable|string|max:255',
            'bed_config'    => 'nullable|string|max:255',
            'description'   => 'nullable|string|max:1000',
            'amenities'     => 'nullable|array',
            'display_order' => 'nullable|integer|min:0',
        ]);
        $data['property_id'] = app('current_property')->id;
        $data['is_active']   = true;
        $data['slug']        = Str::slug($data['name'] . '-' . app('current_property')->id);
        $data['amenities']   = $request->amenities ?? [];
        RoomType::create($data);
        return back()->with('success', 'Tipe kamar berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $rt = RoomType::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'base_rate'     => 'required|numeric|min:0',
            'max_occupancy' => 'required|integer|min:1|max:20',
            'max_adults'    => 'nullable|integer|min:1|max:20',
            'max_children'  => 'nullable|integer|min:0|max:20',
            'size_sqm'      => 'nullable|numeric|min:0',
            'view'          => 'nullable|string|max:255',
            'bed_config'    => 'nullable|string|max:255',
            'description'   => 'nullable|string|max:1000',
            'amenities'     => 'nullable|array',
            'display_order' => 'nullable|integer|min:0',
            'is_active'     => 'boolean',
        ]);
        $data['amenities'] = $request->amenities ?? [];
        $rt->update($data);
        return back()->with('success', 'Tipe kamar diperbarui.');
    }

    public function destroy($id)
    {
        $rt = RoomType::where('property_id', app('current_property')->id)->findOrFail($id);
        $rt->delete();
        return back()->with('success', 'Tipe kamar dihapus.');
    }
}
