<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('works', function (Blueprint $table) {
            $table->increments('id');
            $table->string('works_code')->unique()->comment('Electrical, Plimbing, Painting');
            $table->longText('works_desc')->nullable();
            $table->tinyInteger('works_status')->default(1)->comment('1-Active,0-Inactive');
            $table->tinyInteger('works_type')->default(0)->comment('0-Both,1 - Maintenance, 2 - AMC');
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
        Schema::dropIfExists('works');
    }
}
