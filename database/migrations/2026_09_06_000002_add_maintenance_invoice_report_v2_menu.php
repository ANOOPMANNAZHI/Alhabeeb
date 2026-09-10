<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds "Maintenance Invoice Report v2" under Maintenance Module -> Reports
 * (the same parent menu as the existing "Maintenance" and "Service Report"
 * v1 reports), following the pattern used by 2026_09_02_000001 /
 * 2026_09_06_000001 for the other v2 reports added this session.
 */
class AddMaintenanceInvoiceReportV2Menu extends Migration
{
    public function up()
    {
        $v1Menu = DB::table('menu')
            ->where('route_name', 'showMaintenanceReport')
            ->first();

        $parentMenuId = $v1Menu ? $v1Menu->parent_menu : 0;

        $orderNext = DB::table('menu')
            ->where('parent_menu', $parentMenuId)
            ->count() + 1;

        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Maintenance Invoice Report v2',
            'menu_icon'   => 'fa-university',
            'route_name'  => 'showMaintenanceInvoiceReportV2',
            'url_key'     => 'showMaintenanceInvoiceReportV2',
            'parent_menu' => $parentMenuId,
            'menutype'    => 2,
            'menu_order'  => $orderNext,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        DB::table('permissions')->insert([
            'name'       => 'view_maintenance_invoice_report_v2',
            'guard_name' => 'web',
            'menu_id'    => $menuId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $permission = DB::table('permissions')
            ->where('name', 'view_maintenance_invoice_report_v2')
            ->first();

        if ($permission) {
            $roles = DB::table('roles')->pluck('id');
            foreach ($roles as $roleId) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permission->id,
                    'role_id'       => $roleId,
                ]);
            }
        }

        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        $menu = DB::table('menu')
            ->where('route_name', 'showMaintenanceInvoiceReportV2')
            ->first();

        if ($menu) {
            $permission = DB::table('permissions')
                ->where('menu_id', $menu->id)
                ->first();

            if ($permission) {
                DB::table('role_has_permissions')
                    ->where('permission_id', $permission->id)
                    ->delete();

                DB::table('permissions')
                    ->where('id', $permission->id)
                    ->delete();
            }

            DB::table('menu')->where('id', $menu->id)->delete();
        }

        app('cache')->forget('spatie.permission.cache');
    }
}
