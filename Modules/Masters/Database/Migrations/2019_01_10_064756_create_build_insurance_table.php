<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildInsuranceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('building_insurance', function (Blueprint $table) {
            $table->increments('id');
            $table->string('insurance_company')->unique();
            $table->unsignedInteger('building_id');
            $table->string('insurance_policy_type');
            $table->unsignedInteger('insurance_building_value');
            $table->unsignedInteger('insurance_premium_value');
            $table->dateTime('insurance_start');
            $table->dateTime('insurance_end');
            $table->string('insurance_insured_by');
            $table->string('insurance_debit_acc')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

         Schema::table('building_insurance', function($table) {
            $table->foreign('building_id')->references('id')->on('buildings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('building_insurance');
    }
}
