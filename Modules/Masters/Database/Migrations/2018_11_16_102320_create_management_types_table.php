<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManagementTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('management_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('management_types_name')->unique()->comment('Comprehensive, Normal, Commission');
            $table->longText('management_types_desc')->nullable();
            $table->tinyInteger('management_types_status')->default(1)->comment('1-Active,0-Inactive');
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
        Schema::dropIfExists('management_types');
    }
}
