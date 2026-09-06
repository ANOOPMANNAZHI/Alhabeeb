<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropCleaningChargeMethodFromLandlordContract extends Migration
{
    public function up()
    {
        Schema::table('landlord_contract', function (Blueprint $table) {
            $table->dropColumn('cleaning_charge_method');
        });
    }

    public function down()
    {
        Schema::table('landlord_contract', function (Blueprint $table) {
            $table->unsignedInteger('cleaning_charge_method')->nullable()->after('management_method');
        });
    }
}
