<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildingEleWaterReadingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('building_ele_water_readings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('building_id');
            $table->string('building_meter_category')->nullable();
            $table->string('electricity_acc_no')->nullable();
            $table->string('electricity_met_no')->nullable();
            $table->string('water_acc_no')->nullable();
            $table->string('water_met_no')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('building_ele_water_readings', function($table) {
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
        Schema::dropIfExists('building_ele_water_readings');
    }
}
