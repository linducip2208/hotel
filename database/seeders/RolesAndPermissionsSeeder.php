<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'fo.reservation.create','fo.reservation.cancel','fo.reservation.check_in','fo.reservation.check_out','fo.reservation.move_room',
            'fo.folio.read','fo.folio.charge','fo.folio.payment','fo.folio.discount','fo.folio.discount_above_threshold','fo.folio.refund',
            'fo.night_audit.run',
            'hk.room_status.update','hk.task.assign','hk.task.update','hk.lost_found.read','hk.lost_found.create',
            'pos.order.create','pos.order.update','pos.order.void','pos.order.settle','pos.menu.manage',
            'channel.connect','channel.mapping.edit','channel.rates.update','channel.conflicts.resolve',
            'acc.coa.read','acc.coa.edit','acc.journal.read','acc.journal.post','acc.journal.void',
            'acc.ar.read','acc.ar.create','acc.ap.read','acc.ap.create','acc.ap.approve_high',
            'acc.period.close','acc.period.unlock',
            'report.financial.read','report.operations.read','report.export',
            'sett.property.edit','sett.tax.edit','sett.user.create','sett.user.edit',
            'sett.integration.read','sett.integration.edit_secret',
            'sett.license.read','sett.license.migrate',
            'audit.read',
            'guest.read','guest.edit','guest.export_pii','guest.forget',
            'api.token.manage','webhook.manage',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $roles = [
            'super_owner' => $permissions, // all
            'manager' => array_filter($permissions, fn ($p) => ! str_starts_with($p, 'sett.user.')),
            'front_office' => array_filter($permissions, fn ($p) =>
                str_starts_with($p, 'fo.') || str_starts_with($p, 'guest.read') || $p === 'guest.edit'),
            'cashier' => ['fo.folio.read','fo.folio.payment'],
            'housekeeping' => array_filter($permissions, fn ($p) => str_starts_with($p, 'hk.')),
            'pos_server' => ['pos.order.create','pos.order.update'],
            'pos_cashier' => ['pos.order.create','pos.order.update','pos.order.settle'],
            'accountant' => array_filter($permissions, fn ($p) => str_starts_with($p, 'acc.') || str_starts_with($p, 'report.')),
            'auditor' => array_filter($permissions, fn ($p) => str_ends_with($p, '.read') || str_starts_with($p, 'audit.')),
            'sales_marketing' => ['guest.read','report.operations.read'],
            'it_admin' => array_filter($permissions, fn ($p) => str_starts_with($p, 'sett.') || str_starts_with($p, 'api.') || str_starts_with($p, 'webhook.')),
        ];

        foreach ($roles as $name => $perms) {
            $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            $role->syncPermissions(array_values($perms));
        }
    }
}
