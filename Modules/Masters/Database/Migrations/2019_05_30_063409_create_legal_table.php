<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLegalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('legal', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('building_id');
            $table->unsignedInteger('unit_id');
            $table->unsignedInteger('tenant_contract_id');
            $table->longText('note');
            $table->unsignedInteger('work_flow_processes_code')->nullable();
            $table->tinyInteger('are_status')->default(0)->comment('1 Enabled, 0 disabled');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('legal', function($table) {
            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('tenant_contract_id')->references('id')->on('tenant_contracts');
            $table->foreign('work_flow_processes_code')->references('work_flow_processes_code')->on('work_flow_processes');
        });

        Schema::create('legal_users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('legal_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('role_id');
            $table->tinyInteger('status')->default(1)->comment('1 Active, 0 inactive');
        });
        Schema::table('legal_users', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('legal_users', function($table) {
            $table->foreign('role_id')->references('id')->on('roles');
        });
        Schema::table('legal_users', function($table) {
            $table->foreign('legal_id')->references('id')->on('legal');
        });

        Schema::create('legal_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('legal_id');
            $table->tinyInteger('legal_notes_status')->default(1)->comment('1 - start, 0 - End, 2-Middle');
            $table->longText('legal_notes_note');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('legal_notes', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('legal_id')->references('id')->on('legal');
        });

        Schema::create('legal_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('legal_documents_file_name');
            $table->unsignedInteger('legal_id');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('legal_documents', function($table) {
            $table->foreign('legal_id')->references('id')->on('legal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('legal');
        Schema::dropIfExists('legal_users');
        Schema::dropIfExists('legal_notes');
        Schema::dropIfExists('legal_documents');
    }
}
