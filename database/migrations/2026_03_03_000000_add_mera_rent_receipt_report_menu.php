<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMeraRentReceiptReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Find the "Reports" parent menu group (menutype = 1)
        $reportMenu = DB::table('menu')
            ->where('menu_name', 'like', '%Report%')
            ->where('menutype', 1)
            ->first();

        if (!$reportMenu) {
            $reportMenu = DB::table('menu')
                ->where('menu_name', 'like', '%report%')
                ->where('menutype', 1)
                ->first();
        }

        $parentMenuId = $reportMenu ? $reportMenu->id : 0;

        // Calculate next menu order
        $orderNext = DB::table('menu')
            ->where('parent_menu', $parentMenuId)
            ->count() + 1;

        // Insert the menu item
        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'MERA Rent Receipt Report',
            'menu_icon'   => 'fa-file-text',
            'route_name'  => 'showMeraRentReceiptReport',
            'url_key'     => 'showMeraRentReceiptReport',
            'parent_menu' => $parentMenuId,
            'menutype'    => 2,
            'menu_order'  => $orderNext,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Create a permission for this menu item
        DB::table('permissions')->insert([
            'name'       => 'view_mera_rent_receipt_report',
            'guard_name' => 'web',
            'menu_id'    => $menuId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get the permission id
        $permission = DB::table('permissions')
            ->where('name', 'view_mera_rent_receipt_report')
            ->first();

        if ($permission) {
            // Assign this permission to all existing roles so it is visible by default
            $roles = DB::table('roles')->pluck('id');
            foreach ($roles as $roleId) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permission->id,
                    'role_id'       => $roleId,
                ]);
            }
        }

        // Clear Spatie permission cache
        app('cache')->forget('spatie.permission.cache');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $menu = DB::table('menu')
            ->where('route_name', 'showMeraRentReceiptReport')
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
