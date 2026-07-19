<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Services\Revenue\DynamicPackageService;
use Illuminate\Http\Request;

class DynamicPackageController extends Controller
{
    public function index()
    {
        $property = app('current_property');
        $packages = Package::where('property_id', $property->id)
            ->where('is_active', true)
            ->with('items')
            ->get();
        return view('panel.packages.index', compact('property', 'packages'));
    }

    public function builder(DynamicPackageService $service, $id)
    {
        $property = app('current_property');
        $package = Package::with('items')->findOrFail($id);
        $options = $service->getDynamicOptions($id);
        $priceRange = $service->calculatePriceRange($id);

        return view('panel.packages.builder', compact('property', 'package', 'options', 'priceRange'));
    }

    public function customize(Request $request, DynamicPackageService $service, $id)
    {
        $customizations = $request->input('customizations', []);
        $result = $service->buildPackage($id, $customizations);

        return response()->json($result);
    }

    public function attach(Request $request, DynamicPackageService $service, $id, $reservationId)
    {
        $customizations = $request->input('customizations', []);
        $rp = $service->attachToReservation($reservationId, $id, $customizations);

        return redirect()->route('panel.fo.reservations.show', $reservationId)
            ->with('success', 'Package berhasil ditambahkan ke reservasi.');
    }
}
