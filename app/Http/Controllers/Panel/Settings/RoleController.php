<?php

namespace App\Http\Controllers\Panel\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    protected array $allPermissions = [
        'Front Office' => [
            'fo.reservation.create', 'fo.reservation.cancel', 'fo.reservation.check_in',
            'fo.reservation.check_out', 'fo.reservation.move_room',
            'fo.folio.read', 'fo.folio.charge', 'fo.folio.payment',
            'fo.folio.discount', 'fo.folio.discount_above_threshold', 'fo.folio.refund',
            'fo.night_audit.run',
        ],
        'Housekeeping' => [
            'hk.room_status.update', 'hk.task.assign', 'hk.task.update',
            'hk.lost_found.read', 'hk.lost_found.create',
        ],
        'POS' => [
            'pos.order.create', 'pos.order.update', 'pos.order.void',
            'pos.order.settle', 'pos.menu.manage',
        ],
        'Channel' => [
            'channel.connect', 'channel.mapping.edit', 'channel.rates.update', 'channel.conflicts.resolve',
        ],
        'Accounting' => [
            'acc.coa.read', 'acc.coa.edit', 'acc.journal.read', 'acc.journal.post', 'acc.journal.void',
            'acc.ar.read', 'acc.ar.create', 'acc.ap.read', 'acc.ap.create', 'acc.ap.approve_high',
            'acc.period.close', 'acc.period.unlock',
        ],
        'Reports' => [
            'report.financial.read', 'report.operations.read', 'report.export',
        ],
        'Settings' => [
            'sett.property.edit', 'sett.tax.edit', 'sett.user.create', 'sett.user.edit',
            'sett.integration.read', 'sett.integration.edit_secret',
            'sett.license.read', 'sett.license.migrate',
        ],
        'Audit' => [
            'audit.read',
        ],
        'Guest' => [
            'guest.read', 'guest.edit', 'guest.export_pii', 'guest.forget',
        ],
        'API & Webhooks' => [
            'api.token.manage', 'webhook.manage',
        ],
    ];

    public function index()
    {
        $property = app('current_property');
        $roles = Role::where('guard_name', 'web')->withCount('permissions')->get();
        return view('panel.settings.roles.index', compact('property', 'roles'));
    }

    public function create()
    {
        $property = app('current_property');
        $permissionGroups = $this->allPermissions;
        $existingPermissions = Permission::where('guard_name', 'web')->pluck('name')->toArray();
        return view('panel.settings.roles.create', compact('property', 'permissionGroups', 'existingPermissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('panel.settings.roles.index')
            ->with('success', "Role \"{$role->name}\" berhasil dibuat.");
    }

    public function edit($id)
    {
        $property = app('current_property');
        $role = Role::with('permissions')->findOrFail($id);
        $permissionGroups = $this->allPermissions;
        $assignedPermissions = $role->permissions->pluck('name')->toArray();

        return view('panel.settings.roles.edit', compact('property', 'role', 'permissionGroups', 'assignedPermissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        if (!in_array($role->name, ['super_owner', 'manager'])) {
            $request->validate([
                'name' => 'required|string|max:50|unique:roles,name,' . $id,
            ]);
            $role->update(['name' => $request->name]);
        }

        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions);

        return redirect()->route('panel.settings.roles.index')
            ->with('success', "Role \"{$role->name}\" berhasil diupdate.");
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, ['super_owner', 'manager'])) {
            return back()->with('error', 'Role sistem tidak bisa dihapus.');
        }

        $role->delete();
        return back()->with('success', "Role \"{$role->name}\" dihapus.");
    }
}
