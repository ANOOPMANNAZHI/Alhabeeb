<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tenant_code');
            $table->unsignedInteger('tenant_type_id');
            $table->string('tenant_name');
            $table->longText('tenant_contact_address')->nullable();
            $table->longText('tenant_secondary_address')->nullable();
            $table->string('tenant_pc')->nullable();
            $table->unsignedInteger('location_id')->nullable();
            $table->string('tenant_contact_no')->nullable();
            $table->string('tenant_contact_person')->nullable();
            $table->string('tenant_contact_email')->nullable();
            $table->string('tenant_fax_no')->nullable();
            $table->string('tenant_acc_no')->nullable();
            $table->unsignedInteger('bank_id')->nullable();
            $table->tinyInteger('tenant_status')->default(0)->comment('1 - active , 0  - inactive');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('tenant_document', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id');
            $table->string('tenant_document_name');
            $table->string('tenant_document_file_name');
            $table->tinyInteger('tenant_document_status')->comment('1 - preliminary, 2 - final documentation');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('tenant', function($table) {
            $table->foreign('tenant_type_id')->references('id')->on('tenant_types');
        });
        Schema::table('tenant', function($table) {
            $table->foreign('location_id')->references('id')->on('locations');
        });
        Schema::table('tenant', function($table) {
            $table->foreign('bank_id')->references('id')->on('bank');
        });
        Schema::table('tenant_document', function($table) {
            $table->foreign('tenant_id')->references('id')->on('tenant');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant');
    }
}
