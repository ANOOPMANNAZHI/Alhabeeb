<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the post_landlord_invoice_v2 permission (POST to AX button on the
 * Landlord Invoice v2 list). Attached to the same menu row as
 * view_landlord_invoice_v2 so it shows under that screen in Role settings.
 */
class AddPostLandlordInvoiceV2Permission extends Migration
{
    const PERMISSION = 'post_landlord_invoice_v2';
    const ROLES = ['super_admin', 'finance_manager', 'accountant', 'backoffice_manager'];

    public function up()
    {
        if (DB::table('permissions')->where('name', self::PERMISSION)->exists()) {
            return;
        }

        $menu = DB::table('menu')->where('route_name', 'landlord-invoice-v2.index')->first();

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
