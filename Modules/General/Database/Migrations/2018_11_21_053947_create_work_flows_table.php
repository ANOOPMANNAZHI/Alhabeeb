<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkFlowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_flows', function (Blueprint $table) {
            $table->increments('id');
            $table->string('work_flows_name')->unique()->comment('Sales Tenent,landloard Sales, Complaints,etc');
            $table->tinyInteger('work_flows_status')->default(1)->comment('1-Active,0-Inactive');
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
        Schema::dropIfExists('work_flows');
    }
}
