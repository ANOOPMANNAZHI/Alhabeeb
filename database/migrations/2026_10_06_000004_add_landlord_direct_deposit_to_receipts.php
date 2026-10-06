<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Receipt flag "Direct deposit to landlord": the tenant paid the landlord directly (cash, transfer or a cheque in
 * the landlord's name). Information only — AX posting is unchanged. Existing receipts are left unflagged.
 * Permission mark_receipt_landlord_deposit (Mark / Unmark on the receipt view) goes to the roles that approve receipts.
 */
class AddLandlordDirectDepositToReceipts extends Migration
{
    const PERM = 'mark_receipt_landlord_deposit';

    public function up()
    {
        if (!Schema::hasColumn('receipts_generation', 'receipts_generation_paid_to_landlord')) {
            Schema::table('receipts_generation', function (Blueprint $table) {
                $table->boolean('receipts_generation_paid_to_landlord')->default(false);
                $table->integer('receipts_generation_landlord_marked_by')->nullable();
                $table->timestamp('receipts_generation_landlord_marked_at')->nullable();
            });
        }

        if (DB::table('permissions')->where('name', self::PERM)->exists()) {
            return;
        }
        $view = DB::table('permissions')->where('name', 'view_tenant_receipt')->first();
        $permissionId = DB::table('permissions')->insertGetId(['name' => self::PERM, 'guard_name' => 'web',
            'menu_id' => $view ? $view->menu_id : null, 'created_at' => now(), 'updated_at' => now()]);
        $roles = DB::table('role_has_permissions as rp')->join('permissions as p', 'p.id', '=', 'rp.permission_id')
            ->where('p.name', 'request_approval_receipt')->pluck('rp.role_id')->unique();
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
        if (Schema::hasColumn('receipts_generation', 'receipts_generation_paid_to_landlord')) {
            Schema::table('receipts_generation', function (Blueprint $table) {
                $table->dropColumn(['receipts_generation_paid_to_landlord', 'receipts_generation_landlord_marked_by',
                    'receipts_generation_landlord_marked_at']);
            });
        }
        app('cache')->forget('spatie.permission.cache');
    }
}
