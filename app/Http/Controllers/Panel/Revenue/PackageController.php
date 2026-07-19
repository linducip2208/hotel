<?php

namespace App\Http\Controllers\Panel\Revenue;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageItem;
use App\Models\Reservation;
use App\Models\ReservationPackage;
use App\Services\Revenue\PackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function __construct(protected PackageService $packageService) {}

    public function index()
    {
        $property = app('current_property');
        $packages = Package::where('property_id', $property->id)
            ->with('items')
            ->withCount('items')
            ->orderBy('display_order')
            ->get();

        return view('panel.revenue.packages', compact('packages', 'property'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'min_nights' => 'nullable|integer|min:1',
            'max_nights' => 'nullable|integer|min:1',
            'image_url' => 'nullable|url|max:500',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $property = app('current_property');

        Package::create([
            'property_id' => $property->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::random(6),
            'description' => $validated['description'] ?? null,
            'base_price' => $validated['base_price'],
            'min_nights' => $validated['min_nights'] ?? 1,
            'max_nights' => $validated['max_nights'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => true,
        ]);

        return redirect()->route('panel.packages.index')->with('success', 'Paket berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $package = Package::where('property_id', app('current_property')->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'min_nights' => 'nullable|integer|min:1',
            'max_nights' => 'nullable|integer|min:1',
            'image_url' => 'nullable|url|max:500',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $package->update($validated);

        return redirect()->route('panel.packages.index')->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $package = Package::where('property_id', app('current_property')->id)->findOrFail($id);
        $package->delete();

        return back()->with('success', 'Paket berhasil dihapus.');
    }

    public function addItem(Request $request, $packageId)
    {
        $package = Package::where('property_id', app('current_property')->id)->findOrFail($packageId);

        $validated = $request->validate([
            'item_type' => 'required|string|max:50',
            'reference_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'is_included' => 'boolean',
        ]);

        PackageItem::create([
            'package_id' => $package->id,
            'item_type' => $validated['item_type'],
            'reference_id' => $validated['reference_id'] ?? null,
            'name' => $validated['name'],
            'quantity' => $validated['quantity'] ?? 1,
            'unit_price' => $validated['unit_price'] ?? 0,
            'is_included' => $validated['is_included'] ?? true,
        ]);

        return back()->with('success', 'Item ditambahkan ke paket.');
    }

    public function removeItem($id)
    {
        $item = PackageItem::query()
            ->whereHas('package', fn ($q) => $q->where('property_id', app('current_property')->id))
            ->findOrFail($id);
        $item->delete();

        return back()->with('success', 'Item dihapus dari paket.');
    }

    public function reservationPackages($reservationId)
    {
        $property = app('current_property');
        $reservation = Reservation::where('property_id', $property->id)->findOrFail($reservationId);
        $attachedPackages = ReservationPackage::where('reservation_id', $reservation->id)
            ->with(['package', 'folioCharge'])
            ->get();
        $availablePackages = Package::where('property_id', $property->id)
            ->where('is_active', true)
            ->with('items')
            ->orderBy('display_order')
            ->get();

        return view('panel.revenue.packages-reservation', compact('reservation', 'attachedPackages', 'availablePackages'));
    }

    public function attachToReservation(Request $request, $reservationId, $packageId)
    {
        $property = app('current_property');
        $reservation = Reservation::where('property_id', $property->id)->findOrFail($reservationId);
        $package = Package::where('property_id', $property->id)->findOrFail($packageId);

        $this->packageService->attachToReservation($reservation, $package);

        return back()->with('success', "Paket \"{$package->name}\" berhasil ditambahkan ke reservasi.");
    }

    public function detachFromReservation($reservationId, $rpId)
    {
        $rp = ReservationPackage::where('reservation_id', $reservationId)->findOrFail($rpId);
        $this->packageService->detachFromReservation($rp);

        return back()->with('success', 'Paket berhasil dilepas dari reservasi.');
    }
}
