<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds "Expense Details v2" as a separate menu item next to the existing
 * "Expense details" report, under PLM Module -> Finance Reports (same parent
 * as the v1 report). Resolves the parent by exact name under PLM Module
 * specifically (not a fuzzy ILIKE match) - see the Tenancy Details MERA menu
 * fix (2026_08_15_000001) for why: the system has several similarly-named
 * menus ("Reports", "Finance Reports") and a fuzzy match can attach the new
 * item under the wrong one.
 */
class AddExpenseDetailsReportV2Menu extends Migration
{
    public function up()
    {
        $plmModule = DB::table('menu')
            ->where('menu_name', 'PLM Module')
            ->where('parent_menu', 0)
            ->first();

        $financeReports = $plmModule
            ? DB::table('menu')
                ->where('menu_name', 'Finance Reports')
                ->where('parent_menu', $plmModule->id)
                ->where('menutype', 1)
                ->first()
            : null;

        $parentMenuId = $financeReports ? $financeReports->id : 0;

        $orderNext = DB::table('menu')
            ->where('parent_menu', $parentMenuId)
            ->count() + 1;

        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Expense Details v2',
            'menu_icon'   => 'fa-file-text',
            'route_name'  => 'showExpenseDetailsReportV2',
            'url_key'     => 'showExpenseDetailsReportV2',
            'parent_menu' => $parentMenuId,
            'menutype'    => 2,
            'menu_order'  => $orderNext,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        DB::table('permissions')->insert([
            'name'       => 'view_expense_details_report_v2',
            'guard_name' => 'web',
            'menu_id'    => $menuId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $permission = DB::table('permissions')
            ->where('name', 'view_expense_details_report_v2')
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
        $menu = DB::table('menu')
            ->where('route_name', 'showExpenseDetailsReportV2')
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
