<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menu PLM Module -> Reports -> Cash Receipts (next to Rent / General Receipt) and its view
 * permission for the roles that see the Rent Receipt report. Run after the code is uploaded.
 */
class AddCashReceiptReportMenu extends Migration
{
    const ROUTE = 'showCashReceiptReport';
    const PERM = 'view_cash_receipt_report';

    public function up()
    {
        if (DB::table('menu')->where('route_name', self::ROUTE)->exists()) {
            return;
        }
        $rent = DB::table('menu')->where('route_name', 'showRentReceiptReport')->first();
        if (!$rent) {
            throw new \RuntimeException('Menu "Rent Receipt" not found.');
        }
        $menuId = DB::table('menu')->insertGetId([
            'menu_name' => 'Cash Receipts', 'menu_icon' => 'fa-money', 'route_name' => self::ROUTE, 'url_key' => self::ROUTE,
            'parent_menu' => $rent->parent_menu, 'menutype' => 2, 'menu_order' => $rent->menu_order, 'status' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $permissionId = DB::table('permissions')->insertGetId(['name' => self::PERM, 'guard_name' => 'web', 'menu_id' => $menuId,
            'created_at' => now(), 'updated_at' => now()]);
        // same roles as the Rent Receipt report
        $roles = DB::table('role_has_permissions as rp')->join('permissions as p', 'p.id', '=', 'rp.permission_id')
            ->where('p.name', 'view_rent_receipt_report')->pluck('rp.role_id')->unique();
        foreach ($roles as $roleId) {
            DB::table('role_has_permissions')->insert(['permission_id' => $permissionId, 'role_id' => $roleId]);
        }
        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        $p = DB::table('permissions')->where('name', self::PERM)->first();
        if ($p) {
            DB::table('role_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('model_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('permissions')->where('id', $p->id)->delete();
        }
        DB::table('menu')->where('route_name', self::ROUTE)->delete();
        app('cache')->forget('spatie.permission.cache');
    }
}
