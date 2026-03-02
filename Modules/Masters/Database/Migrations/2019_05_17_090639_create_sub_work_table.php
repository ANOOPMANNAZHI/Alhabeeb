<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubWorkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_work', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('works_id');
            $table->string('sub_work');
            $table->tinyInteger('sub_work_status')->default(0)->comment('0 - Active , 1 - Inactive');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('sub_work', function($table) {
            $table->foreign('works_id')->references('id')->on('works');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sub_work');
    }
}
