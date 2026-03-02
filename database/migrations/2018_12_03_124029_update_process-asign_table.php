<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateProcessAsignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('process_assigns', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('work_flow_process_id');
            $table->unsignedInteger('location_id');
            $table->unsignedInteger('price_range_id');            
            $table->unsignedInteger('tenant_status_id')->nullable();
            $table->unsignedInteger('process_assign_status')->default(1);     
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        
        Schema::table('process_assigns', function($table) {
            $table->foreign('work_flow_process_id')->references('id')->on('work_flow_processes');
        });

        Schema::table('process_assigns', function($table) {
            $table->foreign('location_id')->references('id')->on('locations');
        });

        Schema::table('process_assigns', function($table) {
            $table->foreign('price_range_id')->references('id')->on('price_ranges');
        });


        Schema::create('process_assign_users', function (Blueprint $table) {
            $table->unsignedInteger('process_assigns_id');
            $table->unsignedInteger('role_id')->nullable();
            $table->unsignedInteger('user_id');
         });

        Schema::table('process_assign_users', function($table) {
            $table->foreign('process_assigns_id')->references('id')->on('process_assigns');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('process_assign_users');
        Schema::dropIfExists('process_assigns');
    }
}
