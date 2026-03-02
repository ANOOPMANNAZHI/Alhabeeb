<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAreBuildingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('are_buildings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->comment('ARE id');
            $table->unsignedInteger('building_id');
            $table->date('assign_from'); 
            $table->date('assign_to'); 
            $table->timestamps();
        });
        Schema::table('are_buildings', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
         Schema::table('are_buildings', function($table) {
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
        Schema::dropIfExists('are_buildings');
       /* Schema::dropIfExists('preferred_buildings');*/
    }
}
