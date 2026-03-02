<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_activities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sales_id');
            $table->unsignedInteger('user_id');
            $table->string('sales_activities_name');
            $table->longText('sales_activities_note')->nullable();
            $table->longText('sales_activities_summary_note')->nullable();
            $table->integer('sales_activity_type')->comment('1 - email,  2 -phone, 3 - task, 4 -appointment');
            $table->tinyInteger('sales_activities_status')->default(1)->comment('1 - Not attend, 2 - Attend , 3 - Close');
            $table->date('sales_activities_due_date');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('sales_activities', function($table) {
            $table->foreign('sales_id')->references('id')->on('sales');
        });
        Schema::table('sales_activities', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_activities');
    }
}
