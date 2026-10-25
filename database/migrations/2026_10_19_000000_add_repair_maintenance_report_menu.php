<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds "Repair & Maintenance Report" under Maintenance Module -> Reports (next to Maintenance Invoice Report v2),
 * with permission view_repair_maintenance_report granted to super_admin and the maintenance roles only.
 */
class AddRepairMaintenanceReportMenu extends Migration
{
    const ROUTE = 'showRepairMaintenanceReport';
    const PERMISSION = 'view_repair_maintenance_report';
    const ROLES = ['super_admin', 'maintenance_manager', 'facility_manager', 'maintenance_supervisor',
        'maintenance_coordinator', 'maintenance_executive', 'maintenance_engineer', 'maintenance_contracts_executive'];

    public function up()
    {
        if (DB::table('menu')->where('route_name', self::ROUTE)->exists()) {
            return;
        }
        $sibling = DB::table('menu')->where('route_name', 'showMaintenanceInvoiceReportV2')->first()
            ?: DB::table('menu')->where('route_name', 'showMaintenanceReport')->first();
        $parentMenuId = $sibling ? $sibling->parent_menu : 0;

        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Repair & Maintenance Report',
            'menu_icon'   => 'fa-wrench',
            'route_name'  => self::ROUTE,
            'url_key'     => self::ROUTE,
            'parent_menu' => $parentMenuId,
            'menutype'    => 2,
            'menu_order'  => DB::table('menu')->where('parent_menu', $parentMenuId)->count() + 1,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $permissionId = DB::table('permissions')->insertGetId([
            'name'       => self::PERMISSION,
            'guard_name' => 'web',
            'menu_id'    => $menuId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (DB::table('roles')->whereIn('name', self::ROLES)->pluck('id') as $roleId) {
            DB::table('role_has_permissions')->insert(['permission_id' => $permissionId, 'role_id' => $roleId]);
        }

        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        $menu = DB::table('menu')->where('route_name', self::ROUTE)->first();
        $permission = DB::table('permissions')->where('name', self::PERMISSION)->first();
        if ($permission) {
            DB::table('role_has_permissions')->where('permission_id', $permission->id)->delete();
            DB::table('model_has_permissions')->where('permission_id', $permission->id)->delete();
            DB::table('permissions')->where('id', $permission->id)->delete();
        }
        if ($menu) {
            DB::table('menu')->where('id', $menu->id)->delete();
        }
        app('cache')->forget('spatie.permission.cache');
    }
}
