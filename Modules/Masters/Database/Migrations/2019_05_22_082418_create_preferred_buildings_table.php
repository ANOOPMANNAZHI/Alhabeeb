<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreferredBuildingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preferred_buildings', function (Blueprint $table) {          
            $table->unsignedInteger('building_id');         
            $table->unsignedInteger('are_building_id');
            $table->date('assign_from'); 
            $table->date('assign_to'); 
            $table->timestamps();

            $table->foreign('building_id')
                    ->references('id')
                    ->on('buildings')
                    ->onDelete('cascade');

            $table->foreign('are_building_id')
                    ->references('id')
                    ->on('are_buildings')
                    ->onDelete('cascade');
         });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('preferred_buildings');
    }
}
