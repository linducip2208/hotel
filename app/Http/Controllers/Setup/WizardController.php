<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\LocalLicense;
use App\Models\Property;
use App\Models\User;
use App\Services\License\LicenseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WizardController extends Controller
{
    public function show(Request $request)
    {
        $local = LocalLicense::current();

        // Marketplace pairing replaces the legacy LocalLicense check; once
        // property + admin are configured, the wizard is done.
        if (Property::exists() && User::exists()) {
            return redirect('/panel');
        }

        return view('setup.wizard', [
            'step' => $this->currentStep($local),
            'local' => $local,
        ]);
    }

    protected function currentStep(?LocalLicense $local): string
    {
        // Marketplace pairing (whitelabel.co.id, kit v3) is enforced upstream by
        // RequirePair middleware, so by this point activation is already valid
        // (or dev-bypassed). Skip the legacy HMS-format pair step.
        if (! Property::exists()) return 'property';
        if (! User::exists()) return 'admin';
        return 'done';
    }

    public function connectionCheck(Request $request)
    {
        $checks = [
            'database' => $this->safeCheck(fn () => DB::connection()->getPdo() !== null),
            'storage' => $this->safeCheck(fn () => is_writable(storage_path())),
            'vendor_server' => $this->safeCheck(function () {
                $client = new \GuzzleHttp\Client(['timeout' => 5, 'http_errors' => false]);
                $r = $client->get(rtrim(config('license.vendor_base_url'), '/').'/health');
                return $r->getStatusCode() < 500;
            }),
        ];
        return response()->json(['checks' => $checks, 'all_ok' => ! in_array(false, $checks, true)]);
    }

    public function pair(Request $request, LicenseManager $manager)
    {
        $request->validate([
            'license_key' => ['required', 'string', 'regex:/^HMS(-[A-Z0-9]{5}){4}$/i'],
        ]);

        $result = $manager->pair(strtoupper($request->input('license_key')));
        if (! $result['ok']) {
            return back()->withErrors(['license_key' => $result['message'] ?? 'Pairing failed.']);
        }

        return redirect()->route('setup.wizard');
    }

    public function property(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'region_code' => ['required', 'string', 'max:32'],
            'province' => ['nullable', 'string', 'max:64'],
            'city' => ['nullable', 'string', 'max:64'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'total_rooms' => ['required', 'integer', 'min:1'],
            'star_rating' => ['nullable', 'integer', 'between:1,5'],
            'is_pkp' => ['nullable', 'boolean'],
            'npwp' => ['nullable', 'string', 'max:32'],
        ]);

        Property::firstOrCreate(['name' => $data['name']], $data);
        return redirect()->route('setup.wizard');
    }

    public function createAdmin(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $property = Property::orderBy('id')->first();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'property_id' => $property?->id,
            'is_active' => true,
        ]);

        $user->assignRole('super_owner');

        return redirect()->route('setup.wizard.done');
    }

    public function done()
    {
        return view('setup.done');
    }

    protected function safeCheck(\Closure $fn): bool
    {
        try { return (bool) $fn(); }
        catch (\Throwable $e) { return false; }
    }
}
