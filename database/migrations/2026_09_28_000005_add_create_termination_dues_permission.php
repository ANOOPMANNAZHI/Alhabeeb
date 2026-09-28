<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds create_termination_dues: the "Create dues for a contract" box on the
 * Termination Dues screen (the backfill for one contract, from PLMS).
 * Attached to the Termination Dues menu row so it shows under that screen in
 * Role settings.
 */
class AddCreateTerminationDuesPermission extends Migration
{
    const PERMISSION = 'create_termination_dues';
    const ROLES = ['super_admin', 'backoffice_manager'];

    public function up()
    {
        if (DB::table('permissions')->where('name', self::PERMISSION)->exists()) {
            return;
        }

        $menu = DB::table('menu')->where('route_name', 'termination-dues.index')->first();

        $permissionId = DB::table('permissions')->insertGetId([
            'name'       => self::PERMISSION,
            'guard_name' => 'web',
            'menu_id'    => $menu ? $menu->id : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roleIds = DB::table('roles')->whereIn('name', self::ROLES)->pluck('id');
        foreach ($roleIds as $roleId) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permissionId,
                'role_id'       => $roleId,
            ]);
        }

        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        $permission = DB::table('permissions')->where('name', self::PERMISSION)->first();
        if ($permission) {
            DB::table('role_has_permissions')->where('permission_id', $permission->id)->delete();
            DB::table('permissions')->where('id', $permission->id)->delete();
        }
        app('cache')->forget('spatie.permission.cache');
    }
}
