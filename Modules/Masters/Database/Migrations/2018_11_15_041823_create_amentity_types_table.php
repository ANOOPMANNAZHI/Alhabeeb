<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAmentityTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amentity_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('amentity_types_name')->unique()->comment('Lift, Fire alarm, Fire-pump');
            $table->longText('amentity_types_desc')->nullable();
            $table->tinyInteger('amentity_types_status')->default(1)->comment('1-Active,0-Inactive');
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
        Schema::dropIfExists('amentity_types');
    }
}
