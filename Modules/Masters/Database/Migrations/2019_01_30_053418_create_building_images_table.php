<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildingImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('building_images', function (Blueprint $table) {

            $table->increments('id');
            $table->unsignedInteger('building_id');
            $table->longText('building_path_file_name')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('building_images', function($table) {
            $table->foreign('building_id')->references('id')->on('buildings');
        });
        Schema::create('building_docs', function (Blueprint $table) {

            $table->increments('id');
            $table->unsignedInteger('building_id');
            $table->longText('building_doc_path_name')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('building_docs', function($table) {
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
        Schema::dropIfExists('building_images');
        Schema::dropIfExists('building_docs');
    }
}
