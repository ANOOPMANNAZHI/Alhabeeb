<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHomeUtilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('home_utilities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('home_utilities_code')->unique()->comment('Fridge, Sofa, AC, Inverter, Washing machine');
            $table->longText('home_utilities_desc')->nullable();
            $table->string('home_utilities_make')->nullable();
            $table->string('home_utilities_model')->nullable();
            $table->string('home_utilities_serial_no')->nullable();
            $table->tinyInteger('home_utilities_status')->default(1)->comment('1-Active,0-Inactive');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('home_utilities');
    }
}
