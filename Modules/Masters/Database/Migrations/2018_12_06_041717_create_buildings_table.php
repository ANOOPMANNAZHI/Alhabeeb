<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('building_code')->unique();
            $table->string('building_name');
            $table->unsignedInteger('vendor_id');
            $table->string('building_no');
            $table->unsignedInteger('building_type_id');
            $table->unsignedInteger('management_id');
            $table->longText('building_address');
            $table->string('building_pc');
            $table->unsignedInteger('location_id');
            $table->longText('building_note');
            $table->integer('building_status');
            $table->string('google_location');
            $table->string('building_geo_long');
            $table->string('building_geo_lat');
            $table->integer('building_no_floor');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::table('buildings', function($table) {
            $table->foreign('vendor_id')->references('id')->on('vendors');
            $table->foreign('building_type_id')->references('id')->on('building_types');
            $table->foreign('management_id')->references('id')->on('management_types');
            $table->foreign('location_id')->references('id')->on('locations');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buildings');
    }
}
