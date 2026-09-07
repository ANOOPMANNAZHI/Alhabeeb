<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeeComponentsToLandlordContract extends Migration
{
    public function up()
    {
        Schema::table('landlord_contract', function (Blueprint $table) {
            $table->double('landlord_contract_facility_management_fee')->nullable()->after('landlord_contract_cleaning_charge');
            $table->double('landlord_contract_renewal_fee')->nullable()->after('landlord_contract_facility_management_fee');
            $table->unsignedInteger('landlord_contract_new_leasing_fee_type')->nullable()->after('landlord_contract_renewal_fee');
            $table->double('landlord_contract_new_leasing_fee')->nullable()->after('landlord_contract_new_leasing_fee_type');
        });
    }

    public function down()
    {
        Schema::table('landlord_contract', function (Blueprint $table) {
            $table->dropColumn([
                'landlord_contract_facility_management_fee',
                'landlord_contract_renewal_fee',
                'landlord_contract_new_leasing_fee_type',
                'landlord_contract_new_leasing_fee',
            ]);
        });
    }
}
