<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Tenancy\TenantDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function __construct(
        private TenantDatabaseManager $dbManager
    ) {}

    public function index()
    {
        $tenants = Tenant::with(['plan', 'domains'])
            ->latest()
            ->paginate(50);

        return view('admin.tenants.index', compact('tenants'));
    }

    public function show(string $id)
    {
        $tenant = Tenant::with(['plan', 'domains', 'subscriptions', 'invoices'])
            ->findOrFail($id);

        return view('admin.tenants.show', compact('tenant'));
    }

    public function create()
    {
        $plans = Plan::where('is_active', true)->orderBy('display_order')->get();

        return view('admin.tenants.create', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:64', 'unique:tenants,slug'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'max_rooms' => ['nullable', 'integer', 'min:1'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'trial_ends_at' => ['nullable', 'date'],
        ]);

        $tenant = Tenant::create([
            ...$validated,
            'status' => $request->boolean('activate_immediately') ? 'active' : 'trial',
            'trial_ends_at' => $validated['trial_ends_at'] ?? now()->addDays(14),
            'current_period_ends_at' => $validated['trial_ends_at'] ?? now()->addDays(14),
        ]);

        if ($request->boolean('provision_now')) {
            try {
                $this->dbManager->provision($tenant);
            } catch (\Throwable $e) {
                return back()->with('warning', 'Tenant created but provisioning failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', __('Tenant created successfully.'));
    }

    public function edit(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $plans = Plan::where('is_active', true)->orderBy('display_order')->get();

        return view('admin.tenants.edit', compact('tenant', 'plans'));
    }

    public function update(Request $request, string $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:64', 'unique:tenants,slug,' . $tenant->id],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'max_rooms' => ['nullable', 'integer', 'min:1'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:trial,active,suspended,churned'],
            'trial_ends_at' => ['nullable', 'date'],
            'current_period_ends_at' => ['nullable', 'date'],
        ]);

        $tenant->update($validated);

        return back()->with('success', __('Tenant updated.'));
    }

    public function destroy(string $id)
    {
        $tenant = Tenant::findOrFail($id);

        if ($tenant->database_name) {
            try {
                $this->dbManager->destroy($tenant);
            } catch (\Throwable $e) {
                return back()->with('error', 'Failed to destroy database: ' . $e->getMessage());
            }
        }

        $tenant->logEvent('deleted');
        $tenant->delete();

        return redirect()->route('admin.tenants.index')
            ->with('success', __('Tenant and database destroyed.'));
    }

    public function suspend(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update([
            'status' => 'suspended',
            'suspended_at' => now(),
        ]);
        $tenant->logEvent('suspended');

        return back()->with('success', __('Tenant suspended.'));
    }

    public function resume(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update([
            'status' => 'active',
            'suspended_at' => null,
        ]);
        $tenant->logEvent('resumed');

        return back()->with('success', __('Tenant reactivated.'));
    }

    public function provision(string $id)
    {
        $tenant = Tenant::findOrFail($id);

        try {
            $this->dbManager->provision($tenant);
        } catch (\Throwable $e) {
            return back()->with('error', 'Provisioning failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Tenant database provisioned: ' . $tenant->database_name);
    }

    public function impersonate(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        session(['impersonating_tenant_id' => $tenant->id]);

        return redirect('/panel')
            ->with('status', "Impersonating {$tenant->company_name}");
    }
}
