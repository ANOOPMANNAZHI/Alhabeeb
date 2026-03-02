<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenantContractRevokeNoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_contract_revoke_note', function (Blueprint $table) {
            $table->increments('id');
			$table->unsignedInteger('tenant_contracts_id');
            $table->string('tenant_contract_revoke_note_desc');
            $table->tinyInteger('tenant_contract_revoke_note_status')->default(1)->comment('1-Active,2-Inactive');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_contract_revoke_note');
    }
}
