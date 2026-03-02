<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->increments('id');           
            $table->unsignedInteger('sales_enquiry_id');
            $table->integer('sales_type')->comment('1 - tenant, 2 - landlord');          
            $table->unsignedInteger('work_flow_process_id');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
     
        Schema::table('sales', function($table) {
            $table->foreign('sales_enquiry_id')->references('id')->on('sales_enquiries');
        });
        Schema::table('sales', function($table) {
            $table->foreign('work_flow_process_id')->references('id')->on('work_flow_processes');
        });

 ////////////////////////////////sales_users//////////////////

       Schema::create('sales_users', function (Blueprint $table) {
            $table->unsignedInteger('sales_id');
            $table->unsignedInteger('user_id')->nullable();    
            $table->unsignedInteger('role_id');
           
        });

        Schema::table('sales_users', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('sales_users', function($table) {
            $table->foreign('sales_id')->references('id')->on('sales');
        });

        Schema::table('sales_users', function($table) {
            $table->foreign('role_id')->references('id')->on('roles');
        });


    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales');
        Schema::dropIfExists('sales_users');
    }
}
