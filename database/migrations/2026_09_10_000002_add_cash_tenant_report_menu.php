<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds "Cash Tenant Report" under PLM Module -> Reports, with its view
 * permission granted to every role.
 *
 * NOTE permissions.menu_id is NOT NULL with a cascade delete from menu, so the
 * menu row is always created before the permission.
 */
class AddCashTenantReportMenu extends Migration
{
    const ROUTE = 'showCashTenantReport';
    const PERMISSION = 'view_cash_tenant_report';

    public function up()
    {
        if (DB::table('menu')->where('route_name', self::ROUTE)->exists()) {
            return;
        }

        $plmModule = DB::table('menu')
            ->where('menu_name', 'PLM Module')
            ->where('parent_menu', 0)
            ->first();

        $reports = $plmModule
            ? DB::table('menu')
                ->where('menu_name', 'Reports')
                ->where('parent_menu', $plmModule->id)
                ->where('menutype', 1)
                ->first()
            : null;

        if (!$reports) {
            throw new \RuntimeException(
                'Could not find PLM Module -> Reports. Run: '
                . 'SELECT id, menu_name, parent_menu, menutype FROM menu WHERE menutype = 1 ORDER BY parent_menu;'
            );
        }

        $orderNext = DB::table('menu')->where('parent_menu', $reports->id)->count() + 1;

        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Cash Tenant Report',
            'menu_icon'   => 'fa-money',
            'route_name'  => self::ROUTE,
            'url_key'     => self::ROUTE,
            'parent_menu' => $reports->id,
            'menutype'    => 2,
            'menu_order'  => $orderNext,
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

        foreach (DB::table('roles')->pluck('id') as $roleId) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permissionId,
                'role_id'       => $roleId,
            ]);
        }

        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        $menu = DB::table('menu')->where('route_name', self::ROUTE)->first();

        if ($menu) {
            $permission = DB::table('permissions')->where('menu_id', $menu->id)->first();
            if ($permission) {
                DB::table('role_has_permissions')->where('permission_id', $permission->id)->delete();
                DB::table('permissions')->where('id', $permission->id)->delete();
            }
            DB::table('menu')->where('id', $menu->id)->delete();
        }

        app('cache')->forget('spatie.permission.cache');
    }
}
