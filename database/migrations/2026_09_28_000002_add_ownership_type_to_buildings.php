<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Building ownership type (optional): 1 MERA, 2 Government, 3 Family Property,
 * 4 Individual. Existing buildings start as Individual.
 */
class AddOwnershipTypeToBuildings extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('buildings', 'building_ownership_type')) {
            Schema::table('buildings', function (Blueprint $table) {
                $table->smallInteger('building_ownership_type')->nullable()->default(4);
            });
        }
        DB::table('buildings')->whereNull('building_ownership_type')->update(['building_ownership_type' => 4]);
    }

    public function down()
    {
        if (Schema::hasColumn('buildings', 'building_ownership_type')) {
            Schema::table('buildings', function (Blueprint $table) {
                $table->dropColumn('building_ownership_type');
            });
        }
    }
}
