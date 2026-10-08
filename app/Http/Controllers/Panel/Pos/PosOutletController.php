<?php

namespace App\Http\Controllers\Panel\Pos;

use App\Http\Controllers\Controller;
use App\Models\PosOutlet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PosOutletController extends Controller
{
    public function index()
    {
        $outlets = PosOutlet::where('property_id', app('current_property')->id)
            ->withCount(['tables', 'menuItems'])
            ->orderBy('name')
            ->paginate(25);

        return view('panel.pos.outlets.index', compact('outlets'));
    }

    public function create()
    {
        return view('panel.pos.outlets.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        PosOutlet::create($data + [
            'property_id' => app('current_property')->id,
            'code' => Str::upper(Str::slug($data['name'])).'-'.Str::upper(Str::random(3)),
        ]);

        return redirect()->route('panel.pos.outlets.index')
            ->with('success', 'Outlet berhasil dibuat.');
    }

    public function edit(int $id)
    {
        $outlet = PosOutlet::where('property_id', app('current_property')->id)->findOrFail($id);

        return view('panel.pos.outlets.edit', compact('outlet'));
    }

    public function update(Request $request, int $id)
    {
        $outlet = PosOutlet::where('property_id', app('current_property')->id)->findOrFail($id);
        $outlet->update($this->validated($request, $outlet->id));

        return redirect()->route('panel.pos.outlets.index')->with('success', 'Outlet diperbarui.');
    }

    public function toggle(Request $request, int $id)
    {
        $outlet = PosOutlet::where('property_id', app('current_property')->id)->findOrFail($id);
        $outlet->update(['is_active' => ! $outlet->is_active]);

        return back()->with('success', $outlet->is_active ? 'Outlet diaktifkan.' : 'Outlet dinonaktifkan.');
    }

    public function destroy(Request $request, int $id)
    {
        $outlet = PosOutlet::where('property_id', app('current_property')->id)
            ->withCount('orders')
            ->findOrFail($id);

        // Outlets with order history must be preserved for accounting integrity.
        if ($outlet->orders_count > 0) {
            $outlet->update(['is_active' => false]);

            return back()->with('warning', "Outlet memiliki {$outlet->orders_count} order — dinonaktifkan, bukan dihapus.");
        }

        $outlet->delete();

        return back()->with('success', 'Outlet dihapus.');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:restaurant,bar,spa,minibar,room_service,other'],
            'charge_to_room_enabled' => ['nullable', 'boolean'],
            'takeaway_enabled' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
