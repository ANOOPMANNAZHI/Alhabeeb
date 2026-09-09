<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds "Deposit Refund Receipts" under PLM Module -> Operations -> Transaction,
 * beside the existing Deposit Refund screens, with its view permission granted
 * to every role.
 *
 * NOTE permissions.menu_id is NOT NULL with a cascade delete from menu, so the
 * menu row is always created before the permission.
 */
class AddDepositRefundReceiptMenu extends Migration
{
    const ROUTE = 'depositRefundReceiptList';
    const PERMISSION = 'deposit_refund_receipt_list';

    public function up()
    {
        if (DB::table('menu')->where('route_name', self::ROUTE)->exists()) {
            return;
        }

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

        // Transaction is a group nested under Operations
        $transaction = $operations
            ? DB::table('menu')
                ->where('menu_name', 'Transaction')
                ->where('parent_menu', $operations->id)
                ->where('menutype', 1)
                ->first()
            : null;

        $parentMenuId = $transaction ? $transaction->id : 0;

        $orderNext = DB::table('menu')
            ->where('parent_menu', $parentMenuId)
            ->count() + 1;

        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Deposit Refund Receipts',
            'menu_icon'   => 'fa-file-text-o',
            'route_name'  => self::ROUTE,
            'url_key'     => self::ROUTE,
            'parent_menu' => $parentMenuId,
            'menutype'    => 2,
            'menu_order'  => $orderNext,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $permissionId = DB::table('permissions')->insertGetId([
            'name'       => self::PERMISSION,
            'guard_name' => 'web',
            'menu_id'    => $menuId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (DB::table('roles')->pluck('id') as $roleId) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permissionId,
                'role_id'       => $roleId,
            ]);
        }

        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        $menu = DB::table('menu')->where('route_name', self::ROUTE)->first();
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
