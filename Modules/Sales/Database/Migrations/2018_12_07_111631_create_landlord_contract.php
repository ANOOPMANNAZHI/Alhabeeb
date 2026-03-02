<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLandlordContract extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('landlord_contract', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('building_id');
            $table->unsignedInteger('vendor_id');
            $table->unsignedInteger('sale_enquiry_id');
            $table->string('landlord_contract_name');
            $table->string('landlord_contract_old_no')->unique()->nullable();
            $table->string('landlord_contract_no')->unique();
            $table->string('close_activity')->unique();
            $table->longText('landlord_contract_address')->nullable();
            $table->unsignedInteger('landlord_contract_duration')->nullable();
            $table->unsignedInteger('landlord_contract_duration_type')->nullable()->comment('1 - Month, 2 - Year, 3 - Day');
            $table->unsignedInteger('landlord_contract_management_fee')->nullable();
            $table->float('landlord_contract_percentage',3,2)->nullable();
            $table->float('landlord_contract_amt',15,2);
            $table->float('landlord_contract_agreement_amt',10,2)->nullable();
            $table->unsignedInteger('management_id')->nullable();
            $table->date('landlord_free_lease_period')->nullable()->comment('Free lease period in month');
            $table->unsignedInteger('termination_notification')->nullable();
            $table->date('start_date')->nullable();
            $table->unsignedInteger('landlord_duration')->nullable();
            $table->unsignedInteger('landlord_marketing_executive')->nullable()->comment('Shouild be a employeeId');
            $table->unsignedInteger('user_id');
            $table->date('landlord_contract_valid_from_date')->nullable();
            $table->date('landlord_contract_valid_to_date')->nullable();
            $table->unsignedInteger('landlord_contract_payment_type')->default(1)->comment('1 - monthly, 2 - bi-monthly, 3 - half yearly');
            $table->integer('landlord_contract_status')->default(0);
            $table->mediumText('landlord_contract_note')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();    
           
        });
        Schema::table('landlord_contract', function($table) {
            $table->foreign('building_id')->references('id')->on('buildings');
        });
        Schema::table('landlord_contract', function($table) {
            $table->foreign('management_id')->references('id')->on('management_types');
        });
        Schema::table('landlord_contract', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('landlord_contract');
    }
}
