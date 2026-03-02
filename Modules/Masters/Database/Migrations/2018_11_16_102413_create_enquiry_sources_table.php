<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnquirySourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enquiry_sources', function (Blueprint $table) {
            $table->increments('id');
            $table->string('enquiry_sources_name')->unique()->comment('Facebook.com, Newspaper, website, callcenter, olx etc');
            $table->tinyInteger('enquiry_sources_status')->default(1)->comment('1-Active,0-Inactive');
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
        Schema::dropIfExists('enquiry_sources');
    }
}
