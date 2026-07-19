<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Public tenant signup flow (SaaS-mode only).
 * Standalone-mode: redirect to vendor sales contact.
 */
class TenantSignupController extends Controller
{
    public function show()
    {
        if (config('app.mode') !== 'saas') {
            return view('saas.signup-disabled');
        }
        $plans = Plan::where('is_active', true)->orderBy('display_order')->get();
        return view('saas.signup', compact('plans'));
    }

    public function store(Request $request)
    {
        if (config('app.mode') !== 'saas') abort(404);

        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:tenants,owner_email',
            'owner_phone' => 'nullable|string',
            'slug' => 'required|string|min:3|max:30|regex:/^[a-z0-9-]+$/|unique:tenants,slug',
            'plan_id' => 'required|integer|exists:plans,id',
        ]);

        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'slug' => $data['slug'],
            'company_name' => $data['company_name'],
            'owner_name' => $data['owner_name'],
            'owner_email' => $data['owner_email'],
            'owner_phone' => $data['owner_phone'] ?? null,
            'plan_id' => $data['plan_id'],
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
            'last_active_at' => now(),
        ]);

        TenantDomain::create([
            'tenant_id' => $tenant->id,
            'domain' => $data['slug'].'.hotelhub.id',
            'is_primary' => true,
            'is_verified' => true,
            'ssl_status' => 'active',
        ]);

        \App\Jobs\ProvisionTenantJob::dispatch($tenant->id);

        return view('saas.signup-success', compact('tenant'));
    }
}
