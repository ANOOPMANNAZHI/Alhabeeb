<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnittypeCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unit_type_count', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('building_id');
            $table->integer('unit_type_id');
            $table->integer('unittype_count');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::table('unit_type_count', function($table) {
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
        Schema::dropIfExists('unit_type_count');
    }
}
