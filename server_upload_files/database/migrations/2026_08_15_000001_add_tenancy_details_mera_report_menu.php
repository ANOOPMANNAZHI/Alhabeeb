<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTenancyDetailsMeraReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Find "PLM Module" (top-level menu) then its "Reports" child - there are
        // several "Reports" menus in the system (one per module), so matching on
        // menu_name alone is ambiguous and can attach this under the wrong module.
        $plmModule = DB::table('menu')
            ->where('menu_name', 'PLM Module')
            ->where('parent_menu', 0)
            ->first();

        $reportMenu = $plmModule
            ? DB::table('menu')
                ->where('menu_name', 'Reports')
                ->where('parent_menu', $plmModule->id)
                ->where('menutype', 1)
                ->first()
            : null;

        $parentMenuId = $reportMenu ? $reportMenu->id : 0;

        // Calculate next menu order
        $orderNext = DB::table('menu')
            ->where('parent_menu', $parentMenuId)
            ->count() + 1;

        // Insert the menu item
        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Tenancy Details MERA',
            'menu_icon'   => 'fa-file-text',
            'route_name'  => 'showtenancyDetailsMeraReport',
            'url_key'     => 'showtenancyDetailsMeraReport',
            'parent_menu' => $parentMenuId,
            'menutype'    => 2,
            'menu_order'  => $orderNext,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Create a permission for this menu item
        DB::table('permissions')->insert([
            'name'       => 'view_tenancy_details_mera_report',
            'guard_name' => 'web',
            'menu_id'    => $menuId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get the permission id
        $permission = DB::table('permissions')
            ->where('name', 'view_tenancy_details_mera_report')
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
            ->where('route_name', 'showtenancyDetailsMeraReport')
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
