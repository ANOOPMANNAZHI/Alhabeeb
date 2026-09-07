<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds "Landlord Invoice v2" under PLM Module -> Operations. Resolves the
 * parent by exact name under PLM Module specifically (not hardcoded id or a
 * fuzzy match) - see the Tenancy Details MERA menu fix (2026_08_15_000001)
 * and the Expense Details v2 menu (2026_09_02_000001) for why.
 */
class AddLandlordInvoiceV2Menu extends Migration
{
    public function up()
    {
        $plmModule = DB::table('menu')
            ->where('menu_name', 'PLM Module')
            ->where('parent_menu', 0)
            ->first();

        $operations = $plmModule
            ? DB::table('menu')
                ->where('menu_name', 'Operations')
                ->where('parent_menu', $plmModule->id)
                ->where('menutype', 1)
                ->first()
            : null;

        $parentMenuId = $operations ? $operations->id : 0;

        $orderNext = DB::table('menu')
            ->where('parent_menu', $parentMenuId)
            ->count() + 1;

        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Landlord Invoice v2',
            'menu_icon'   => 'fa-file-text-o',
            'route_name'  => 'landlord-invoice-v2.index',
            'url_key'     => 'landlord-invoice-v2',
            'parent_menu' => $parentMenuId,
            'menutype'    => 2,
            'menu_order'  => $orderNext,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        DB::table('permissions')->insert([
            'name'       => 'view_landlord_invoice_v2',
            'guard_name' => 'web',
            'menu_id'    => $menuId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $permission = DB::table('permissions')
            ->where('name', 'view_landlord_invoice_v2')
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
        $menu = DB::table('menu')->where('route_name', 'landlord-invoice-v2.index')->first();
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
