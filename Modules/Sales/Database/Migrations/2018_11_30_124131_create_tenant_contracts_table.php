<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenantContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id');
            $table->unsignedInteger('occupant_id')->nullable();
            $table->string('tenant_contract_old_no')->nullable();
            $table->string('tenant_contract_no')->nullable();
            $table->longText('tenant_contract_address')->nullable();
            $table->integer('tenant_contract_no_members')->nullable();
            $table->integer('tenant_contract_duration')->nullable();
            $table->integer('tenant_contract_duration_type')->nullable();
            $table->integer('tenant_contract_rent')->nullable();
            $table->string('tenant_contract_muncipality_agr_no')->nullable();
            $table->dateTime('tenant_contract_last_paid_date')->nullable();
            $table->float('tenant_contract_last_paid_amt')->nullable();
            $table->string('tenant_contract_guarantee_cheque_details')->nullable();
            $table->string('tenant_contract_electric_water')->nullable();
            $table->float('tenant_contract_agreement_amt')->nullable();
            $table->dateTime('tenant_contract_start_date')->nullable();
            $table->dateTime('tenant_contract_effective_date')->nullable();
            $table->dateTime('tenant_contract_valid_from_date')->nullable();
            $table->dateTime('tenant_contract_valid_to_date')->nullable();
            $table->integer('tenant_contract_payment_type')->nullable()->comment('1 - monthly, 2 - bi-monthly, 3 - half yearly');
            $table->tinyInteger('tenant_contract_status')->default(0)->comment('1 - active , 0  - inactive');
            $table->integer('tenant_contract_registered_in')->comment('1 - muscat, 2 - not in muscat')->nullable();
            $table->float('tenant_contract_deposit_amt')->nullable();
            $table->string('tenant_contract_receipt_no')->nullable();
            $table->dateTime('tenant_contract_receipt_date')->nullable();
            $table->unsignedInteger('building_id')->nullable();
            $table->unsignedInteger('unit_id')->nullable();
            $table->string('tenant_contract_note')->nullable();
            $table->unsignedInteger('sale_enquiry_id')->nullable();
            $table->float('tenant_contract_penalty')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        
        Schema::table('tenant_contracts', function($table) {
            $table->foreign('tenant_id')->references('id')->on('tenant');
        });
        Schema::table('tenant_contracts', function($table) {
            $table->foreign('sale_enquiry_id')->references('id')->on('sales_enquiries');
        });
        Schema::create('tenant_contract_status', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_status_id');
            $table->unsignedInteger('tenant_contract_id');
        });
        Schema::table('tenant_contract_status', function($table) {
            $table->foreign('tenant_contract_id')->references('id')->on('tenant_contracts');
        });
        Schema::table('tenant_contract_status', function($table) {
            $table->foreign('tenant_status_id')->references('id')->on('tenant_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_contracts');
    }
}
