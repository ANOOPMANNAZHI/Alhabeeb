<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Municipal tax / processing / penalty saved at municipality registration, and their GL vouchers. */
class AddMunicipalityChargesToTenantContracts extends Migration
{
    public function up()
    {
        Schema::table('tenant_contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('tenant_contracts', 'tenant_contract_municipality_tax_amt')) {
                $table->decimal('tenant_contract_municipality_tax_amt', 12, 3)->nullable();
            }
            if (!Schema::hasColumn('tenant_contracts', 'tenant_contract_municipality_processing_amt')) {
                $table->decimal('tenant_contract_municipality_processing_amt', 12, 3)->nullable();
            }
            if (!Schema::hasColumn('tenant_contracts', 'tenant_contract_municipality_penalty_amt')) {
                $table->decimal('tenant_contract_municipality_penalty_amt', 12, 3)->default(0);
            }
            if (!Schema::hasColumn('tenant_contracts', 'tenant_contract_municipality_gl_id')) {
                $table->integer('tenant_contract_municipality_gl_id')->nullable();
            }
            if (!Schema::hasColumn('tenant_contracts', 'tenant_contract_municipality_penalty_gl_id')) {
                $table->integer('tenant_contract_municipality_penalty_gl_id')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('tenant_contracts', function (Blueprint $table) {
            foreach (['tenant_contract_municipality_tax_amt', 'tenant_contract_municipality_processing_amt',
                      'tenant_contract_municipality_penalty_amt', 'tenant_contract_municipality_gl_id',
                      'tenant_contract_municipality_penalty_gl_id'] as $col) {
                if (Schema::hasColumn('tenant_contracts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
