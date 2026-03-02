<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildingAmentityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('building_amentity', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('amentity_type_id');
            $table->unsignedInteger('landlord_building_id');
            $table->longText('building_amentity_utilities_remark');
            $table->string('amc_contract_no');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable(); 
            $table->timestamps();
        });

        Schema::table('building_amentity', function($table) {
            $table->foreign('amentity_type_id')->references('id')->on('amentity_types');
            $table->foreign('landlord_building_id')->references('id')->on('buildings');
        });
    }
  
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('building_amentity');
    }
}
