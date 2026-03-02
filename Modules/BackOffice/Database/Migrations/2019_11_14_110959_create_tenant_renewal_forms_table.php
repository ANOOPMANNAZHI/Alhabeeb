<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenantRenewalFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_renewal_forms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tenant_contract_rent');
            $table->date('tenant_contract_start_date');
            $table->date('tenant_contract_valid_to_date');
            $table->integer('tenant_contract_payment_type');
            $table->unsignedInteger('tenant_contract_id');
            $table->timestamps();

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
        Schema::dropIfExists('tenant_renewal_forms');
    }
}
