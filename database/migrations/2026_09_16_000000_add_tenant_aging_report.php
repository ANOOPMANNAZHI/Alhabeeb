<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTenantAgingReport extends Migration
{
    public function up()
    {
        // Menu entry next to Tenant Receivable v2 (same parent)
        $parentMenuId = DB::table('menu')->where('id', 183)->value('id') ?? 0;

        $orderNext = DB::table('menu')
            ->where('parent_menu', $parentMenuId)
            ->count() + 1;

        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Tenant Aging Report',
            'menu_icon'   => 'fa-hourglass-half',
            'route_name'  => 'showTenantAgingReport',
            'url_key'     => 'showTenantAgingReport',
            'parent_menu' => $parentMenuId,
            'menutype'    => 2,
            'menu_order'  => $orderNext,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Permission, granted to every role like the other v2 reports
        $permissionId = DB::table('permissions')->insertGetId([
            'name'       => 'view_tenant_aging_report',
            'guard_name' => 'web',
            'menu_id'    => $menuId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roles = DB::table('roles')->pluck('id');
        foreach ($roles as $roleId) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permissionId,
                'role_id'       => $roleId,
            ]);
        }

        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        $menu = DB::table('menu')->where('route_name', 'showTenantAgingReport')->first();
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
