<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkFlowCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_flow_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('assign_field_name');
            $table->unsignedInteger('location_id')->nullable();
            $table->unsignedInteger('price_range_id')->nullable();
            $table->tinyInteger('assign_field_status')->default(1)->comment('1-Active,0-Inactive');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('work_flow_categories', function($table) {
            $table->foreign('location_id')->references('id')->on('locations');
        });
        Schema::table('work_flow_categories', function($table) {
            $table->foreign('price_range_id')->references('id')->on('price_ranges');
        });
    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_flow_categories');
    }
}
