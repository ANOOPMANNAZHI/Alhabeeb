<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractorWorkLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contractor_work_links', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id')->comment('Linking Contractors with Works (Electrical, Plimbing, Painting)');
            $table->integer('work_id');
            $table->tinyInteger('contractor_work_status')->default(1)->comment('1-Active,0-Inactive');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('contractor_work_links', function($table) {
            $table->foreign('vendor_id')->references('id')->on('vendors');
        });
        Schema::table('contractor_work_links', function($table) {
            $table->foreign('work_id')->references('id')->on('works');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contractor_work_links');
    }
}
