<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComplaintServiceReportImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('complaint_service_report_image', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('complaint_service_report_id')->nullable();              
            $table->longText('image_path_file_name')->nullable();
            $table->longText('image_path_thumbnail')->nullable();
            $table->integer('stage')->comment('0 - open, 1- inprogress,2-attend,3-completed,4-closed');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('complaint_service_report_image', function($table) {
            $table->foreign('complaint_service_report_id')->references('id')->on('complaint_service_report');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('complaint_service_report_image');

    }
}
