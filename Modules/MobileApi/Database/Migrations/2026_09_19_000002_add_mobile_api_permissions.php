<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * "Mobile Devices" under PLM Module -> Masters (the office screen that clears /
 * blocks a registered phone), plus the two mobile permissions hung off it —
 * every permission in this system belongs to a menu row (permissions.menu_id is
 * NOT NULL and the permission admin screen is per menu).
 *
 * mobile_app_login is what the API checks at login; it is granted to the ARE
 * roles only. mobile_devices_manage guards the office screen.
 */
return new class extends Migration
{
    const ROUTE = 'mobile-devices.index';

    const PERMS = [
        'mobile_app_login'      => ['are', 'are_team_lead'],
        'mobile_devices_manage' => ['super_admin', 'backoffice_manager'],
    ];

    public function up(): void
    {
        $plmModule = DB::table('menu')->where('menu_name', 'PLM Module')->where('parent_menu', 0)->first();
        $masters = $plmModule
            ? DB::table('menu')->where('menu_name', 'Masters')->where('parent_menu', $plmModule->id)->where('menutype', 1)->first()
            : null;
        $parentMenuId = $masters ? $masters->id : ($plmModule ? $plmModule->id : 0);

        $menuId = DB::table('menu')->where('route_name', self::ROUTE)->value('id')
            ?? DB::table('menu')->insertGetId([
                'menu_name'   => 'Mobile Devices',
                'menu_icon'   => 'fa-mobile',
                'route_name'  => self::ROUTE,
                'url_key'     => 'mobileDevices',
                'parent_menu' => $parentMenuId,
                'menutype'    => 2,
                'menu_order'  => DB::table('menu')->where('parent_menu', $parentMenuId)->count() + 1,
                'status'      => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

        foreach (self::PERMS as $name => $roles) {
            $pid = DB::table('permissions')->where('name', $name)->value('id')
                ?? DB::table('permissions')->insertGetId([
                    'name' => $name, 'guard_name' => 'web', 'menu_id' => $menuId, 'created_at' => now(), 'updated_at' => now(),
                ]);
            foreach (DB::table('roles')->whereIn('name', $roles)->pluck('id') as $rid) {
                DB::table('role_has_permissions')->updateOrInsert(['permission_id' => $pid, 'role_id' => $rid]);
            }
        }
        app('cache')->forget('spatie.permission.cache');
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('name', array_keys(self::PERMS))->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
        DB::table('menu')->where('route_name', self::ROUTE)->delete();
        app('cache')->forget('spatie.permission.cache');
    }
};
