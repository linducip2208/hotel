<?php

namespace App\Http\Controllers\Panel\Settings;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with('roomType')
            ->where('property_id', app('current_property')->id)
            ->orderBy('floor')->orderByRaw('LPAD(number, 10, "0")')
            ->get();
        $roomTypes = RoomType::where('property_id', app('current_property')->id)
            ->where('is_active', true)->orderBy('name')->get();
        return view('panel.settings.rooms.index', compact('rooms', 'roomTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'number'       => 'required|string|max:20',
            'floor'        => 'nullable|integer|min:0',
            'room_type_id' => 'required|exists:room_types,id',
            'notes'        => 'nullable|string|max:500',
        ]);
        $data['property_id'] = app('current_property')->id;
        $data['is_active']   = true;
        $data['fo_status']   = 'vacant';
        $data['hk_status']   = 'clean';
        Room::create($data);
        return back()->with('success', 'Kamar #' . $data['number'] . ' berhasil ditambahkan.');
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'prefix'       => 'required|string|max:5',
            'start'        => 'required|integer|min:1',
            'end'          => 'required|integer|gt:start|max:9999',
            'floor'        => 'required|integer|min:0',
            'room_type_id' => 'required|exists:room_types,id',
            'padding'      => 'nullable|integer|min:1|max:4',
        ]);

        $propertyId = app('current_property')->id;
        $padding    = $request->padding ?? 2;
        $count      = 0;
        for ($i = $request->start; $i <= $request->end; $i++) {
            Room::create([
                'property_id'  => $propertyId,
                'number'       => $request->prefix . str_pad($i, $padding, '0', STR_PAD_LEFT),
                'floor'        => $request->floor,
                'room_type_id' => $request->room_type_id,
                'is_active'    => true,
                'fo_status'    => 'vacant',
                'hk_status'    => 'clean',
            ]);
            $count++;
        }
        return back()->with('success', $count . ' kamar berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $room = Room::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'number'       => 'required|string|max:20',
            'floor'        => 'nullable|integer|min:0',
            'room_type_id' => 'required|exists:room_types,id',
            'is_active'    => 'boolean',
            'view'         => 'nullable|string|max:255',
            'notes'        => 'nullable|string|max:500',
        ]);
        $room->update($data);
        return back()->with('success', 'Kamar diperbarui.');
    }

    public function destroy($id)
    {
        $room = Room::where('property_id', app('current_property')->id)->findOrFail($id);
        $room->delete();
        return back()->with('success', 'Kamar #' . $room->number . ' dihapus.');
    }
}
