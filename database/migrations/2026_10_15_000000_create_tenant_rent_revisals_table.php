<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Rent revisal before renewal (spec 2026-10-15): one revisal per contract + the 120-day ARE emails sent. */
class CreateTenantRentRevisalsTable extends Migration
{
    public function up()
    {
        Schema::create('tenant_rent_revisals', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('tenant_contract_id')->unique();
            $t->decimal('current_rent', 15, 3);
            $t->decimal('revised_rent', 15, 3);
            $t->text('note')->nullable();
            $t->string('email_status', 20)->nullable();
            $t->timestamp('emailed_at')->nullable();
            $t->integer('created_by');
            $t->timestamps();
            $t->foreign('tenant_contract_id')->references('id')->on('tenant_contracts');
        });
        Schema::create('tenant_rent_revisal_notices', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('tenant_contract_id')->unique();
            $t->integer('are_user_id');
            $t->string('email_status', 20)->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();
            $t->foreign('tenant_contract_id')->references('id')->on('tenant_contracts');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tenant_rent_revisal_notices');
        Schema::dropIfExists('tenant_rent_revisals');
    }
}
