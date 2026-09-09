<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds an optional SWIFT/BIC code to vendors (landlord bank details on the
 * Masters -> Vendors add/edit screen). Same shape as the VATIN No migration.
 */
class AddSwiftCodeToVendors extends Migration
{
    public function up()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('swift_code', 11)->nullable()->after('vatin_no');
        });
    }

    public function down()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('swift_code');
        });
    }
}
