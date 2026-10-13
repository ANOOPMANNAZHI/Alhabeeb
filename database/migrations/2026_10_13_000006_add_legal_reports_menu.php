<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Legal module phase 5 (plan 2026-10-13): Reports -> Legal Cases Summary and Legal Settlements, with their
 * permissions. Run after the code is uploaded: the sidebar calls route() on every menu row.
 */
class AddLegalReportsMenu extends Migration
{
    // menu name => [route, permission]
    const MENUS = [
        'Legal Cases Summary' => ['legal.reports.cases', 'view_legal_cases_summary'],
        'Legal Settlements' => ['legal.reports.settlements', 'view_legal_settlements_report'],
    ];
    const ROLES = ['super_admin', 'md', 'ceo', 'backoffice_manager', 'legal_advisor'];

    public function up()
    {
        $parent = DB::table('menu')->where('route_name', 'showLegalCaseReport')->value('parent_menu');
        if (!$parent) {
            throw new \RuntimeException('Reports menu (parent of "Legal Case" report) not found.');
        }
        $roles = DB::table('roles')->whereIn('name', self::ROLES)->pluck('id');
        $now = now();
        foreach (self::MENUS as $name => $m) {
            if (DB::table('menu')->where('route_name', $m[0])->exists()) {
                continue;
            }
            $menuId = DB::table('menu')->insertGetId([
                'menu_name' => $name, 'menu_icon' => 'fa-gavel', 'route_name' => $m[0], 'url_key' => str_replace('.', '-', $m[0]),
                'parent_menu' => $parent, 'menutype' => 2,
                'menu_order' => DB::table('menu')->where('parent_menu', $parent)->max('menu_order') + 1,
                'status' => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $pid = DB::table('permissions')->insertGetId(['name' => $m[1], 'guard_name' => 'web', 'menu_id' => $menuId,
                'created_at' => $now, 'updated_at' => $now]);
            foreach ($roles as $roleId) {
                DB::table('role_has_permissions')->insert(['permission_id' => $pid, 'role_id' => $roleId]);
            }
        }
        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        foreach (self::MENUS as $m) {
            foreach (DB::table('permissions')->where('name', $m[1])->get() as $p) {
                DB::table('role_has_permissions')->where('permission_id', $p->id)->delete();
                DB::table('model_has_permissions')->where('permission_id', $p->id)->delete();
                DB::table('permissions')->where('id', $p->id)->delete();
            }
            DB::table('menu')->where('route_name', $m[0])->delete();
        }
        app('cache')->forget('spatie.permission.cache');
    }
}
