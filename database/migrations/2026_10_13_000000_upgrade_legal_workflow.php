<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Legal module upgrade, phase 1 (plan 2026-10-13): who handles the case, case type, priority,
 * assigned lawyer, court details, close reason, reopen link; the stage master; one timeline
 * table so approvals and refer-backs stop overwriting legal.note.
 *
 * Old cases: handled by Al Habib, type from the contract (active = A), priority Medium, lawyer =
 * the legal_advisor already attached in legal_users. Cases whose contract is no longer in legal
 * (tenant_contracts.status <> 5) or that were closed by an end note (legal_is_closed = 1) get
 * closed_at, so a new Move to Legal on that contract opens a fresh case.
 */
class UpgradeLegalWorkflow extends Migration
{
    // name => [applies_to (0 both, 1 A only, 2 B only), is_final]
    const STAGES = [
        'Legal notice sent' => [0, false],
        'Case filed' => [0, false],
        'Hearing' => [0, false],
        'Judgment' => [0, false],
        'Appeal' => [0, false],
        'Execution / enforcement' => [0, false],
        'Eviction completed' => [1, false],
        'Withdrawn - settled out of court' => [0, false],
        'Closed' => [0, true],
    ];

    public function up()
    {
        Schema::table('legal', function (Blueprint $table) {
            $table->smallInteger('handled_by')->default(1);          // 1 Al Habib, 2 Landlord, 3 Government, 4 MERA
            $table->string('handed_to_name', 150)->nullable();
            $table->string('handover_ref', 100)->nullable();
            $table->date('handover_date')->nullable();
            $table->smallInteger('case_type')->nullable();           // 1 Occupying / refusing, 2 Vacated / terminated
            $table->smallInteger('priority')->nullable();            // 1 High, 2 Medium, 3 Low
            $table->integer('lawyer_user_id')->nullable();
            $table->integer('stage_id')->nullable();
            $table->decimal('claim_amount', 14, 3)->nullable();
            $table->string('court_case_no', 100)->nullable();
            $table->string('court_name', 150)->nullable();
            $table->date('filed_date')->nullable();
            $table->date('next_hearing_date')->nullable();
            $table->decimal('judgment_amount', 14, 3)->nullable();
            $table->string('close_reason', 40)->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->integer('closed_by')->nullable();
            $table->integer('parent_legal_id')->nullable();
        });

        Schema::create('legal_stages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->smallInteger('applies_to')->default(0);
            $table->boolean('is_final')->default(false);
            $table->integer('sort_order')->default(0);
            $table->smallInteger('status')->default(1);
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        // kind: moved, plm_approved, plm_referback, lawyer_approved, lawyer_rejected, handed_over,
        // handled_by, stage, comment, hearing, settlement, reassigned, closed, reopened
        Schema::create('legal_case_updates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('legal_id')->index();
            $table->string('kind', 30);
            $table->integer('stage_id')->nullable();
            $table->text('note')->nullable();
            $table->date('event_date')->nullable();
            $table->date('next_date')->nullable();
            $table->integer('created_by');
            $table->timestamps();
        });

        $now = now();
        $order = 0;
        foreach (self::STAGES as $name => $s) {
            DB::table('legal_stages')->insert(['name' => $name, 'applies_to' => $s[0], 'is_final' => $s[1],
                'sort_order' => ++$order, 'status' => 1, 'created_by' => 1, 'created_at' => $now, 'updated_at' => $now]);
        }

        DB::statement("UPDATE legal l SET case_type = CASE WHEN tc.tenant_contract_status = 1 THEN 1 ELSE 2 END
                       FROM tenant_contracts tc WHERE tc.id = l.tenant_contract_id");
        DB::statement("UPDATE legal SET case_type = 2 WHERE case_type IS NULL");
        DB::statement("UPDATE legal SET priority = 2");
        DB::statement("UPDATE legal l SET lawyer_user_id = x.user_id FROM (
                           SELECT lu.legal_id, max(lu.user_id) AS user_id FROM legal_users lu
                           JOIN roles r ON r.id = lu.role_id AND r.name = 'legal_advisor'
                           WHERE lu.user_id > 0 GROUP BY lu.legal_id) x
                       WHERE x.legal_id = l.id");
        DB::statement("UPDATE legal l SET closed_at = l.updated_at, close_reason = 'before_upgrade'
                       FROM tenant_contracts tc
                       WHERE tc.id = l.tenant_contract_id AND (tc.status IS DISTINCT FROM 5 OR l.legal_is_closed = 1)");
    }

    public function down()
    {
        Schema::dropIfExists('legal_case_updates');
        Schema::dropIfExists('legal_stages');
        Schema::table('legal', function (Blueprint $table) {
            $table->dropColumn(['handled_by', 'handed_to_name', 'handover_ref', 'handover_date', 'case_type', 'priority',
                'lawyer_user_id', 'stage_id', 'claim_amount', 'court_case_no', 'court_name', 'filed_date',
                'next_hearing_date', 'judgment_amount', 'close_reason', 'closed_at', 'closed_by', 'parent_legal_id']);
        });
    }
}
