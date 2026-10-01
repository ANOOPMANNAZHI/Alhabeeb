<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Building is taxable (yes/no). New and existing buildings start as taxable.
 */
class AddIsTaxableToBuildings extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('buildings', 'building_is_taxable')) {
            Schema::table('buildings', function (Blueprint $table) {
                $table->boolean('building_is_taxable')->default(true);
            });
        }
        DB::table('buildings')->whereNull('building_is_taxable')->update(['building_is_taxable' => true]);
    }

    public function down()
    {
        if (Schema::hasColumn('buildings', 'building_is_taxable')) {
            Schema::table('buildings', function (Blueprint $table) {
                $table->dropColumn('building_is_taxable');
            });
        }
    }
}
