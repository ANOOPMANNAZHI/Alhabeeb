<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Mail -> Templates. The Mail parent is found through the Compose row
 * (massMail.create) because menu ids differ between databases.
 */
class AddMessageTemplatesMenu extends Migration
{
    const ROUTE = 'message-templates.index';
    const PERMS = ['view_message_templates', 'manage_message_templates'];
    const ROLES = ['super_admin', 'backoffice_manager'];

    public function up()
    {
        if (DB::table('menu')->where('route_name', self::ROUTE)->exists()) {
            return;
        }
        $compose = DB::table('menu')->where('route_name', 'massMail.create')->first();
        $parentId = $compose ? $compose->parent_menu : 0;

        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Templates',
            'menu_icon'   => 'fa-file-text-o',
            'route_name'  => self::ROUTE,
            'url_key'     => 'message-templates',
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
