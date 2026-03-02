<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkFlowProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	  if (!Schema::hasTable('work_flow_processes')) {
        Schema::create('work_flow_processes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('work_flows_id');
            $table->string('work_flow_processes_code')->unique();            
            $table->string('work_flow_processes_name')->unique()->comment('Stages (Unassigned, Assigned, approval, documentation etc)');
            $table->tinyInteger('process_assign_order')->default(0)->comment('work flow stage order ');
            $table->tinyInteger('process_assign_level')->default(0)->comment('0 - inprogress, 1 - completed');
            $table->tinyInteger('process_assign_status')->default(1)->comment('1-Active,0-Inactive');
            $table->tinyInteger('work_flow_processes_status')->default(1)->comment('1-Active,0-Inactive');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('work_flow_processes', function($table) {
            $table->foreign('work_flows_id')->references('id')->on('work_flows');
        });
	}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_flow_processes');
    }
}
