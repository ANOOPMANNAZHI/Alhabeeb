<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreferredTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('preferred_time')) {
        Schema::create('preferred_time', function (Blueprint $table) {
            $table->increments('id');
            $table->string('time');
            $table->timestamps();
        });
       }

        Schema::create('complaint_preferred_time', function (Blueprint $table)  {
            $table->unsignedInteger('complaint_enquiry_id');
            $table->unsignedInteger('preferred_time_id');

            $table->foreign('complaint_enquiry_id')
                ->references('id')
                ->on('complaint_enquiries')
                ->onDelete('cascade');

            $table->foreign('preferred_time_id')
                ->references('id')
                ->on('preferred_time')
                ->onDelete('cascade');
            $table->primary(['complaint_enquiry_id', 'preferred_time_id']);            
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('preferred');
    }
}
