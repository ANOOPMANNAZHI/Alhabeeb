<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Legal module phase 4 (plan 2026-10-13): a waiver PLM verifies on a legal settlement is a credit on the
 * contract and must leave every receivable. One row per waiver; cancelled_at set when the deal falls through
 * (settlement broken, or the case reopened with "waiver no longer applies").
 * Backfills verified settlements that already carry a waiver.
 */
class LegalWaivers extends Migration
{
    public function up()
    {
        Schema::create('legal_waivers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('legal_id')->index();
            $table->integer('settlement_id')->index();
            $table->integer('tenant_contract_id')->index();
            $table->decimal('amount', 14, 3);
            $table->string('reason', 500)->nullable();
            $table->integer('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason', 200)->nullable();
            $table->timestamps();
        });

        $now = now();
        $rows = DB::table('legal_settlements as s')->join('legal as l', 'l.id', '=', 's.legal_id')
            ->where('s.waiver_amount', '>', 0)->whereIn('s.status', [1, 2, 3])
            ->select('s.*', 'l.tenant_contract_id')->get();
        foreach ($rows as $s) {
            $cancelled = $s->waiver_cancelled_at ?: ((int) $s->status === 3 ? $s->broken_at : null);
            DB::table('legal_waivers')->insert(['legal_id' => $s->legal_id, 'settlement_id' => $s->id, 'tenant_contract_id' => $s->tenant_contract_id,
                'amount' => $s->waiver_amount, 'reason' => $s->waiver_reason, 'approved_by' => $s->verified_by, 'approved_at' => $s->verified_at,
                'cancelled_at' => $cancelled, 'cancel_reason' => $cancelled ? 'before table' : null, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('legal_waivers');
    }
}
