<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddLandlordTaxInvoiceReportMenu extends Migration
{
    public function up()
    {
        $parentMenuId = DB::table('menu')->where('id', 183)->value('id') ?? 0;

        $orderNext = DB::table('menu')
            ->where('parent_menu', $parentMenuId)
            ->count() + 1;

        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Landlord Tax Invoice Report',
            'menu_icon'   => 'fa-file-text-o',
            'route_name'  => 'showLandlordTaxInvoiceReport',
            'url_key'     => 'showLandlordTaxInvoiceReport',
            'parent_menu' => $parentMenuId,
            'menutype'    => 2,
            'menu_order'  => $orderNext,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        DB::table('permissions')->insert([
            'name'       => 'view_landlord_tax_invoice_report',
            'guard_name' => 'web',
            'menu_id'    => $menuId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $permission = DB::table('permissions')
            ->where('name', 'view_landlord_tax_invoice_report')
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
        $menu = DB::table('menu')->where('route_name', 'showLandlordTaxInvoiceReport')->first();
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
