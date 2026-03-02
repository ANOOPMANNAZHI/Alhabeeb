<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       /* Schema::create('job_category', function (Blueprint $table) {
            $table->increments('id');
            $table->string('job_category_name')->unique();
            $table->string('job_category_code')->unique();
            $table->tinyInteger('job_category_status')->default(1)->comment('1-Active,0-Inactive');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       /* Schema::dropIfExists('job_category');*/
    }
}
