<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the edit_active_tenant_contract permission, which lets an admin use the
 * Edit button on tenant contracts that are already ACTIVE, not just on direct
 * contracts still at workflow stage 107.
 *
 * Granted to the admin roles only - editing an executed contract changes an
 * agreement that is already in force.
 *
 * NOTE permissions.menu_id is NOT NULL with a foreign key to menu, so the new
 * permission is attached to the same menu row as the existing
 * edit_tenant_contract_direct permission rather than creating a menu of its own
 * (it is an action permission, not a screen).
 */
class AddEditActiveTenantContractPermission extends Migration
{
    const PERMISSION = 'edit_active_tenant_contract';
    const SIBLING    = 'edit_tenant_contract_direct';

    const ADMIN_ROLES = ['super_admin', 'admin', 'administrator'];

    public function up()
    {
        if (DB::table('permissions')->where('name', self::PERMISSION)->exists()) {
            return;
        }

        $sibling = DB::table('permissions')->where('name', self::SIBLING)->first();

        if (!$sibling) {
            throw new \RuntimeException(
                'Permission ' . self::SIBLING . ' not found, so the menu it belongs to '
                . 'could not be resolved. Run: SELECT id, name, menu_id FROM permissions '
                . "WHERE name LIKE '%tenant_contract%';"
            );
        }

        $permissionId = DB::table('permissions')->insertGetId([
            'name'       => self::PERMISSION,
            'guard_name' => 'web',
            'menu_id'    => $sibling->menu_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roleIds = DB::table('roles')->whereIn('name', self::ADMIN_ROLES)->pluck('id');

        foreach ($roleIds as $roleId) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permissionId,
                'role_id'       => $roleId,
            ]);
        }

        if ($roleIds->isEmpty()) {
            // Not fatal: the permission exists and can be granted from the UI,
            // but nobody has it yet, so the button would stay hidden.
            echo "WARNING: none of the roles " . implode(', ', self::ADMIN_ROLES)
               . " exist, so " . self::PERMISSION . " was granted to no role.\n"
               . "Run: SELECT id, name FROM roles ORDER BY name;\n";
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
