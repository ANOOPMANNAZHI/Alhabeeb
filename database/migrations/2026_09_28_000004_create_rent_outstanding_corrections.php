<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail of rent-outstanding:correct-short-contracts. One row per corrected
 * contract, with the figures before and after; the command skips contracts
 * already listed here, so it can be re-run safely.
 */
class CreateRentOutstandingCorrections extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('rent_outstanding_corrections')) {
            Schema::create('rent_outstanding_corrections', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('tenant_contract_id')->unique();
                $table->date('termination_date')->nullable();
                $table->decimal('rule_reduction', 12, 3);
                $table->decimal('os_old', 12, 3)->nullable();
                $table->decimal('os_new', 12, 3)->nullable();
                $table->integer('dues_line_id')->nullable();
                $table->decimal('dues_line_old', 12, 3)->nullable();
                $table->decimal('dues_line_new', 12, 3)->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('rent_outstanding_corrections');
    }
}
