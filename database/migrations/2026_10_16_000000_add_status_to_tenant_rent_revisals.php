<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Rent revisal drafts: status draft (saved, not emailed) or sent. Existing revisals were all sent. */
class AddStatusToTenantRentRevisals extends Migration
{
    public function up()
    {
        Schema::table('tenant_rent_revisals', function (Blueprint $t) {
            $t->string('status', 10)->default('sent');
        });
    }

    public function down()
    {
        Schema::table('tenant_rent_revisals', function (Blueprint $t) {
            $t->dropColumn('status');
        });
    }
}
