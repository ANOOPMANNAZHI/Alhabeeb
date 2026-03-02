<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcessActionLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('process_action_links', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('work_flow_processes_code')->nullable();
            $table->unsignedInteger('actions_id')->nullable();
            $table->unsignedInteger('next_processes_code')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('process_action_links', function($table) {
            $table->foreign('work_flow_processes_code')->references('id')->on('work_flow_processes');
        });
        Schema::table('process_action_links', function($table) {
            $table->foreign('actions_id')->references('id')->on('actions');
        });
        Schema::table('process_action_links', function($table) {
            $table->foreign('next_processes_code')->references('id')->on('work_flow_processes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('process_action_links');
    }
}
