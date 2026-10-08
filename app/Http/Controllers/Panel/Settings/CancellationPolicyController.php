<?php

namespace App\Http\Controllers\Panel\Settings;

use App\Http\Controllers\Controller;
use App\Models\CancellationPolicy;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CancellationPolicyController extends Controller
{
    public function index()
    {
        $propertyId = app('current_property')->id;
        $policies = CancellationPolicy::where('property_id', $propertyId)
            ->withCount('ratePlans')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('panel.settings.cancellation-policies', compact('policies'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        CancellationPolicy::create($data + [
            'property_id' => app('current_property')->id,
            'code' => Str::slug($data['name']).'-'.Str::random(4),
            'is_active' => true,
        ]);

        return back()->with('success', 'Policy berhasil dibuat.');
    }

    public function update(Request $request, int $id)
    {
        $policy = CancellationPolicy::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $this->validated($request);

        $policy->update($data);

        return back()->with('success', 'Policy berhasil diperbarui.');
    }

    public function duplicate(Request $request, int $id)
    {
        $policy = CancellationPolicy::where('property_id', app('current_property')->id)->findOrFail($id);

        $copy = $policy->replicate();
        $copy->name = $policy->name.' (Copy)';
        $copy->code = Str::slug($copy->name).'-'.Str::random(4);
        $copy->is_default = false;
        $copy->is_active = false;
        $copy->save();

        app(AuditLogger::class)->record('cancellation_policy.duplicated', $copy, ['from_policy_id' => $policy->id]);

        return back()->with('success', 'Policy diduplikasi sebagai draft non-aktif.');
    }

    public function toggle(Request $request, int $id)
    {
        $policy = CancellationPolicy::where('property_id', app('current_property')->id)->findOrFail($id);
        $policy->update(['is_active' => ! $policy->is_active]);

        app(AuditLogger::class)->record('cancellation_policy.toggled', $policy, ['is_active' => $policy->is_active]);

        return back()->with('success', $policy->is_active ? 'Policy diaktifkan.' : 'Policy dinonaktifkan.');
    }

    public function destroy(Request $request, int $id)
    {
        $policy = CancellationPolicy::where('property_id', app('current_property')->id)
            ->withCount('ratePlans')
            ->findOrFail($id);

        // Safe delete: still referenced by rate plans → deactivate instead.
        if ($policy->rate_plans_count > 0) {
            $policy->update(['is_active' => false]);

            return back()->with('warning', "Policy masih dipakai {$policy->rate_plans_count} rate plan — dinonaktifkan, bukan dihapus.");
        }

        $policy->delete();
        app(AuditLogger::class)->record('cancellation_policy.deleted', $policy, []);

        return back()->with('success', 'Policy dihapus.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'is_refundable' => 'nullable|boolean',
            'rules' => 'required|array|min:1',
            'rules.*.days_before' => 'required|integer|min:0',
            'rules.*.penalty_pct' => 'required|numeric|min:0|max:100',
            'display_text' => 'nullable|string|max:1000',
        ]);

        $data['is_refundable'] = $request->boolean('is_refundable');
        $data['rules'] = collect($data['rules'])
            ->map(fn ($r) => ['days_before' => (int) $r['days_before'], 'penalty_pct' => (float) $r['penalty_pct']])
            ->sortByDesc('days_before')
            ->values()
            ->all();

        return $data;
    }
}
