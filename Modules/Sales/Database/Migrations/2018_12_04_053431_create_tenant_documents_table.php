<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenantDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_contract_id');
            $table->string('tenant_documents_name')->nullable();
            $table->longText('tenant_documents_file_name')->nullable();
            $table->tinyInteger('tenant_documents_status')->comment('1 - preliminary, 2 - final documentation');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('tenant_documents', function($table) {
            $table->foreign('tenant_contract_id')->references('id')->on('tenant_contracts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_documents');
    }
}
