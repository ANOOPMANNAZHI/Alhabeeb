<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenantContractRevokeTempTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_contract_revoke_temp', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_contracts_id');
			$table->unsignedInteger('tenant_id');
			$table->unsignedInteger('occupant_id')->nullable();	
            $table->string('tenant_contract_electric_water')->nullable();	
            $table->date('tenant_contract_last_paid_date')->nullable();	
            $table->decimal('tenant_contract_last_paid_amt', 8, 2)->nullable();	
            $table->string('tenant_contract_muncipality_agr_no')->nullable();	
            $table->integer('tenant_contract_registered_in')->nullable();	
            $table->string('tenant_contract_payment_type')->nullable();	
            $table->longText('tenant_contract_note')->nullable();	
            $table->string('tenant_contract_deposit_amt')->nullable();	
            $table->longText('tenant_contract_guarantee_cheque_details')->nullable();	
            $table->string('tenant_contract_receipt_no')->nullable();
            $table->date('tenant_contract_receipt_date')->nullable();
            $table->decimal('tenant_contract_receipt_amt', 8, 2)->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('tenant_contract_revoke_temp', function($table) {
            $table->foreign('tenant_id')->references('id')->on('tenant');
        });
        Schema::table('tenant_contract_revoke_temp', function($table) {
            $table->foreign('tenant_contracts_id')->references('id')->on('tenant_contracts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_contract_revoke_temp');
    }
}
