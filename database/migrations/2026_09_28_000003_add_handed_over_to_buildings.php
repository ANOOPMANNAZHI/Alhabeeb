<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Marks buildings handed back to the landlord. The final Landlord Termination
 * step already sets building_status = 0 (Inactive); this flag and date tell a
 * handed-over building apart from one that is inactive for other reasons.
 * Backfill: inactive buildings whose landlord contracts are all terminated
 * (landlord_renewal_termination_status = 8) and none active; the date is the
 * latest terminated contract's end_date, else its valid-to date.
 */
class AddHandedOverToBuildings extends Migration
{
    public function up()
    {
        Schema::table('buildings', function (Blueprint $table) {
            if (!Schema::hasColumn('buildings', 'building_handed_over')) {
                $table->boolean('building_handed_over')->default(false);
            }
            if (!Schema::hasColumn('buildings', 'building_handed_over_date')) {
                $table->date('building_handed_over_date')->nullable();
            }
        });

        DB::statement("
            UPDATE buildings b
               SET building_handed_over = true,
                   building_handed_over_date = (
                       SELECT COALESCE(lc.end_date, lc.landlord_contract_valid_to_date::date)
                         FROM landlord_contract lc
                        WHERE lc.building_id = b.id AND lc.landlord_renewal_termination_status = 8
                        ORDER BY COALESCE(lc.end_date, lc.landlord_contract_valid_to_date::date) DESC NULLS LAST, lc.id DESC
                        LIMIT 1)
             WHERE b.building_status = 0
               AND b.building_handed_over = false
               AND EXISTS (SELECT 1 FROM landlord_contract lc WHERE lc.building_id = b.id AND lc.landlord_renewal_termination_status = 8)
               AND NOT EXISTS (SELECT 1 FROM landlord_contract lc WHERE lc.building_id = b.id AND lc.landlord_contract_status = 1)
        ");
    }

    public function down()
    {
        Schema::table('buildings', function (Blueprint $table) {
            foreach (['building_handed_over', 'building_handed_over_date'] as $col) {
                if (Schema::hasColumn('buildings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
