<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Masters\Entities\PdcBounceReason;

/**
 * Masters -> Bounce Reasons. Codes 1-9 were built into the code and keep their numbers
 * (pdc.pdc_bounce_reason); Misc (9) arrives switched off and 10 "Account Closed / Frozen /
 * Dormant" is added. Insert-only: rows edited on the screen are never overwritten.
 */
class CreatePdcBounceReasons extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pdc_bounce_reasons')) {
            Schema::create('pdc_bounce_reasons', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100);
                $table->string('bank_keywords', 500)->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_fallback')->default(false);
                $table->integer('created_by')->nullable();
                $table->integer('updated_by')->nullable();
                $table->timestamps();
            });
        }
        foreach (PdcBounceReason::DEFAULTS as $d) {
            if (!DB::table('pdc_bounce_reasons')->where('id', $d['id'])->exists()) {
                DB::table('pdc_bounce_reasons')->insert($d + ['created_at' => now(), 'updated_at' => now()]);
            }
        }
        // ids were inserted explicitly: move the sequence past them so new reasons get 11, 12, ...
        DB::statement("SELECT setval(pg_get_serial_sequence('pdc_bounce_reasons', 'id'), (SELECT MAX(id) FROM pdc_bounce_reasons))");
    }

    public function down()
    {
        Schema::dropIfExists('pdc_bounce_reasons');
    }
}
