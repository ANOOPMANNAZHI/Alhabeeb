<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Masters -> Bounce Reason, next to Bank (the parent is found through the Bank row,
 * since menu ids differ between databases). Permissions like the Bank master's,
 * granted to super_admin and backoffice_manager.
 * Deploy: upload the code first — a menu row whose route is missing breaks every page.
 */
class AddBounceReasonsMenu extends Migration
{
    const ROUTE = 'bounce-reason.index';
    const PERMS = ['view_bounce_reason', 'add_bounce_reason', 'edit_bounce_reason'];
    const ROLES = ['super_admin', 'backoffice_manager'];

    public function up()
    {
        if (DB::table('menu')->where('route_name', self::ROUTE)->exists()) {
            return;
        }
        $bank = DB::table('menu')->where('route_name', 'bank.index')->first();
        $parentId = $bank ? $bank->parent_menu : 0;

        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Bounce Reason',
            'menu_icon'   => 'fa-undo',
            'route_name'  => self::ROUTE,
            'url_key'     => 'bounce-reason',
            'parent_menu' => $parentId,
            'menutype'    => 2,
            'menu_order'  => DB::table('menu')->where('parent_menu', $parentId)->count() + 1,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $roleIds = DB::table('roles')->whereIn('name', self::ROLES)->pluck('id');
        foreach (self::PERMS as $name) {
            if (DB::table('permissions')->where('name', $name)->exists()) {
                continue;
            }
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => $name, 'guard_name' => 'web', 'menu_id' => $menuId, 'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($roleIds as $roleId) {
                DB::table('role_has_permissions')->insert(['permission_id' => $permissionId, 'role_id' => $roleId]);
            }
        }
        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        foreach (DB::table('permissions')->whereIn('name', self::PERMS)->get() as $p) {
            DB::table('role_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('permissions')->where('id', $p->id)->delete();
        }
        DB::table('menu')->where('route_name', self::ROUTE)->delete();
        app('cache')->forget('spatie.permission.cache');
    }
}
