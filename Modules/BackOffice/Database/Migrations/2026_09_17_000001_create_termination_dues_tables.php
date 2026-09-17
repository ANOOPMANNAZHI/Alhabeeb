<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Termination dues: what a terminated tenant still owes, by category and
 * owner team. Settlements are computed live from receipts and deposit
 * refunds; only manual decisions (assignments, waivers) are stored here.
 * See docs/superpowers/specs/2026-09-17-termination-dues-design.md.
 */
class CreateTerminationDuesTables extends Migration
{
    public function up()
    {
        Schema::create('termination_dues', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_contract_id')->unique();
            $table->unsignedInteger('termination_id')->nullable();
            $table->date('termination_date')->nullable();
            $table->timestamp('terminated_at')->nullable();
            // Inspection (503) row created_at: general receipts from here on settle the charges.
            $table->timestamp('charges_fixed_at')->nullable();
            // Highest approved rent receipt already netted into the rent outstanding.
            $table->unsignedInteger('rent_receipts_upto_id')->nullable();
            $table->string('status', 20)->default('open'); // open | partial | settled | written_off
            $table->decimal('total_owed', 12, 3)->default(0);
            $table->decimal('total_settled', 12, 3)->default(0);
            $table->decimal('balance', 12, 3)->default(0);
            $table->decimal('backoffice_balance', 12, 3)->default(0);
            $table->decimal('maintenance_balance', 12, 3)->default(0);
            $table->date('next_promise_date')->nullable();
            $table->timestamp('last_followup_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('termination_date');
        });

        Schema::create('termination_dues_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('termination_dues_id');
            $table->string('category', 20);   // rent | municipal | ew | maintenance | other
            $table->string('owner_team', 20); // backoffice | maintenance
            $table->string('description', 255);
            $table->string('source_type', 50)->nullable(); // termination_checklist | termination | computed
            $table->unsignedInteger('source_id')->nullable();
            $table->decimal('amount', 12, 3)->default(0);
            $table->unsignedSmallInteger('line_order')->default(0);
            $table->timestamps();

            $table->foreign('termination_dues_id')->references('id')->on('termination_dues')->onDelete('cascade');
            $table->index(['termination_dues_id', 'category']);
        });

        Schema::create('termination_dues_allocations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('termination_dues_line_id');
            $table->string('source_type', 30); // rent_receipt | general_receipt_line | deposit_deduction | waiver
            $table->unsignedInteger('source_id')->nullable();
            $table->decimal('amount', 12, 3)->default(0);
            $table->text('remark')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('termination_dues_line_id')->references('id')->on('termination_dues_lines')->onDelete('cascade');
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('termination_dues_followups', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('termination_dues_id');
            $table->string('owner_team', 20);
            $table->date('followup_date');
            $table->string('method', 30); // call | sms | whatsapp | email | visit | other
            $table->text('note')->nullable();
            $table->date('promise_date')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('termination_dues_id')->references('id')->on('termination_dues')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('termination_dues_followups');
        Schema::dropIfExists('termination_dues_allocations');
        Schema::dropIfExists('termination_dues_lines');
        Schema::dropIfExists('termination_dues');
    }
}
