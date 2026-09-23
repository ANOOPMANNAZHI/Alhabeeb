<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Drawer's bank account on a PDC (optional, digits as typed; compared without leading zeros). */
class AddPdcAccountNoToPdc extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('pdc', 'pdc_account_no')) {
            Schema::table('pdc', function (Blueprint $table) {
                $table->string('pdc_account_no', 20)->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('pdc', 'pdc_account_no')) {
            Schema::table('pdc', function (Blueprint $table) {
                $table->dropColumn('pdc_account_no');
            });
        }
    }
}
