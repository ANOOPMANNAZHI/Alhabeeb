<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('units', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unit_code')->unique();
            $table->unsignedInteger('building_id');
            $table->unsignedInteger('unit_type_id');
            $table->integer('unit_toilets');
            $table->integer('unit_floor');
            $table->string('unit_electric_meter');
            $table->string('unit_electric_consumer_no');
            $table->string('unit_water_meter');
            $table->string('unit_water_consumer_no');
            $table->integer('unit_vaccant_status')->comment('0 - vaccant, 1 - occupied');
            $table->integer('unit_is_legal')->comment(' 1 - yes, 2 - No ');           
            $table->date('unit_legal_date')->nullable();           
            $table->integer('unit_is_furnished')->comment('1 - furnished , 2- unfurnished  ');               
            $table->longText('unit_note');            
            $table->integer('unit_status')->comment('1 - active , 0 - inactive');
          
            $table->integer('created_by');            
            $table->integer('updated_by')->nullable(); 
            $table->timestamps();
        });

        Schema::table('units', function($table) {
            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('unit_type_id')->references('id')->on('unit_types');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('units');
    }
}
