<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComplaintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('complaint_enquiries', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('complaint_date');
            $table->string('complaint_no');
            $table->string('complainer_name');
            $table->string('complaint_mob_no');
            $table->unsignedInteger('building_id')->nullable();              
            $table->unsignedInteger('location_id')->nullable();
            $table->unsignedInteger('unit_id')->nullable();
            $table->string('way_no')->nullable();
            $table->unsignedInteger('occupant_id')->nullable();
            $table->unsignedInteger('tenant_id')->nullable();
            $table->enum('tenant_status', ['Normal', 'On Hold', 'No Maintenance', 'Blacklist', 'Legal','Maintained By Landlord']);
            $table->tinyInteger('priority_status')->default(0)->comment('0 - Normal , 1 - high');
            $table->tinyInteger('complaint_status')->default(0)->comment('0 - open ,1 - Partialy Closed,2 - Closed');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('complaint_enquiries', function($table) {
            $table->foreign('building_id')->references('id')->on('buildings');
        });
        
        Schema::table('complaint_enquiries', function($table) {
            $table->foreign('unit_id')->references('id')->on('units');
        });
        Schema::table('complaint_enquiries', function($table) {
            $table->foreign('location_id')->references('id')->on('locations');
        });
        Schema::table('complaint_enquiries', function($table) {
            $table->foreign('occupant_id')->references('id')->on('occupants');
        });
        Schema::table('complaint_enquiries', function($table) {
            $table->foreign('tenant_id')->references('id')->on('tenant');
        });
        /***********************************************************************************/
        
        Schema::create('complaint_checklists', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('work_id'); 
            $table->unsignedInteger('complaint_enquiries_id'); 
            $table->longText('checklist_desc')->nullable();
            $table->string('checklist_name')->nullable();
            $table->string('complaint_ticket_no')->nullable();
            $table->integer('assigned_to')->nullable();
            $table->integer('assigned_to_type')->nullable()->comment('0=technical head, 1= Subcontractor');
            $table->integer('sub_assigned_to')->nullable();
            $table->unsignedInteger('work_flow_processes_code');
            $table->tinyInteger('service_report_status')->default(0)->comment('default 0, generated = 1');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('complaint_checklists', function($table) {
            $table->foreign('work_id')->references('id')->on('works');
        });
        Schema::table('complaint_checklists', function($table) {
            $table->foreign('complaint_enquiries_id')->references('id')->on('complaint_enquiries');
        });
        Schema::table('complaint_checklists', function($table) {
            $table->foreign('work_flow_processes_code')->references('id')->on('work_flow_processes');
        });
        /***********************************************************************************/
        Schema::create('complaint_processes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('complaint_checklists_id'); 
            $table->unsignedInteger('complaint_enquiries_id'); 
            $table->unsignedInteger('work_flow_processes_code'); 
            $table->longText('complaint_processes_note')->nullable();
            $table->integer('assigned_to')->nullable();
            $table->integer('assigned_to_type')->nullable()->comment('0=technical head, 1= Subcontractor');
            $table->integer('sub_assigned_to')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('complaint_processes', function($table) {
            $table->foreign('complaint_enquiries_id')->references('id')->on('complaint_enquiries');
        });
        Schema::table('complaint_processes', function($table) {
            $table->foreign('complaint_checklists_id')->references('id')->on('complaint_checklists');
        });
        Schema::table('complaint_processes', function($table) {
            $table->foreign('work_flow_processes_code')->references('id')->on('work_flow_processes');
        });
        /***********************************************************************************/
        Schema::create('complaint_users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('complaint_processes_id'); 
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('role_id'); 
            $table->tinyInteger('status')->default(1)->comment('0 - inactive , 1 - active');
        });
        Schema::table('complaint_users', function($table) {
            $table->foreign('complaint_processes_id')->references('id')->on('complaint_processes');
        });
        Schema::table('complaint_users', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('complaint_users', function($table) {
            $table->foreign('role_id')->references('id')->on('roles');
        });
        /***********************************************************************************/
        Schema::create('complaint_service_report', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id'); 
            $table->string('service_report_no');
            $table->longText('tenant_signature')->nullable();
            $table->unsignedInteger('tenant_id')->nullable(); 
            $table->longText('complaint_assign_note')->nullable();
            $table->tinyInteger('complaint_assign_status')->default(0)->comment('0 - open, 1- inprogress,2-attend,3-completed,4-closed');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('complaint_service_report', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('complaint_service_report', function($table) {
            $table->foreign('tenant_id')->references('id')->on('tenant');
        });
        /***********************************************************************************/
        Schema::create('complaint_service_report_checklist', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('checklist_id'); 
            $table->unsignedInteger('complaint_service_report_id'); 


        });
        Schema::table('complaint_service_report_checklist', function($table) {
            $table->foreign('checklist_id')->references('id')->on('complaint_checklists');
        });
        Schema::table('complaint_service_report_checklist', function($table) {
            $table->foreign('complaint_service_report_id')->references('id')->on('complaint_service_report');
        });
        /***********************************************************************************/
        Schema::create('complaint_service_report_inv', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('complaint_service_report_id');
            $table->unsignedInteger('inventory_id');
            $table->float('quantity');

        });
        Schema::table('complaint_service_report_inv', function($table) {
            $table->foreign('complaint_service_report_id')->references('id')->on('complaint_service_report');
        });
        Schema::table('complaint_service_report_inv', function($table) {
            $table->foreign('inventory_id')->references('id')->on('inventories');
        });
        /***********************************************************************************/
        Schema::create('complaint_service_report_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('complaint_service_report_id');
            $table->integer('stage')->comment('0 - open, 1- inprogress,2-attend,3-completed,4-closed');
            $table->longText('desc')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();

        });
        Schema::table('complaint_service_report_notes', function($table) {
            $table->foreign('complaint_service_report_id')->references('id')->on('complaint_service_report');
        });
        /***********************************************************************************/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('complaint_enquiries');
        Schema::dropIfExists('complaint_processes');
        Schema::dropIfExists('complaint_users');
        Schema::dropIfExists('complaint_checklists');
        Schema::dropIfExists('complaint_service_report');
        Schema::dropIfExists('complaint_service_report_checklist');
        Schema::dropIfExists('complaint_service_report_inv');
        Schema::dropIfExists('complaint_service_report_notes');

    }
}
