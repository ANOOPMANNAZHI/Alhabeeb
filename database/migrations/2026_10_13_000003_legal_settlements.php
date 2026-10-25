<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\BackOffice\Services\SystemTemplateDefaults;

/**
 * Legal module phase 3 (plan 2026-10-13): settlements.
 * The lawyer enters a settlement (out of court, or decided by the court) with its installments; PLM
 * verifies it (waiver, fees recovered, final total, dates) or sends it back. Collection is matched from
 * receipts; overdue installments alert, a long overdue one breaks the settlement. A case closes only
 * after its settlement is verified (paid in full, or balance written off with a reason).
 * status: 0 Pending verification, 1 Active, 2 Completed, 3 Broken, 4 Sent back, 5 Cancelled.
 * Permissions hang on the Active Cases menu. Run after the code is uploaded.
 */
class LegalSettlements extends Migration
{
    const PERMISSIONS = [
        'legal_settlement_enter' => ['super_admin', 'legal_advisor'],
        'legal_settlement_verify' => ['super_admin', 'md', 'backoffice_manager'],
        'legal_case_close' => ['super_admin', 'md', 'backoffice_manager'],
    ];
    const TEMPLATES = ['legal_settlement_entered', 'legal_settlement_verified', 'legal_settlement_sent_back',
        'legal_settlement_overdue', 'legal_settlement_broken', 'legal_settlement_completed', 'legal_settlement_shortfall',
        'legal_case_reopened'];

    public function up()
    {
        Schema::create('legal_settlements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('legal_id')->index();
            $table->smallInteger('settlement_type');                 // 1 Out of court, 2 Court decided
            $table->date('agreed_date');                             // agreement / judgment date
            $table->decimal('amount', 14, 3);                        // what the tenant agreed / was ordered to pay
            $table->decimal('outstanding_at_entry', 14, 3)->default(0);
            $table->decimal('waiver_amount', 14, 3)->default(0);
            $table->string('waiver_reason', 500)->nullable();
            $table->decimal('fees_recovered', 14, 3)->default(0);
            $table->decimal('settled_total', 14, 3)->default(0);     // amount + fees recovered = sum of installments
            $table->smallInteger('status')->default(0);
            $table->text('note')->nullable();
            $table->text('sendback_reason')->nullable();
            $table->integer('entered_by');
            $table->integer('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('broken_at')->nullable();
            $table->timestamp('shortfall_alerted_at')->nullable();
            $table->timestamp('waiver_cancelled_at')->nullable();
            $table->timestamps();
        });
        Schema::create('legal_settlement_installments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('settlement_id')->index();
            $table->date('due_date');
            $table->decimal('amount', 14, 3);
            $table->integer('sort_order')->default(0);
            $table->timestamp('overdue_alerted_at')->nullable();
            $table->timestamps();
        });

        SystemTemplateDefaults::seed(self::TEMPLATES);

        $menuId = DB::table('menu')->where('route_name', 'activeCases')->value('id');
        $roles = DB::table('roles')->pluck('id', 'name');
        $now = now();
        foreach (self::PERMISSIONS as $perm => $roleNames) {
            if (DB::table('permissions')->where('name', $perm)->exists()) {
                continue;
            }
            $pid = DB::table('permissions')->insertGetId(['name' => $perm, 'guard_name' => 'web', 'menu_id' => $menuId,
                'created_at' => $now, 'updated_at' => $now]);
            foreach ($roleNames as $r) {
                if (isset($roles[$r])) {
                    DB::table('role_has_permissions')->insert(['permission_id' => $pid, 'role_id' => $roles[$r]]);
                }
            }
        }
        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        foreach (DB::table('permissions')->whereIn('name', array_keys(self::PERMISSIONS))->get() as $p) {
            DB::table('role_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('model_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('permissions')->where('id', $p->id)->delete();
        }
        DB::table('message_templates')->whereIn('key', self::TEMPLATES)->delete();
        Schema::dropIfExists('legal_settlement_installments');
        Schema::dropIfExists('legal_settlements');
        app('cache')->forget('spatie.permission.cache');
    }
}
