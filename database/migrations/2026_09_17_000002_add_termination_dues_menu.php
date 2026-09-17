<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * "Termination Dues" under PLM Module -> Operations, two team permissions
 * granted to every role (admins can then prune per role), and the
 * account-code -> category map setting used to classify general receipts
 * and deposit-refund deductions.
 */
class AddTerminationDuesMenu extends Migration
{
    const ROUTE = 'termination-dues.index';
    const PERMS = ['view_termination_dues_backoffice', 'view_termination_dues_maintenance'];

    public function up()
    {
        $plmModule = DB::table('menu')->where('menu_name', 'PLM Module')->where('parent_menu', 0)->first();
        $operations = $plmModule
            ? DB::table('menu')->where('menu_name', 'Operations')->where('parent_menu', $plmModule->id)->where('menutype', 1)->first()
            : null;
        $parentMenuId = $operations ? $operations->id : 0;
        $orderNext = DB::table('menu')->where('parent_menu', $parentMenuId)->count() + 1;

        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Termination Dues',
            'menu_icon'   => 'fa-hand-holding-usd',
            'route_name'  => self::ROUTE,
            'url_key'     => 'termination-dues',
            'parent_menu' => $parentMenuId,
            'menutype'    => 2,
            'menu_order'  => $orderNext,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $roles = DB::table('roles')->pluck('id');
        foreach (self::PERMS as $name) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => $name, 'guard_name' => 'web', 'menu_id' => $menuId, 'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($roles as $roleId) {
                DB::table('role_has_permissions')->insert(['permission_id' => $permissionId, 'role_id' => $roleId]);
            }
        }

        if (!DB::table('configuration')->where('configuration_settings', 'termination_dues_account_map')->exists()) {
            DB::table('configuration')->insert([
                'configuration_name'     => 'termination_dues',
                'configuration_settings' => 'termination_dues_account_map',
                'configuration_value'    => json_encode([
                    'rent' => ['12211', '31001'], 'municipal' => ['41102', '41103'],
                    'ew' => ['41105', '22305'], 'maintenance' => ['41110', '22310', '41107', '22307', '12302'],
                ]),
                'created_by' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        $menu = DB::table('menu')->where('route_name', self::ROUTE)->first();
        foreach (DB::table('permissions')->whereIn('name', self::PERMS)->get() as $p) {
            DB::table('role_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('permissions')->where('id', $p->id)->delete();
        }
        if ($menu) {
            DB::table('menu')->where('id', $menu->id)->delete();
        }
        DB::table('configuration')->where('configuration_settings', 'termination_dues_account_map')->delete();
        app('cache')->forget('spatie.permission.cache');
    }
}
