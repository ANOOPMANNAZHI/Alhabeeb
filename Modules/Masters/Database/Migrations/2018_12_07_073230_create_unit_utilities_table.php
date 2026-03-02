<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitUtilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unit_utilities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('home_utility_id');
            $table->unsignedInteger('unit_id');
            $table->longText('unit_utilities_remark');
            $table->string('amc_contract_no');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable(); 
            $table->timestamps();
        });

           Schema::table('unit_utilities', function($table) {
            $table->foreign('home_utility_id')->references('id')->on('home_utilities');
            $table->foreign('unit_id')->references('id')->on('units');
        });
    }  
  

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unit_utilities');
    }
}
