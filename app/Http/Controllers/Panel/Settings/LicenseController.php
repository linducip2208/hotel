<?php

namespace App\Http\Controllers\Panel\Settings;

use App\Http\Controllers\Controller;
use App\Models\LocalLicense;
use App\Services\License\LicenseManager;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function show(LicenseManager $manager)
    {
        $local = LocalLicense::current();
        $status = $manager->status();
        return view('panel.settings.license', compact('local', 'status'));
    }

    public function refresh(LicenseManager $manager)
    {
        $manager->heartbeat();
        return back();
    }

    public function migrate(Request $request, LicenseManager $manager)
    {
        // Stub: redirect to wizard with migrate flag
        return redirect('/setup/wizard?migrate=1');
    }
}
