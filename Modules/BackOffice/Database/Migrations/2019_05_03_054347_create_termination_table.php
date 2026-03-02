<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTerminationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('termination', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contract_id');
            $table->unsignedInteger('work_flow_processes_code')->nullable();
            $table->longText('termination_notes')->nullable();
            $table->integer('termination_type')->comment('1 - tenant, 2 - landlord');
            $table->longText('termination_remark')->nullable();
            $table->integer('termination_main_key_status')->comment('1-Received,0-Not Received')->nullable();
            $table->float('termination_amount')->nullable();
            $table->integer('termination_type_status')->default(0)->comment('0=>normal,1=>premature,2=>under approval,3=>rejected,4=>approved');
            $table->integer('assigned_to')->nullable();
            $table->string('termination_tenant_signature')->nullable();
            $table->string('termination_electricity_acc_no')->nullable();
            $table->string('termination_electricity_close_reading')->nullable();
            $table->float('termination_electricity_amount')->nullable();
            $table->string('termination_water_acc_no')->nullable();
            $table->string('termination_water_close_reading')->nullable();
            $table->float('termination_water_amount')->nullable();
            $table->date('termination_takenover_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->float('termination_total_amount')->nullable();
            $table->float('termination_total_elec_water_amount')->nullable();
            $table->float('termination_discount_maintenance_due')->nullable();
            $table->float('termination_net_amount')->nullable();
            $table->integer('termination_review_status')->comment('0=>inspection_complete,1=>send for review')->nullable();

            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();

        });
        Schema::table('termination', function($table) {
            $table->foreign('work_flow_processes_code')->references('work_flow_processes_code')->on('work_flow_processes');
        });
        ///////////////////////////////////////////////////////////////////////

        Schema::create('termination_users', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('termination_id')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 Active, 0 inactive');
        });
        Schema::table('termination_users', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('termination_users', function($table) {
            $table->foreign('role_id')->references('id')->on('roles');
        });
        Schema::table('termination_users', function($table) {
            $table->foreign('termination_id')->references('id')->on('termination');
        });
        ///////////////////////////////////////////////////////////////////////

        Schema::create('termination_document', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('termination_id')->nullable();
            $table->string('termination_doc')->nullable();
            $table->string('termination_doc_type')->nullable();
            $table->string('image_path_thumbnail')->nullable();
            $table->string('termination_doc_name')->nullable();
            $table->unsignedInteger('work_flow_processes_code')->nullable();
            $table->unsignedInteger('termination_contract')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();

        });
        Schema::table('termination_document', function($table) {
            $table->foreign('termination_id')->references('id')->on('termination');
        });
        Schema::table('termination_document', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('termination_document', function($table) {
            $table->foreign('work_flow_processes_code')->references('work_flow_processes_code')->on('work_flow_processes');
        });
        
        ///////////////////////////////////////////////////////////////////////

        Schema::create('termination_checklists', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('termination_id')->nullable();
            $table->unsignedInteger('sub_work_id')->nullable();
            $table->float('termination_amount')->nullable();
            $table->float('termination_quantity')->nullable();
            $table->string('termination_other_work')->nullable();
            $table->unsignedInteger('work_id')->nullable();
            $table->unsignedInteger('work_flow_processes_code')->nullable();
            $table->unsignedInteger('termination_contract_id')->nullable();

        });
        Schema::table('termination_checklists', function($table) {
            $table->foreign('termination_id')->references('id')->on('termination');
        });
        Schema::table('termination_checklists', function($table) {
            $table->foreign('sub_work_id')->references('id')->on('sub_work');
        });
        Schema::table('termination_checklists', function($table) {
            $table->foreign('work_flow_processes_code')->references('work_flow_processes_code')->on('work_flow_processes');
        });
        
        Schema::table('termination_checklists', function($table) {
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
        Schema::dropIfExists('termination');
        Schema::dropIfExists('termination_users');
        Schema::dropIfExists('termination_document');
        Schema::dropIfExists('termination_checklists');
    }
}
