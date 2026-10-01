<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Receivables Follow-up: append-only follow-up history per tenant contract,
 * menu PLM Module -> Operations -> Receivables Follow-up, and two permissions
 * (view / add) for the roles that chase rent.
 */
class CreateReceivableFollowups extends Migration
{
    const ROUTE = 'receivable-followup.index';
    const PERMS = ['view_receivable_followup', 'add_receivable_followup'];
    const ROLES = ['super_admin', 'are', 'are_team_lead', 'backoffice_executive', 'backoffice_manager'];

    public function up()
    {
        if (!Schema::hasTable('receivable_followups')) {
            Schema::create('receivable_followups', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('tenant_contract_id');
                $table->text('comment');
                $table->date('promised_date')->nullable();
                $table->decimal('balance_at_followup', 14, 3)->nullable();
                $table->integer('created_by');
                $table->timestamps();
                $table->index(['tenant_contract_id', 'created_at']);
            });
        }

        if (DB::table('menu')->where('route_name', self::ROUTE)->exists()) {
            return;
        }
        $plm = DB::table('menu')->where('menu_name', 'PLM Module')->where('parent_menu', 0)->first();
        $operations = $plm
            ? DB::table('menu')->where('menu_name', 'Operations')->where('parent_menu', $plm->id)->where('menutype', 1)->first()
            : null;
        if (!$operations) {
            throw new \RuntimeException('Menu "PLM Module -> Operations" not found.');
        }
        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Receivables Follow-up',
            'menu_icon'   => 'fa-phone',
            'route_name'  => self::ROUTE,
            'url_key'     => 'receivable-followup',
            'parent_menu' => $operations->id,
            'menutype'    => 2,
            'menu_order'  => DB::table('menu')->where('parent_menu', $operations->id)->count() + 1,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $roleIds = DB::table('roles')->whereIn('name', self::ROLES)->pluck('id');
        foreach (self::PERMS as $name) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => $name, 'guard_name' => 'web', 'menu_id' => $menuId, 'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($roleIds as $roleId) {
                DB::table('role_has_permissions')->insert(['permission_id' => $permissionId, 'role_id' => $roleId]);
            }
        }
        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        foreach (DB::table('permissions')->whereIn('name', self::PERMS)->get() as $p) {
            DB::table('role_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('permissions')->where('id', $p->id)->delete();
        }
        DB::table('menu')->where('route_name', self::ROUTE)->delete();
        Schema::dropIfExists('receivable_followups');
        app('cache')->forget('spatie.permission.cache');
    }
}
