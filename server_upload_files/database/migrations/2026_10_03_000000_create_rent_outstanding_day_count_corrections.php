<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail of rent-outstanding:apply-day-count: stored rent outstanding on early
 * terminations from 01/08/2026 recalculated with the 30-day rule (user, 03/10/2026).
 * One row per change, figures before and after.
 */
class CreateRentOutstandingDayCountCorrections extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('rent_outstanding_day_count_corrections')) {
            Schema::create('rent_outstanding_day_count_corrections', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('tenant_contract_id')->index();
                $table->date('termination_date')->nullable();
                $table->decimal('due_old', 12, 3);
                $table->decimal('due_new', 12, 3);
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
        Schema::dropIfExists('rent_outstanding_day_count_corrections');
    }
}
