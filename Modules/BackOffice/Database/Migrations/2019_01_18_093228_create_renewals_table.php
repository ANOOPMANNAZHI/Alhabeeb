<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRenewalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('renewals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('new_contract_id')->nullable();
            $table->unsignedInteger('old_contract_id')->nullable();
            $table->unsignedInteger('renewal_type')->comment('1 - Tenant , 2-Landlord');;
            $table->unsignedInteger('work_flow_processes_code')->nullable();
            $table->longText('renewal_notes')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });


        Schema::table('renewals', function($table) {
            $table->foreign('work_flow_processes_code')->references('work_flow_processes_code')->on('work_flow_processes');
        });
        Schema::create('renewal_users', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('role_id')->nullable();
            $table->unsignedInteger('renewals_id')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 Active, 0 inactive');
        });

        Schema::table('renewal_users', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('renewal_users', function($table) {
            $table->foreign('role_id')->references('id')->on('roles');
        });
        Schema::table('renewal_users', function($table) {
            $table->foreign('renewals_id')->references('id')->on('renewals');
        });
        Schema::create('renewal_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('renewal_id');
            $table->unsignedInteger('renewal_notes_status')->default(1)->comment('1 - inprogresss, 0 - closed');;
            $table->integer('renewal_type')->comment('1 - Tenant , 2-Landlord');;
            $table->longText('renewal_notes_note');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('renewal_notes', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('renewal_notes', function($table) {
            $table->foreign('renewal_id')->references('id')->on('renewals');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('renewals');
        Schema::dropIfExists('renewal_users');
    }
}
