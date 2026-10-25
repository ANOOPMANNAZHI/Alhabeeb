<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menu Maintenance Module -> Building Inspection (Dashboard, Visits, Issue Register, Reports, Setup)
 * and its permissions. Run after the code is uploaded: the sidebar calls route() on every menu row.
 *   executives  - see their own buildings, visits and issues
 *   supervisors - see everything, verify issues of others (Assistant Manager)
 *   managers    - see everything, verify any issue, setup
 */
class AddBuildingInspectionMenus extends Migration
{
    const PARENT = 'Building Inspection';
    // menu => [route, url_key, icon, [permission => roles]]
    const EXEC = ['super_admin', 'maintenance_manager', 'maintenance_supervisor', 'maintenance_executive'];
    const SUPERVISE = ['super_admin', 'maintenance_manager', 'maintenance_supervisor'];
    const MANAGE = ['super_admin', 'maintenance_manager'];

    private function menus()
    {
        return [
            'Inspection Dashboard' => ['inspection.dashboard', 'inspection-dashboard', 'fa-tachometer', [
                'inspection_view' => self::EXEC,
                'inspection_view_all' => self::SUPERVISE,
            ]],
            'Inspection Visits' => ['inspection.visits', 'inspection-visits', 'fa-calendar-check-o', [
                'inspection_visit_create' => self::EXEC,
                'inspection_execute' => ['super_admin', 'maintenance_supervisor', 'maintenance_executive'],
            ]],
            'Inspection Issues' => ['inspection.issues', 'inspection-issues', 'fa-list-alt', [
                'inspection_issue_create' => self::EXEC,
                'inspection_complaint_create' => self::EXEC,
                'inspection_verify' => self::SUPERVISE,
            ]],
            'Inspection Reports' => ['inspection.reports', 'inspection-reports', 'fa-bar-chart', [
                'inspection_reports' => self::SUPERVISE,
            ]],
            'Inspection Setup' => ['inspection.setup', 'inspection-setup', 'fa-cogs', [
                'inspection_masters' => self::MANAGE,
            ]],
        ];
    }

    public function up()
    {
        if (DB::table('menu')->where('route_name', 'inspection.dashboard')->exists()) {
            return;
        }
        $module = DB::table('menu')->where('parent_menu', 0)->whereRaw("regexp_replace(menu_name, '\\s+', ' ', 'g') = 'Maintenance Module'")->first();
        if (!$module) {
            throw new \RuntimeException('Menu "Maintenance Module" not found.');
        }
        $now = now();
        $parentId = DB::table('menu')->insertGetId([
            'menu_name' => self::PARENT, 'menu_icon' => 'fa-building', 'route_name' => null, 'url_key' => null,
            'parent_menu' => $module->id, 'menutype' => 1,
            'menu_order' => DB::table('menu')->where('parent_menu', $module->id)->max('menu_order') + 1,
            'status' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $roles = DB::table('roles')->pluck('id', 'name');
        $order = 0;
        foreach ($this->menus() as $name => $m) {
            list($route, $key, $icon, $perms) = $m;
            $menuId = DB::table('menu')->insertGetId([
                'menu_name' => $name, 'menu_icon' => $icon, 'route_name' => $route, 'url_key' => $key,
                'parent_menu' => $parentId, 'menutype' => 2, 'menu_order' => ++$order, 'status' => 1,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            foreach ($perms as $perm => $roleNames) {
                $pid = DB::table('permissions')->insertGetId(['name' => $perm, 'guard_name' => 'web', 'menu_id' => $menuId,
                    'created_at' => $now, 'updated_at' => $now]);
                foreach ($roleNames as $r) {
                    if (isset($roles[$r])) {
                        DB::table('role_has_permissions')->insert(['permission_id' => $pid, 'role_id' => $roles[$r]]);
                    }
                }
            }
        }
        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        $names = [];
        foreach ($this->menus() as $m) {
            $names = array_merge($names, array_keys($m[3]));
        }
        foreach (DB::table('permissions')->whereIn('name', $names)->get() as $p) {
            DB::table('role_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('model_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('permissions')->where('id', $p->id)->delete();
        }
        $parent = DB::table('menu')->where('menu_name', self::PARENT)->where('menutype', 1)->first();
        if ($parent) {
            DB::table('menu')->where('parent_menu', $parent->id)->delete();
            DB::table('menu')->where('id', $parent->id)->delete();
        }
        app('cache')->forget('spatie.permission.cache');
    }
}
