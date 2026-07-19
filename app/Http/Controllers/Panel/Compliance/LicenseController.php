<?php

namespace App\Http\Controllers\Panel\Compliance;

use App\Http\Controllers\Controller;
use App\Models\PropertyLicense;
use App\Services\Compliance\LicenseService;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function __construct(protected LicenseService $service) {}

    public function index()
    {
        $licenses = $this->service->list(app('current_property'));
        return view('panel.compliance.licenses', compact('licenses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'license_name' => 'required|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'issuing_authority' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'renewal_reminder_days' => 'nullable|integer|min:1|max:365',
            'notes' => 'nullable|string',
        ]);
        $this->service->create(app('current_property'), $data);
        return back()->with('success', 'Izin berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $license = PropertyLicense::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'license_name' => 'required|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'issuing_authority' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'renewal_reminder_days' => 'nullable|integer|min:1|max:365',
            'notes' => 'nullable|string',
        ]);
        $this->service->update($license, $data);
        return back()->with('success', 'Izin berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $license = PropertyLicense::where('property_id', app('current_property')->id)->findOrFail($id);
        $this->service->delete($license);
        return back()->with('success', 'Izin berhasil dihapus.');
    }

    public function uploadDoc(Request $request, $id)
    {
        $license = PropertyLicense::where('property_id', app('current_property')->id)->findOrFail($id);
        $request->validate(['document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240']);
        $path = $request->file('document')->store('licenses', 'public');
        $license->update(['document_path' => $path]);
        return back()->with('success', 'Dokumen berhasil diunggah.');
    }
}
