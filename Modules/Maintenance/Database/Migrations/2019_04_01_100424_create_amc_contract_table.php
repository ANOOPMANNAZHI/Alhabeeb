<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAmcContractTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amc_contract', function (Blueprint $table) {
            $table->increments('id');
            $table->string('amc_contract_no');
            $table->date('amc_contract_period_from'); 
            $table->date('amc_contract_period_to'); 
            $table->unsignedInteger('building_id');
            $table->unsignedInteger('vendor_id');
            $table->unsignedInteger('payment_method_id')->comment('Contract Frequency');
            $table->string('amc_contract_cost');
            $table->tinyInteger('amc_contract_status')->default(0)->comment('0 - Active , 1 - Cancelled');

            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('amc_contract', function($table) {
            $table->foreign('building_id')->references('id')->on('buildings');
        });
        Schema::table('amc_contract', function($table) {
            $table->foreign('vendor_id')->references('id')->on('vendors');
        });
        Schema::table('amc_contract', function($table) {
            $table->foreign('payment_method_id')->references('id')->on('payment_method');
        });

        /************************************************************************************/

        Schema::create('amc_contract_amenities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('amc_contract_id'); 
            $table->unsignedInteger('amenities_type_id'); 
        });
        Schema::table('amc_contract_amenities', function($table) {
            $table->foreign('amc_contract_id')->references('id')->on('amc_contract');
        });
        Schema::table('amc_contract_amenities', function($table) {
            $table->foreign('amenities_type_id')->references('id')->on('amentity_types');
        });

        /*************************************************************************************/

        Schema::create('amc_schedule', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('amc_contract_id')->nullable(); 
            $table->date('amc_schedule_period_from'); 
            $table->date('amc_schedule_period_to'); 
            $table->unsignedInteger('user_id')->nullable()->comment('Technician id');
            $table->unsignedInteger('building_id'); 
            $table->unsignedInteger('unit_id')->nullable(); 
            $table->unsignedInteger('vendor_id')->nullable(); 
            $table->unsignedInteger('payment_method_id')->comment('AMC Frequency');
            $table->longText('amc_schedule_description');
            $table->tinyInteger('amc_schedule_status')->default(0)->comment('0 - Scheduled , 1 - Closed , 2 - Cancelled');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('amc_schedule', function($table) {
            $table->foreign('amc_contract_id')->references('id')->on('amc_contract');
        });
        Schema::table('amc_schedule', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('amc_schedule', function($table) {
            $table->foreign('building_id')->references('id')->on('buildings');
        });
        Schema::table('amc_schedule', function($table) {
            $table->foreign('unit_id')->references('id')->on('units');
        });
        Schema::table('amc_schedule', function($table) {
            $table->foreign('vendor_id')->references('id')->on('vendors');
        });
        Schema::table('amc_schedule', function($table) {
            $table->foreign('payment_method_id')->references('id')->on('payment_method');
        });

        /*************************************************************************************/

        Schema::create('amc_schedule_amenities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('amc_schedule_id'); 
            $table->unsignedInteger('amenities_type_id'); 
        });
        Schema::table('amc_schedule_amenities', function($table) {
            $table->foreign('amc_schedule_id')->references('id')->on('amc_schedule');
        });
        Schema::table('amc_schedule_amenities', function($table) {
            $table->foreign('amenities_type_id')->references('id')->on('amentity_types');
        });

        /*************************************************************************************/

        Schema::create('amc_schedule_task', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('amc_schedule_id'); 
            $table->unsignedInteger('amenities_type_id'); 
            $table->date('amc_schedule_from_date'); 
            $table->date('amc_schedule_to_date'); 
            $table->tinyInteger('amc_schedule_task_status')->default(0)->comment('0 - Open , 1 - Closed ');
            $table->longText('amc_schedule_task_remarks')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('amc_schedule_task', function($table) {
            $table->foreign('amc_schedule_id')->references('id')->on('amc_schedule');
        });
        Schema::table('amc_schedule_task', function($table) {
            $table->foreign('amenities_type_id')->references('id')->on('amentity_types');
        });


    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amc_contract');
        Schema::dropIfExists('amc_contract_amenities');
        Schema::dropIfExists('amc_schedule');
        Schema::dropIfExists('amc_schedule_amenities');
        Schema::dropIfExists('amc_schedule_task');
    }
}
